<?php
/**
 * Misiunea Iubirii - Viva.com Secure Checkout Bridge
 * Updated with enhanced error reporting and security headers
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allows requests from your domain
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// --- CONFIGURATION ---
// IMPORTANT: If these are Demo credentials, use 'demo-api.vivapayments.com'
$merchantId = "d9507472-e1e8-4897-a787-0903f3aeba89";
$apiKey     = "Cd797kWC8y86Rc6YNRGvwr4zFX5ZCU";
$sourceCode = "1457"; 
$apiUrl     = 'https://api.vivapayments.com/checkout/v2/orders';

// Get the posted amount
$input = json_decode(file_get_contents('php://input'), true);
$amount = isset($input['amount']) ? (int)$input['amount'] : 5000;

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
        'amount' => $amount,
        'customerTrns' => 'Donation to Misiunea Iubirii',
        'sourceCode' => $sourceCode,
        'requestLang' => 'en-GB'
    ]),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($merchantId . ':' . $apiKey)
    ),
));

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

if ($httpCode == 200) {
    $data = json_decode($response, true);
    if (isset($data['orderCode'])) {
        echo json_encode(['orderCode' => $data['orderCode']]);
    } else {
        echo json_encode(['error' => 'Viva response missing orderCode', 'debug' => $data]);
    }
} else {
    // This tells us EXACTLY what Viva didn't like (e.g., wrong API key, wrong source code)
    $vivaError = json_decode($response, true);
    echo json_encode([
        'error' => 'Viva API Error', 
        'httpCode' => $httpCode,
        'message' => $vivaError['message'] ?? 'Unknown error',
        'curlError' => $curlError
    ]);
}
?>
