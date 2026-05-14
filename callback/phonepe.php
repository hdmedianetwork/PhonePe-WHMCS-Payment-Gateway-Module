<?php
/**
 * ============================================================
 *  PhonePe Payment Gateway - Callback Handler for WHMCS
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
 *  This file handles the server-to-server callback (webhook)
 *  sent by PhonePe after a payment attempt (success or failure).
 *  It verifies the checksum, marks the invoice as paid in WHMCS,
 *  and logs the transaction.
 *
 *  FILE PATH (relative to WHMCS root):
 *  /modules/gateways/callback/phonepe.php
 * ============================================================
 */

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once(dirname(__FILE__) . '/../phonepe-sdk/encdec_phonepe.php');

// ── Gateway Initialization ────────────────────────────────────────────────
$gatewayModuleName = basename(__FILE__, '.php');
$gatewayParams     = getGatewayVariables("phonepe");

if (!$gatewayParams['type']) {
    die("PhonePe Gateway Module is not activated. Please enable it from WHMCS Admin > Payment Gateways.");
}

// ── Read Incoming Callback Data ───────────────────────────────────────────
$checkSumValue = isset($_SERVER['HTTP_X_VERIFY']) ? $_SERVER['HTTP_X_VERIFY'] : '';
$body          = file_get_contents('php://input');
$responseData  = json_decode($body, true);

if (empty($responseData['response'])) {
    die("Invalid callback payload received.");
}

$response = $responseData['response'];

// ── Gateway Credentials ───────────────────────────────────────────────────
$SaltKey   = $gatewayParams['SaltKey'];
$SaltIndex = $gatewayParams['SaltIndex'];

// ── Decode PhonePe Response ───────────────────────────────────────────────
$data = decode_data($response);

$invoiceId         = $data['data']['merchantTransactionId'];
$transactionId     = $data['data']['transactionId'];
$paymentAmount     = $data['data']['amount'];   // Amount in paisa
$transactionStatus = ($data['code'] === 'PAYMENT_SUCCESS') ? 'success' : 'failed';

// ── Extract Clean Invoice ID (strip the appended timestamp) ───────────────
$invoice_arr  = explode('_', $invoiceId);
$invoiceIdR   = $invoice_arr[0];

// ── Verify Checksum (Security Check) ─────────────────────────────────────
$apiData = array(
    'bepData'   => $response,
    'saltKey'   => $SaltKey,
    'saltIndex' => $SaltIndex,
    'type'      => 'response',
);

$checkSumValueNew = generateCheckSum($apiData);

if ($checkSumValue !== $checkSumValueNew) {
    // Checksum mismatch — possible tampering, mark as failed
    $transactionStatus = 'failed';
    logTransaction(
        $gatewayParams['name'],
        array('error' => 'Checksum mismatch', 'received' => $checkSumValue, 'expected' => $checkSumValueNew),
        'failed'
    );
}

// ── Process Payment ───────────────────────────────────────────────────────
if ($transactionStatus === 'success') {

    // Validate invoice ID exists in WHMCS
    $invoiceIdR = checkCbInvoiceID($invoiceIdR, $gatewayParams['name']);

    // Ensure transaction hasn't already been processed
    checkCbTransID($transactionId);

    // Log the full transaction details
    logTransaction($gatewayParams['name'], $data, $transactionStatus);

    // Mark invoice as paid in WHMCS
    addInvoicePayment(
        $invoiceIdR,
        $transactionId,
        $paymentAmount / 100,  // Convert paisa back to rupees
        0,                      // Gateway fee (0 — PhonePe charges merchant separately)
        $gatewayModuleName
    );

} else {
    // Log failed transaction for reference
    logTransaction($gatewayParams['name'], $data, 'failed');
}

/*
 * ============================================================
 *  End of PhonePe Callback Handler
 *  Developed by SkyServer Cloud Technologies
 *  https://skyserver.in | support@skyserver.in
 * ============================================================
 */
