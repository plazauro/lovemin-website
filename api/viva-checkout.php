<?php
/**
 * Arcee's Misiunea Iubirii - Viva.com Secure Checkout Bridge
 * 
 * Instructions:
 * 1. Save this as 'api/viva-checkout.php' on your server.
 * 2. Ensure your server has the CURL extension enabled.
 * 3. Replace YOUR_SOURCE_CODE_HERE with the 4-digit code from your Viva Dashboard.
 */

header('Content-Type: application/json');

// --- CONFIGURATION ---
$merchantId = "d9507472-e1e8-4897-a787-0903f3aeba89";
$apiKey     = "Cd797kWC8y86Rc6YNRGvwr4zFX5ZCU";
$sourceCode = "1457"; // Update this with your 4-digit Source Code

// Get the posted amount (fallback to $50 if empty)
$input = json_decode(file_get_contents('php://input'), true);
$amount = isset($input['amount']) ? (int)$input['amount'] : 5000;

// 1. Prepare the API Call
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.vivapayments.com/checkout/v2/orders',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
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
curl_close($curl);

// 2. Process Response
if ($httpCode == 200) {
    $data = json_decode($response, true);
    echo json_encode(['orderCode' => $data['orderCode']]);
} else {
    echo json_encode(['error' => 'Failed to create order', 'details' => $response]);
}
?>
