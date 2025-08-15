<?php
// config.php
// تنظیمات اتصال به دیتابیس و متغیرهای ثابت پروژه

define('DB_HOST', 'localhost');         // آدرس هاست دیتابیس (در cPanel معمولاً localhost)
define('DB_NAME', 'steam2_db');         // نام دیتابیس (مطابق نامی که ساختید)
define('DB_USER', 'YOUR_DB_USER');      // یوزرنیم دیتابیس
define('DB_PASS', 'YOUR_DB_PASSWORD');  // پسورد دیتابیس
define('BASE_URL', 'https://westwolves.ir/'); // آدرس دامنه اصلی پروژه

// برای بارگذاری فونت وزیرمتن، صداها و لوتی از مسیر محلی یا CDN استفاده کنید
define('FONT_URL', BASE_URL . 'assets/fonts/Vazirmatn.woff2');
define('SOUNDS_URL', BASE_URL . 'assets/sounds/');
define('LOTTIE_URL', BASE_URL . 'assets/lottie/');

function getDB() {
    static $db = null;
    if ($db === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        try {
            $db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("خطا در اتصال به دیتابیس: " . $e->getMessage());
        }
    }
    return $db;
}
?>