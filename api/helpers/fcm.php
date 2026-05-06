<?php
/**
 * Helper to send Firebase Cloud Messaging (FCM) notifications
 */

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

/**
 * Save notification to database and send push
 */
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
?>