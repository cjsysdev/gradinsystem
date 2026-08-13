<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * SMS text and phone-number rules — pure logic, no CI, no DB, no HTTP.
 *
 * Kept separate from Philsms_client for the same reason Worksheet_generator is
 * separate from Anthropic_client: every rule in here can be exercised by
 * AdminSmsController::selftest() without spending a single SMS credit.
 *
 * Numbers in this database are free text (varchar(35)) and inconsistent —
 * '09171234567', '0955 336 6401', '+639171234567' all appear. Nothing may be
 * handed to the gateway without passing through to_msisdn() first.
 */
class Sms_message
{
    /**
     * GSM 03.38 basic set — one septet each. Single-quoted deliberately: in a
     * double-quoted string '$¥' parses as a variable (PHP allows high-byte
     * characters in variable names) and the constant won't compile.
     */
    const GSM_BASIC = '@£$¥èéùìòÇ' . "\n" . 'Øø' . "\r"
        . 'ÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?'
        . '¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';

    /** GSM 03.38 extension set — two septets each (escape + char). */
    const GSM_EXTENDED = '^{}\\[~]|€';

    /**
     * Normalise any Philippine mobile number to the gateway's wire format,
     * 639XXXXXXXXX. Returns null when the input can't be a PH mobile number —
     * callers must treat null as "skip this recipient", never as an error.
     */
    public static function to_msisdn($raw)
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if ($digits === '') {
            return null;
        }

        // 00639XXXXXXXXX — international prefix instead of '+'.
        if (strpos($digits, '00') === 0) {
            $digits = substr($digits, 2);
        }

        $len = strlen($digits);

        if ($len === 11 && strpos($digits, '09') === 0) {
            $digits = '63' . substr($digits, 1);        // 09XXXXXXXXX
        } elseif ($len === 10 && strpos($digits, '9') === 0) {
            $digits = '63' . $digits;                   // 9XXXXXXXXX
        }

        return preg_match('/^639\d{9}$/', $digits) ? $digits : null;
    }

    /**
     * National format (09XXXXXXXXX) for storage and display — what the school's
     * paper forms and the xlsx exports expect. Returns null if unparseable.
     */
    public static function to_national($raw)
    {
        $msisdn = self::to_msisdn($raw);
        return $msisdn === null ? null : '0' . substr($msisdn, 2);
    }

    /**
     * How many SMS credits one message body costs per recipient.
     *
     * A single non-GSM character (curly quote, emoji, ñ typed as U+00F1 outside
     * the GSM set) silently drops the limit from 160 to 70, so this is shown
     * live in the compose UI rather than discovered on the bill.
     *
     * @return array ['encoding' => 'plain'|'unicode', 'chars' => int, 'segments' => int]
     */
    public static function segments($text)
    {
        $text = (string) $text;
        $chars = self::mb_chars($text);

        $units   = 0;
        $unicode = false;

        foreach ($chars as $char) {
            if (mb_strpos(self::GSM_BASIC, $char, 0, 'UTF-8') !== false) {
                $units += 1;
            } elseif (mb_strpos(self::GSM_EXTENDED, $char, 0, 'UTF-8') !== false) {
                $units += 2;
            } else {
                $unicode = true;
                break;
            }
        }

        if ($unicode) {
            $units  = count($chars);
            $single = 70;
            $multi  = 67;
        } else {
            $single = 160;
            $multi  = 153;
        }

        if ($units === 0) {
            $segments = 0;
        } elseif ($units <= $single) {
            $segments = 1;
        } else {
            $segments = (int) ceil($units / $multi);
        }

        return [
            'encoding' => $unicode ? 'unicode' : 'plain',
            'chars'    => count($chars),
            'units'    => $units,
            'segments' => $segments,
        ];
    }

    /**
     * Collapse recipients sharing a number. Siblings share a guardian, and some
     * students list their own number as their emergency contact — without this
     * one person gets (and the school pays for) the same message twice.
     *
     * Keeps the first occurrence; expects each row to carry an 'msisdn' key.
     */
    public static function dedupe(array $recipients)
    {
        $seen = [];
        $out  = [];

        foreach ($recipients as $recipient) {
            $msisdn = isset($recipient['msisdn']) ? $recipient['msisdn'] : null;
            if ($msisdn === null || isset($seen[$msisdn])) {
                continue;
            }
            $seen[$msisdn] = true;
            $out[] = $recipient;
        }

        return $out;
    }

    private static function mb_chars($text)
    {
        // mb_str_split exists in PHP 7.4+; the fallback keeps this library
        // usable if the app is ever run on an older CLI binary.
        if (function_exists('mb_str_split')) {
            return mb_str_split($text, 1, 'UTF-8');
        }
        return preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
