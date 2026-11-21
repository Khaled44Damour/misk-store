<?php
/**
 * إعدادات الاتصال بقاعدة البيانات.
 * - على جهازك (localhost) يستخدم قسم "local" تلقائياً.
 * - على الاستضافة الحقيقية يستخدم قسم "production" تلقائياً.
 * هذا الملف محمي من الوصول المباشر عبر المتصفح (.htaccess).
 */
return [
    'local' => [
        'host' => 'localhost',
        'name' => 'misk_perfumes',
        'user' => 'root',
        'pass' => '',          // كلمة مرور XAMPP (غالباً فارغة)
    ],
    'production' => [
        'host' => 'localhost', // غالباً localhost، وتجده في لوحة الاستضافة
        'name' => 'CHANGE_ME_DB_NAME',
        'user' => 'CHANGE_ME_DB_USER',
        'pass' => 'CHANGE_ME_DB_PASSWORD',
    ],
];
