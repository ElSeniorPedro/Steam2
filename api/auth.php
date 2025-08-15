<?php
require_once("../config.php");
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'دسترسی غیرمجاز']);
    exit;
}

switch ($input['action']) {
    case 'login_promoter':
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');
        if (!$username || !$password) {
            echo json_encode(['success' => false, 'message' => 'همه فیلدها الزامی است.']);
            exit;
        }
        $db = getDB();
        $stmt = $db->prepare("SELECT u.id, u.username, u.password, u.active, p.status
                              FROM users u
                                JOIN promoters p ON u.id=p.user_id
                              WHERE u.username=? AND u.role='promoter' LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'کد پروموتر یا رمز اشتباه است.']);
            exit;
        }
        if (!$user['active'] || $user['status'] !== 'active') {
            echo json_encode(['success' => false, 'message' => 'حساب شما غیرفعال است.']);
            exit;
        }
        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'کد پروموتر یا رمز اشتباه است.']);
            exit;
        }
        // تولید توکن ساده (در پروژه واقعی JWT توصیه می‌شود)
        $token = bin2hex(random_bytes(24));
        // در صورت نیاز، می‌توانید توکن را در دیتابیس ذخیره کنید
        // ...
        // ثبت زمان آخرین ورود
        $db->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
        echo json_encode([
            'success' => true,
            'token' => $token
        ]);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'عملیات نامعتبر']);
}