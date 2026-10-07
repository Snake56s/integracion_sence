<?php
// This file is part of Moodle - http://moodle.org/

namespace block_sence_integration;

defined('MOODLE_INTERNAL') || die();

class client {

    /**
     * Test connection to SENCE Intermediate Manager API via GET.
     */
    public static function test_connection($rut_otec = null, $token = null) {
        global $CFG;

        if (empty($rut_otec)) {
            $rut_otec = get_config('block_sence_integration', 'rut_otec');
        }
        if (empty($token)) {
            $token = strtoupper(get_config('block_sence_integration', 'token_sence'));
        }

        $idsistema = get_config('block_sence_integration', 'idsistema') ?: '1350';
        $base_url = get_config('block_sence_integration', 'api_base_url') ?: 'https://auladigital.sence.cl/gestor/API/avance-sic';

        $endpoint = rtrim($base_url, '/') . '/historialEnvios?' . http_build_query([
            'rutOtec' => $rut_otec,
            'idSistema' => $idsistema,
            'token' => $token
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            return [
                'success' => false,
                'http_code' => $http_code,
                'error' => $curl_error,
                'response' => null
            ];
        }

        $json = json_decode($response, true);
        return [
            'success' => ($http_code == 200),
            'http_code' => $http_code,
            'response' => $json ?: $response
        ];
    }

    /**
     * Send progress payload via POST to SENCE Intermediate Manager API.
     */
    public static function send_progress(array $payload) {
        $base_url = get_config('block_sence_integration', 'api_base_url') ?: 'https://auladigital.sence.cl/gestor/API/avance-sic';
        $endpoint = rtrim($base_url, '/') . '/enviarAvance';

        $json_payload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600); // Recommended 10 min
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_payload)
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            return [
                'success' => false,
                'http_code' => $http_code,
                'error' => $curl_error,
                'raw_response' => null,
                'data' => null
            ];
        }

        $decoded = json_decode($response, true);
        return [
            'success' => ($http_code == 200),
            'http_code' => $http_code,
            'raw_response' => $response,
            'data' => $decoded
        ];
    }
}
