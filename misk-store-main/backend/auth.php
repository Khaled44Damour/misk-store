<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function requireAdmin(): void {
    if (empty($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "يجب تسجيل دخول المدير أولاً"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
