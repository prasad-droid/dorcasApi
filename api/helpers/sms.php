
<?php
function sendSMS($mobile, $otp)
{
    $url = "https://360marketingservice.com/api/v2/SendSMS";
    $url = "https://360marketingservice.com/api/v2/SendSMS";
    $api_key = 'V5qDoa0ufecAJ8+2jiLBA19lomga531GEgU5lbbVV3A=';
    $client_id = '246e74f6-c4ea-4bf8-a3ed-b1c743054baf';
    $sender_id = 'DORCAS';
    $tid = '1707176190355786877';
    $message = "Dear Customer, your new Dorcas OTP is $otp. Please use this code to complete your verification. Do not share it with anyone. - Team Dorcas";

    $data = [
        'ApiKey' => $api_key,
        'ClientId' => $client_id,
        'SenderId' => $sender_id,
        'MobileNumbers' => $mobile,
        'Message' => $message,
        'Is_Flash' => false,
        'IsRegisteredForDelivery' => true,
        'DataCoding' => '0',
        'tid' => $tid,
    ];

    $payload = json_encode($data);

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Content-Length: " . strlen($payload)
        ]
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        return [
            "status" => false,
            "message" => $error
        ];
    }

    return [
        "status" => true,
        "response" => $response,
        "otp" => $otp
    ];
}
?>