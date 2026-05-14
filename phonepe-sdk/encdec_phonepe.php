<?php
/**
 * ============================================================
 *  PhonePe SDK - Encode / Decode & API Helper
 * ============================================================
 *
 *  @package     PhonePe WHMCS Gateway
 *  @version     1.0.0
 *  @author      SkyServer Cloud Technologies
 *  @website     https://skyserver.in
 *  @support     support@skyserver.in
 *  @copyright   Copyright (c) 2024 SkyServer Cloud Technologies
 *  @license     Proprietary - All Rights Reserved
 *
 * ------------------------------------------------------------
 *  DEVELOPED BY
 *  SkyServer Cloud Technologies
 *  Website : https://skyserver.in
 *  Support : support@skyserver.in
 * ------------------------------------------------------------
 *
 *  DESCRIPTION:
 *  Core SDK helper functions for the PhonePe WHMCS gateway.
 *  Handles Base64 encoding/decoding, SHA256 checksum generation,
 *  and cURL API communication with PhonePe's PG endpoints.
 *
 *  FILE PATH (relative to WHMCS root):
 *  /modules/gateways/phonepe-sdk/encdec_phonepe.php
 * ============================================================
 */

/**
 * Encode payment data to Base64
 *
 * Converts an associative array to JSON and then Base64 encodes it,
 * as required by PhonePe's API request format.
 *
 * @param  array  $jsonArray  Payment data array
 * @return string             Base64-encoded JSON string
 *
 * @author SkyServer Cloud Technologies <support@skyserver.in>
 */
function encode_data($jsonArray)
{
    $jsonData      = json_encode($jsonArray);
    $base64Encoded = base64_encode($jsonData);
    return $base64Encoded;
}

/**
 * Decode Base64 response from PhonePe
 *
 * Decodes the Base64-encoded response received from PhonePe
 * back into a PHP associative array.
 *
 * @param  string $base64String  Base64-encoded response string
 * @return array                 Decoded data as associative array
 *
 * @author SkyServer Cloud Technologies <support@skyserver.in>
 */
function decode_data($base64String)
{
    $jsonData = base64_decode($base64String);
    $decoded  = json_decode($jsonData, true);
    return $decoded;
}

/**
 * Generate SHA256 Checksum
 *
 * Generates the X-VERIFY checksum header value required by
 * PhonePe for request authentication. The payload string
 * format differs based on the request type:
 *
 *  - pay      : base64_payload + "/pg/v1/pay"    + saltKey
 *  - refund   : base64_payload + "/pg/v1/refund" + saltKey
 *  - response : base64_payload + saltKey
 *
 * Final checksum format: sha256_hash###saltIndex
 *
 * @param  array  $data  Array with keys: bepData, saltKey, saltIndex, type
 * @return string        Checksum string in format: hash###saltIndex
 *
 * @author SkyServer Cloud Technologies <support@skyserver.in>
 */
function generateCheckSum($data)
{
    switch ($data['type']) {
        case 'pay':
            $payload = $data['bepData'] . '/pg/v1/pay' . $data['saltKey'];
            break;

        case 'refund':
            $payload = $data['bepData'] . '/pg/v1/refund' . $data['saltKey'];
            break;

        case 'response':
        default:
            $payload = $data['bepData'] . $data['saltKey'];
            break;
    }

    $hash     = hash('sha256', $payload);
    $checksum = $hash . '###' . $data['saltIndex'];

    return $checksum;
}

/**
 * Call PhonePe API via cURL
 *
 * Makes a POST request to the specified PhonePe endpoint
 * and returns the redirect URL for the payment page.
 *
 * Returns null if:
 *  - A cURL error occurs
 *  - The API response does not contain a redirect URL
 *
 * @param  string      $prodUrl   Full API endpoint URL (e.g. https://api.phonepe.com/.../pay)
 * @param  array       $data      Array with keys: bepData, checkSumValue
 * @return string|null            PhonePe redirect URL or null on failure
 *
 * @author SkyServer Cloud Technologies <support@skyserver.in>
 */
function callApi($prodUrl, $data)
{
    $curl = curl_init();

    $jsonData = json_encode(['request' => $data['bepData']]);

    curl_setopt_array($curl, [
        CURLOPT_URL            => $prodUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $jsonData,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-VERIFY: ' . $data['checkSumValue'],
        ],
    ]);

    $response = curl_exec($curl);
    $err      = curl_error($curl);

    curl_close($curl);

    if ($err) {
        // Log cURL error — visible to admin via WHMCS Activity Log
        error_log('[SkyServer PhonePe Gateway] cURL Error: ' . $err);
        return null;
    }

    $responseData = json_decode($response, true);

    if (isset($responseData['data']['instrumentResponse']['redirectInfo']['url'])) {
        return $responseData['data']['instrumentResponse']['redirectInfo']['url'];
    }

    // Log unexpected API response
    error_log('[SkyServer PhonePe Gateway] Unexpected API response: ' . $response);
    return null;
}

/*
 * ============================================================
 *  End of PhonePe SDK Helper
 *  Developed by SkyServer Cloud Technologies
 *  https://skyserver.in | support@skyserver.in
 * ============================================================
 */
