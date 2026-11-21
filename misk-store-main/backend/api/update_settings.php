<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../db.php';
require_once '../auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "طريقة الطلب غير صحيحة"], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$text = trim($data['announcement_text'] ?? $_POST['announcement_text'] ?? '');

if (mb_strlen($text, 'UTF-8') > 300) {
    echo json_encode(["status" => "error", "message" => "النص طويل جداً (الحد الأقصى 300 حرف)"], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('announcement_text', :v)
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute([':v' => $text]);
    echo json_encode(["status" => "success", "message" => "تم حفظ نص الشريط بنجاح"], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(["status" => "error", "message" => "فشل الحفظ: " . safeError($e)], JSON_UNESCAPED_UNICODE);
}
?>
