<?php
require_once("../config.php");
header('Content-Type: application/json');

// بررسی توکن (در این نسخه ساده، فقط وجود توکن کفایت می‌کند)
// در نسخه پیشرفته، اعتبار سنجی توکن و استخراج اطلاعات کاربر توصیه می‌شود.
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'get_promoter_info') {
    // فرض بر این است که توکن در localStorage سمت کاربر است و در پروژه واقعی باید به کاربر متصل شود
    // اینجا برای سادگی، یوزرنیم کاربر را به عنوان token ارسال می‌کنیم
    $username = $input['username'] ?? '';
    if (!$username) {
        echo json_encode(['success' => false, 'message' => 'ورود نامعتبر']);
        exit;
    }
    $db = getDB();
    $stmt = $db->prepare("SELECT p.id, u.username, p.first_name, p.last_name, p.phone, 
                                 pr.name AS province, ci.name AS city, p.profile_pic, 
                                 p.status, p.type, u.active
                          FROM promoters p
                          JOIN users u ON u.id=p.user_id
                          LEFT JOIN provinces pr ON pr.id=p.province_id
                          LEFT JOIN cities ci ON ci.id=p.city_id
                          WHERE u.username=? LIMIT 1");
    $stmt->execute([$username]);
    $info = $stmt->fetch();
    if (!$info) {
        echo json_encode(['success' => false, 'message' => 'مشخصات یافت نشد']);
        exit;
    }
    // اسپات فعلی (برای نمونه فرضی: اولین اسپات ثبت شده در دیتابیس/در نسخه اصلی براساس لاگین و تخصیص پویا)
    $stmt2 = $db->prepare("SELECT s.id, s.name, s.code, s.lat, s.lng, pr.name as province, ci.name as city
                           FROM spots s
                           LEFT JOIN provinces pr ON pr.id=s.province_id
                           LEFT JOIN cities ci ON ci.id=s.city_id
                           LIMIT 1");
    $stmt2->execute();
    $spot = $stmt2->fetch();
    echo json_encode([
        'success' => true,
        'promoter' => $info,
        'spot' => $spot
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'عملیات نامعتبر']);