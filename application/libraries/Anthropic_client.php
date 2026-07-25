<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Minimal Claude (Anthropic Messages API) client — native cURL so the project
 * keeps its "no composer vendor dir" setup (see Xlsx_writer.php).
 *
 * Used by AdminController's Worksheet Generator to turn a topic + output type
 * into structured JSON. Server-side only — the API key never reaches the browser.
 */
class Anthropic_client
{
    private $api_key;
    private $model;
    private $version;

    public function __construct()
    {
        $ci = &get_instance();
        $ci->config->load('anthropic');

        $this->api_key = $ci->config->item('anthropic_api_key');
        $this->model   = $ci->config->item('anthropic_model') ?: 'claude-opus-4-8';
        $this->version = $ci->config->item('anthropic_version') ?: '2023-06-01';
    }

    public function is_configured()
    {
        return !empty($this->api_key);
    }

    /**
     * @param string $system     System prompt (shape/format instructions).
     * @param string $userPrompt User-turn content (the actual request).
     * @param int    $maxTokens  Output token cap.
     * @return array ['ok' => bool, 'text' => string, 'error' => string]
     */
    public function generate($system, $userPrompt, $maxTokens = 8000)
    {
        if (!$this->is_configured()) {
            return ['ok' => false, 'text' => '', 'error' => 'Anthropic API key is not configured. Set the ANTHROPIC_API_KEY environment variable.'];
        }

        $body = json_encode([
            'model'      => $this->model,
            'max_tokens' => $maxTokens,
            'system'     => $system,
            'messages'   => [
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . $this->api_key,
                'anthropic-version: ' . $this->version,
                'content-type: application/json',
            ],
            CURLOPT_POSTFIELDS     => $body,
            // Kept below the calling controller's set_time_limit(240) so a
            // slow generation surfaces our own error instead of a hard PHP kill.
            CURLOPT_TIMEOUT        => 220,
        ]);

        $response  = curl_exec($ch);
        $curl_err  = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'text' => '', 'error' => 'Request to Anthropic API failed: ' . $curl_err];
        }

        $decoded = json_decode($response, true);

        if ($http_code !== 200) {
            $msg = isset($decoded['error']['message']) ? $decoded['error']['message'] : ('HTTP ' . $http_code);
            return ['ok' => false, 'text' => '', 'error' => 'Anthropic API error: ' . $msg];
        }

        if (!empty($decoded['stop_reason']) && $decoded['stop_reason'] === 'refusal') {
            return ['ok' => false, 'text' => '', 'error' => 'The model declined this request.'];
        }

        $text = '';
        if (!empty($decoded['content']) && is_array($decoded['content'])) {
            foreach ($decoded['content'] as $block) {
                if (isset($block['type']) && $block['type'] === 'text') {
                    $text .= $block['text'];
                }
            }
        }

        if ($text === '') {
            return ['ok' => false, 'text' => '', 'error' => 'No text content in Anthropic API response.'];
        }

        if (!empty($decoded['stop_reason']) && $decoded['stop_reason'] === 'max_tokens') {
            return ['ok' => false, 'text' => $text, 'error' => 'Response was truncated (max_tokens reached). Try a smaller/simpler request.'];
        }

        return ['ok' => true, 'text' => $text, 'error' => ''];
    }
}
