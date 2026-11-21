<?php
$config = require __DIR__ . '/config.php';

$hostName = strtolower(preg_replace('/:\\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost'));
$isLocal = in_array($hostName, ['localhost', '127.0.0.1', '[::1]'], true)
    || substr($hostName, -6) === '.local' || substr($hostName, -5) === '.test';
$db = $isLocal ? $config['local'] : $config['production'];

if (!$isLocal) {
    // على الاستضافة: لا نعرض أخطاء PHP للزوار
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
        $db['user'],
        $db['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    ensureSchema($pdo);
} catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $isLocal
            ? "فشل الاتصال بقاعدة البيانات: " . $exception->getMessage()
            : "تعذر الاتصال بقاعدة البيانات، تأكد من إعدادات backend/config.php"
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * توحيد قيمة القسم إلى إحدى القيم الثلاث المعتمدة: نسائي / رجالي / بوكس
 * يرجع '' إذا كانت القيمة غير معروفة.
 */
function normalizeCategory($value): string {
    $v = mb_strtolower(trim((string)$value), 'UTF-8');
    $v = str_replace(['أ', 'إ', 'آ'], 'ا', $v);
    if ($v === '') return '';
    if (strpos($v, 'نسا') !== false || in_array($v, ['women', 'woman', 'female', 'ladies'], true)) return 'نسائي';
    if (strpos($v, 'رجال') !== false || in_array($v, ['men', 'man', 'male'], true)) return 'رجالي';
    if (strpos($v, 'بوكس') !== false || strpos($v, 'box') !== false || strpos($v, 'هدا') !== false || $v === 'gift') return 'بوكس';
    return '';
}

/** يضمن وجود الجداول والأعمدة المطلوبة (آمن للتكرار). */
function ensureSchema(PDO $pdo): void {
    try {
        $col = $pdo->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'category'")->fetchColumn();
        if ($col && strtolower($col) !== 'varchar') {
            $pdo->exec("ALTER TABLE products MODIFY category VARCHAR(60) NOT NULL DEFAULT ''");
        }
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(80) NOT NULL,
            setting_value TEXT NULL,
            PRIMARY KEY (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('announcement_text', 'شحن سريع وآمن إلى جميع المحافظات ✦ عطر يليق بك، في كل لحظة')");
    } catch (Throwable $e) {
        // لا نوقف الموقع إذا لم تتوفر صلاحية تعديل الجداول
    }
}

/** رسالة خطأ آمنة: التفاصيل التقنية تظهر محلياً فقط. */
function safeError(Throwable $e): string {
    global $isLocal;
    if ($isLocal || $e instanceof RuntimeException) return $e->getMessage();
    return 'حدث خطأ في الخادم، حاول مرة أخرى';
}
