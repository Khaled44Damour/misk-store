<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../db.php';

try {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'announcement_text'");
    $stmt->execute();
    $text = $stmt->fetchColumn();
    echo json_encode([
        "status" => "success",
        "announcement_text" => $text === false ? "" : $text
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(["status" => "error", "message" => safeError($e)], JSON_UNESCAPED_UNICODE);
}
?>
