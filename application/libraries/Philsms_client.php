<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Minimal PhilSMS gateway client — native cURL so the project keeps its
 * "no composer vendor dir" setup (see Anthropic_client.php, Xlsx_writer.php).
 *
 * API: POST {base}/sms/send with a Bearer token. `recipient` takes a
 * comma-separated list, which is why a whole section goes out in a handful of
 * calls instead of one per student. Success is {"status":"success","data":…};
 * failures come back as {"status":"error","message":…} — sometimes still on
 * HTTP 200, so the body is checked as well as the status code.
 *
 * Server-side only — the token never reaches the browser. Never throws and
 * never dies: every path returns an array so a failed blast reports itself
 * instead of taking the request down mid-send.
 */
class Philsms_client
{
    private $api_token;
    private $sender_id;
    private $base_url;
    private $timeout;

    public function __construct()
    {
        $ci = &get_instance();
        $ci->config->load('philsms');
        // Loaded for its class definition — send() calls Sms_message
        // statically to pick plain vs unicode.
        $ci->load->library('sms_message');

        $this->api_token = $ci->config->item('philsms_api_token');
        $this->sender_id = $ci->config->item('philsms_sender_id') ?: 'PhilSMS';
        $this->base_url  = rtrim($ci->config->item('philsms_base_url') ?: 'https://app.philsms.com/api/v3', '/');
        $this->timeout   = (int) ($ci->config->item('philsms_timeout') ?: 30);
    }

    public function is_configured()
    {
        return !empty($this->api_token);
    }

    public function sender_id()
    {
        return $this->sender_id;
    }

    /**
     * Send one message body to one or more numbers.
     *
     * @param string|array $recipients One msisdn, or msisdns to bulk-send to.
     * @param string       $message    Body, already segment-checked by the caller.
     * @return array ['ok' => bool, 'data' => array, 'uid' => string|null, 'error' => string]
     */
    public function send($recipients, $message)
    {
        if (!$this->is_configured()) {
            return $this->fail('PhilSMS API token is not configured. Set the PHILSMS_API_TOKEN environment variable.');
        }

        $list = is_array($recipients) ? $recipients : [$recipients];
        $list = array_values(array_filter(array_map('trim', $list)));

        if (empty($list)) {
            return $this->fail('No recipients to send to.');
        }

        if (trim($message) === '') {
            return $this->fail('Message is empty.');
        }

        $segments = Sms_message::segments($message);

        $response = $this->request('POST', '/sms/send', [
            'recipient' => implode(',', $list),
            'sender_id' => $this->sender_id,
            'type'      => $segments['encoding'] === 'unicode' ? 'unicode' : 'plain',
            'message'   => $message,
        ]);

        if (!$response['ok']) {
            return $response;
        }

        $data = $response['data'];
        $uid  = null;
        if (isset($data['data']['uid'])) {
            $uid = $data['data']['uid'];
        } elseif (isset($data['data'][0]['uid'])) {
            $uid = $data['data'][0]['uid'];
        }

        return ['ok' => true, 'data' => $data, 'uid' => $uid, 'error' => ''];
    }

    /**
     * Remaining SMS credits, so a blast can be blocked before it half-sends.
     *
     * @return array ['ok' => bool, 'credits' => float|null, 'error' => string]
     */
    public function balance()
    {
        if (!$this->is_configured()) {
            return ['ok' => false, 'credits' => null, 'error' => 'PhilSMS API token is not configured.'];
        }

        $response = $this->request('GET', '/balance');

        if (!$response['ok']) {
            return ['ok' => false, 'credits' => null, 'error' => $response['error']];
        }

        // The account payload has moved around between docs revisions; accept
        // the shapes seen in the wild rather than hard-failing on a key name.
        $data    = $response['data'];
        $credits = null;
        foreach (['remaining_unit', 'sms_unit', 'balance', 'credits', 'unit'] as $key) {
            if (isset($data['data'][$key]) && is_numeric($data['data'][$key])) {
                $credits = (float) $data['data'][$key];
                break;
            }
            if (isset($data[$key]) && is_numeric($data[$key])) {
                $credits = (float) $data[$key];
                break;
            }
        }

        return ['ok' => true, 'credits' => $credits, 'error' => ''];
    }

    // ── Transport ───────────────────────────────────────────────────────────
    private function request($method, $path, array $body = null)
    {
        $ch = curl_init($this->base_url . $path);

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->api_token,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            // Kept below the calling controller's set_time_limit() so a slow
            // gateway surfaces our own error instead of a hard PHP kill.
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST]       = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($ch, $options);

        $raw       = curl_exec($ch);
        $curl_err  = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            return $this->fail('Request to PhilSMS failed: ' . $curl_err);
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return $this->fail('PhilSMS returned a non-JSON response (HTTP ' . $http_code . ').');
        }

        if ($http_code < 200 || $http_code >= 300) {
            return $this->fail('PhilSMS error: ' . $this->message_from($decoded, 'HTTP ' . $http_code));
        }

        // A rejected send can still arrive as HTTP 200 with status=error.
        if (isset($decoded['status']) && $decoded['status'] !== 'success') {
            return $this->fail('PhilSMS error: ' . $this->message_from($decoded, 'unknown error'));
        }

        return ['ok' => true, 'data' => $decoded, 'uid' => null, 'error' => ''];
    }

    private function message_from(array $decoded, $fallback)
    {
        if (!empty($decoded['message'])) {
            return is_string($decoded['message'])
                ? $decoded['message']
                : json_encode($decoded['message']);
        }
        if (!empty($decoded['data']) && is_string($decoded['data'])) {
            return $decoded['data'];
        }
        return $fallback;
    }

    private function fail($error)
    {
        return ['ok' => false, 'data' => [], 'uid' => null, 'error' => $error];
    }
}
