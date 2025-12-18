<?php
/**
 * Debug script untuk test AJAX email notification
 */

// Simulate AJAX request
$_POST['action'] = 'send_single';
$_POST['student_id'] = '6'; // FAIRUZ PUTRA SEMA

// Include the controller
ob_start();
include 'controllers/send_email_notification.php';
$response = ob_get_clean();

echo "=== RESPONSE FROM CONTROLLER ===\n";
echo $response . "\n\n";

// Decode and display
$data = json_decode($response, true);
if ($data) {
    echo "=== DECODED RESPONSE ===\n";
    echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
    echo "Message: " . $data['message'] . "\n";
} else {
    echo "ERROR: Failed to decode JSON response\n";
    echo "Raw response: $response\n";
}
