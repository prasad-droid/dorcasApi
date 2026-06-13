<?php
/**
 * Helper to send Firebase Cloud Messaging (FCM) notifications
 */

/* ======================================================================
   LEGACY FCM IMPLEMENTATION (Commented out as requested)
   ======================================================================
function sendPushNotification($to, $title, $body, $data = [])
{
    $url = 'https://fcm.googleapis.com/fcm/send';

    // IMPORTANT: Replace with your actual FCM Server Key from Firebase Console
    // Settings > Cloud Messaging > Cloud Messaging API (Legacy)
    $serverKey = 'AIzaSyC0t2VpSkVWOVdm-VfFhc3bbUfQ3yEbAZU'; // TODO: Replace with real key

    $fields = [
        'to' => $to,
        'notification' => [
            'title' => $title,
            'body' => $body,
            'sound' => 'default',
            'click_action' => 'FCM_PLUGIN_ACTIVITY',
            'icon' => 'fcm_push_icon'
        ],
        'data' => array_merge($data, [
            'title' => $title,
            'body' => $body
        ]),
        'priority' => 'high'
    ];

    $headers = [
        'Authorization: key=' . $serverKey,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['status' => false, 'error' => $error];
    }

    return ['status' => true, 'response' => json_decode($result, true)];
}
   ====================================================================== */

// ======================================================================
// NEW HTTP v1 API IMPLEMENTATION
// ======================================================================
class FCMv1Service {
    private $serviceAccountFile;

    public function __construct() {
        $this->serviceAccountFile = __DIR__ . '/../notifications/service-account.json';
    }

    private function getAccessToken() {
        if (!file_exists($this->serviceAccountFile)) {
            error_log("FCM Error: Service account file not found at " . $this->serviceAccountFile);
            return null;
        }

        $keyData = json_decode(file_get_contents($this->serviceAccountFile), true);
        
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $payload = json_encode([
            'iss' => $keyData['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;
        $signature = '';
        openssl_sign($signatureInput, $signature, $keyData['private_key'], "sha256WithRSAEncryption");
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        $jwt = $signatureInput . "." . $base64UrlSignature;
        
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    public function sendNotification($target, $title, $body, $dataPayload = []) {
        $keyData = json_decode(file_get_contents($this->serviceAccountFile), true);
        $projectId = $keyData['project_id'];
        
        $token = $this->getAccessToken();
        if (!$token) {
            return ["status" => false, "message" => "Failed to generate access token"];
        }
        
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        
        $message = [
            'message' => [
                'token' => $target,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => $dataPayload,
                'android' => [
                    'notification' => [
                        'channel_id' => 'dorcas-app-channel'
                    ]
                ]
            ]
        ];
        
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            "status" => $httpCode == 200,
            "http_code" => $httpCode,
            "response" => json_decode($result, true)
        ];
    }
}

/**
 * Save notification to database and send push
 */
/* ======================================================================
   LEGACY createNotification IMPLEMENTATION (Commented out as requested)
   ======================================================================
function createNotification($conn, $user_id, $user_type, $type, $title, $body, $booking_id = null)
{
    // 1. Save to database
    $query = "INSERT INTO notifications (user_id, user_type, type, title, body, booking_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issssi", $user_id, $user_type, $type, $title, $body, $booking_id);
    $stmt->execute();

    // 2. Fetch device token
    $table = ($user_type === 'vendor') ? 'vendors' : 'customers';
    $token_query = "SELECT device_token FROM $table WHERE id = ?";
    $t_stmt = $conn->prepare($token_query);
    $t_stmt->bind_param("i", $user_id);
    $t_stmt->execute();
    $token_res = $t_stmt->get_result();

    if ($row = $token_res->fetch_assoc()) {
        if (!empty($row['device_token'])) {
            // 3. Send push notification
            sendPushNotification($row['device_token'], $title, $body, ['type' => $type, 'booking_id' => $booking_id]);
        }
    }
}
   ====================================================================== */

// ======================================================================
// NEW HTTP v1 createNotification IMPLEMENTATION
// ======================================================================
function createNotification($conn, $user_id, $user_type, $type, $title, $body, $booking_id = null, $deepLink = '')
{
    // 1. Save to database
    $query = "INSERT INTO notifications (user_id, user_type, type, title, body, booking_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issssi", $user_id, $user_type, $type, $title, $body, $booking_id);
    $stmt->execute();

    // 2. Fetch device token
    $table = ($user_type === 'vendor') ? 'vendors' : 'customers';
    $token_query = "SELECT device_token FROM $table WHERE id = ?";
    $t_stmt = $conn->prepare($token_query);
    $t_stmt->bind_param("i", $user_id);
    $t_stmt->execute();
    $token_res = $t_stmt->get_result();

    if ($row = $token_res->fetch_assoc()) {
        if (!empty($row['device_token'])) {
            // 3. Send push notification using HTTP v1
            $fcmService = new FCMv1Service();
            
            // In HTTP v1 all data values MUST be strings
            $dataPayload = [
                'type' => (string)$type,
                'booking_id' => (string)$booking_id,
            ];
            
            if (!empty($deepLink)) {
                $dataPayload['link'] = (string)$deepLink;
            } else {
                // Assign a default route based on user type if empty
                $dataPayload['link'] = ($user_type === 'vendor') ? '/tech/dashboard' : '/customer/dashboard';
            }
            
            $result = $fcmService->sendNotification($row['device_token'], $title, $body, $dataPayload);
            
            // 4. Token Cleanup if invalid
            if (!$result['status'] && isset($result['response']['error'])) {
                $errorMsg = $result['response']['error']['message'] ?? '';
                $errorCode = $result['response']['error']['details'][0]['errorCode'] ?? '';
                $status = $result['response']['error']['status'] ?? '';
                
                if ($errorCode === 'UNREGISTERED' || $status === 'NOT_FOUND' || strpos($errorMsg, 'not registered') !== false || $status === 'INVALID_ARGUMENT') {
                    // Remove token from database
                    $clear_token_query = "UPDATE $table SET device_token = NULL WHERE id = ?";
                    $c_stmt = $conn->prepare($clear_token_query);
                    $c_stmt->bind_param("i", $user_id);
                    $c_stmt->execute();
                }
            }
        }
    }
}
?>