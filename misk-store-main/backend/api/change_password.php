<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../db.php';
require_once '../auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "طريقة الطلب غير صحيحة"], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];
$current = (string)($data['current_password'] ?? '');
$new = (string)($data['new_password'] ?? '');

if (mb_strlen($new, 'UTF-8') < 8) {
    echo json_encode(["status" => "error", "message" => "كلمة المرور الجديدة يجب ألا تقل عن 8 أحرف"], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT admin_password FROM admins WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['admin_id']]);
    $hash = $stmt->fetchColumn();

    if (!$hash || !password_verify($current, $hash)) {
        sleep(1);
        echo json_encode(["status" => "error", "message" => "كلمة المرور الحالية غير صحيحة"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE admins SET admin_password = :p WHERE id = :id");
    $stmt->execute([':p' => password_hash($new, PASSWORD_DEFAULT), ':id' => $_SESSION['admin_id']]);
    echo json_encode(["status" => "success", "message" => "تم تغيير كلمة المرور بنجاح"], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(["status" => "error", "message" => safeError($e)], JSON_UNESCAPED_UNICODE);
}
?>
