<?php
/**
 * Alternative QR Code Generator using Google Charts API
 * Use this if GD extension cannot be enabled
 */

function generateQRCodeWithGoogleAPI($qrcode, $file_path) {
    $qr_url = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($qrcode);
    
    // Create directory if it doesn't exist
    $directory = dirname($file_path);
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    // Download QR code image from Google Charts API
    $qr_image = file_get_contents($qr_url);
    
    if ($qr_image !== false) {
        file_put_contents($file_path, $qr_image);
        return true;
    }
    
    return false;
}

// Alternative QR code generation using external service
function generateQRCodeWithQRServer($qrcode, $file_path) {
    // Using qr-server.com API (free service)
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrcode);
    
    // Create directory if it doesn't exist
    $directory = dirname($file_path);
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    // Download QR code image
    $context = stream_context_create([
        'http' => [
            'timeout' => 10, // 10 seconds timeout
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $qr_image = file_get_contents($qr_url, false, $context);
    
    if ($qr_image !== false) {
        file_put_contents($file_path, $qr_image);
        return true;
    }
    
    return false;
}
?>