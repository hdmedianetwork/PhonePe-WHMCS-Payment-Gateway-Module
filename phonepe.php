<?php
/**
 * ============================================================
 *  PhonePe Payment Gateway Module for WHMCS
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
 */

require_once(dirname(__FILE__) . '/phonepe-sdk/encdec_phonepe.php');

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Module Metadata
 */
function phonepe_MetaData()
{
    return array(
        'DisplayName'                 => 'PhonePe Payment Gateway',
        'APIVersion'                  => '1.1',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage'            => false,
    );
}

/**
 * Module Info
 * Shown in WHMCS App Store / Payment Gateway info panel.
 */
function phonepe_moduleInfo()
{
    return array(
        'name'         => 'PhonePe Payment Gateway',
        'description'  => 'Accept payments via PhonePe - India\'s leading UPI & digital payments platform. Supports UPI, Credit/Debit Cards, Net Banking & Wallets. Developed by SkyServer Cloud Technologies | https://skyserver.in',
        'author'       => 'SkyServer Cloud Technologies',
        'authorurl'    => 'https://skyserver.in',
        'supporturl'   => 'https://skyserver.in',
        'supportemail' => 'support@skyserver.in',
        'category'     => 'Payment Gateways',
        'version'      => '1.0.0',
    );
}

/**
 * Gateway Configuration Fields
 */
function phonepe_config()
{
    $config_array = array(
        'FriendlyName' => array(
            'Type'  => 'System',
            'Value' => 'PhonePe | Developed by SkyServer Cloud Technologies (https://skyserver.in)',
        ),
        'MerchantId' => array(
            'FriendlyName' => 'Merchant ID',
            'Type'         => 'text',
            'Size'         => '30',
            'Description'  => 'Your PhonePe Merchant ID',
        ),
        'SaltKey' => array(
            'FriendlyName' => 'Salt Key',
            'Type'         => 'password',
            'Size'         => '50',
            'Description'  => 'Your PhonePe Salt Key (keep this secret)',
        ),
        'SaltIndex' => array(
            'FriendlyName' => 'Salt Index',
            'Type'         => 'text',
            'Size'         => '5',
            'Description'  => 'Usually 1',
        ),
        'ProductionUrl' => array(
            'FriendlyName' => 'Production URL',
            'Type'         => 'text',
            'Size'         => '100',
            'Description'  => 'PhonePe API base URL (no trailing slash)',
        ),
        'DevelopedBy' => array(
            'FriendlyName' => 'Developed By',
            'Type'         => 'System',
            'Value'        => 'SkyServer Cloud Technologies | https://skyserver.in | support@skyserver.in',
        ),
    );

    return $config_array;
}

/**
 * Payment Link / Button Generator
 *
 * @author  SkyServer Cloud Technologies <support@skyserver.in>
 * @website https://skyserver.in
 */
function phonepe_link($params)
{
    $MerchantId    = $params['MerchantId'];
    $SaltKey       = $params['SaltKey'];
    $SaltIndex     = $params['SaltIndex'];
    $ProductionUrl = $params['ProductionUrl'];

    $systemUrl  = $params['systemurl'];
    $returnUrl  = $params['returnurl'];
    $moduleName = $params['paymentmethod'];

    $invoiceId = $params['invoiceid'] . '_' . time();
    $amount    = (int)($params['amount'] * 100);

    $phone  = $params['clientdetails']['phonenumber'];
    $userId = $params['clientdetails']['id'];

    $jsonData = array(
        'merchantId'            => $MerchantId,
        'merchantTransactionId' => $invoiceId,
        'merchantUserId'        => $userId,
        'amount'                => $amount,
        'redirectUrl'           => $returnUrl,
        'redirectMode'          => 'POST',
        'callbackUrl'           => "{$systemUrl}/modules/gateways/callback/{$moduleName}.php",
        'mobileNumber'          => $phone,
        'paymentInstrument'     => array('type' => 'PAY_PAGE'),
    );

    $bepData = encode_data($jsonData);

    $apiSalt = array(
        'bepData'   => $bepData,
        'saltKey'   => $SaltKey,
        'saltIndex' => $SaltIndex,
        'type'      => 'pay',
    );

    $checkSumValue = generateCheckSum($apiSalt);

    $apiData = array(
        'bepData'       => $bepData,
        'checkSumValue' => $checkSumValue,
    );

    $paymentUrl = callApi($ProductionUrl . '/pay', $apiData);

    if ($paymentUrl) {
        $htmlOutput  = '<form method="post" action="' . htmlspecialchars($paymentUrl) . '">';
        $htmlOutput .= '<input type="submit" value="Pay with PhonePe" />';
        $htmlOutput .= '</form>';
    } else {
        $htmlOutput = '<p style="color:red;">Unable to initiate PhonePe payment. Please contact <a href="mailto:support@skyserver.in">support@skyserver.in</a>.</p>';
    }

    return $htmlOutput;
}

/**
 * Refund Handler
 *
 * @author  SkyServer Cloud Technologies <support@skyserver.in>
 * @website https://skyserver.in
 */
function phonepe_refund($params)
{
    $MerchantId    = $params['MerchantId'];
    $SaltKey       = $params['SaltKey'];
    $SaltIndex     = $params['SaltIndex'];
    $ProductionUrl = $params['ProductionUrl'];

    $invoiceId             = $params['invoiceid'] . '_' . time();
    $transactionIdToRefund = $params['transid'];
    $refundAmount          = (int)($params['amount'] * 100);
    $userId                = $params['clientdetails']['id'];
    $systemUrl             = $params['systemurl'];

    $jsonData = array(
        'merchantId'            => $MerchantId,
        'merchantUserId'        => $userId,
        'originalTransactionId' => $transactionIdToRefund,
        'merchantTransactionId' => $invoiceId,
        'amount'                => $refundAmount,
        'callbackUrl'           => $systemUrl,
    );

    $bepData = encode_data($jsonData);

    $apiSalt = array(
        'bepData'   => $bepData,
        'saltKey'   => $SaltKey,
        'saltIndex' => $SaltIndex,
        'type'      => 'refund',
    );

    $checkSumValue = generateCheckSum($apiSalt);

    $apiData = array(
        'bepData'       => $bepData,
        'checkSumValue' => $checkSumValue,
    );

    callApi($ProductionUrl . '/refund', $apiData);

    return array(
        'status'  => 'success',
        'rawdata' => 'Refund request raised with PhonePe',
        'transid' => $transactionIdToRefund,
        'fees'    => 0,
    );
}

/*
 * ============================================================
 *  Developed by SkyServer Cloud Technologies
 *  https://skyserver.in | support@skyserver.in
 * ============================================================
 */
