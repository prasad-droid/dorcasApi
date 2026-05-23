<?php
/**
 * Firebase Cloud Messaging (FCM) HTTP v1 API Integration
 * 
 * IMPORTANT: You must download a "Service Account Key" from Firebase Console.
 * (Project Settings -> Service Accounts -> Generate New Private Key)
 * Save it as "service-account.json" in the same directory as this file.
 */

class FCMService {
    private $serviceAccountFile = 'service-account.json';
    
    /**
     * Generate OAuth2 Access Token using JWT
     */
    private function getAccessToken() {
        if (!file_exists($this->serviceAccountFile)) {
            throw new Exception("Service account file not found. Please download it from Firebase Console.");
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
        
        // Exchange JWT for Access Token
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

    /**
     * Send Push Notification
     * @param string $target - FCM Device Token OR Topic (e.g. "/topics/all")
     * @param string $title
     * @param string $body
     * @param array $dataPayload
     */
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
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => $dataPayload
            ]
        ];
        
        if (strpos($target, '/topics/') === 0) {
            $message['message']['topic'] = str_replace('/topics/', '', $target);
        } else {
            $message['message']['token'] = $target;
        }
        
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
            "response" => json_decode($result, true)
        ];
    }
}

// ==========================================
// API Endpoint Logic
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['target']) || !isset($input['title']) || !isset($input['body'])) {
        echo json_encode(["status" => false, "message" => "Missing parameters (target, title, body)"]);
        exit;
    }
    
    try {
        $fcm = new FCMService();
        $result = $fcm->sendNotification($input['target'], $input['title'], $input['body'], $input['data'] ?? []);
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode(["status" => false, "message" => $e->getMessage()]);
    }
}
?>
