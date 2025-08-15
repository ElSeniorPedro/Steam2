<?php
require_once("../config.php");
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
if (!$username) {
    echo json_encode(['success'=>false,'message'=>'کد پروموتر وارد نشده است.']);
    exit;
}
$db = getDB();
$stmt = $db->prepare("SELECT u.id, u.active, p.status FROM users u JOIN promoters p ON u.id=p.user_id WHERE u.username=?");
$stmt->execute([$username]);
$p = $stmt->fetch();
if(!$p){
    echo json_encode(['success'=>false,'message'=>'پروموتر یافت نشد.']);
    exit;
}
// سوئیچ وضعیت
$new_active = $p['active']?0:1;
$new_status = $p['status']=='active'?'inactive':'active';
// غیرفعال‌سازی رمز جدید تصادفی
$new_pass = bin2hex(random_bytes(6));
$stmt2 = $db->prepare("UPDATE users SET active=?, password=? WHERE id=?");
$stmt2->execute([$new_active, password_hash($new_pass, PASSWORD_BCRYPT), $p['id']]);
$stmt3 = $db->prepare("UPDATE promoters SET status=? WHERE user_id=?");
$stmt3->execute([$new_status, $p['id']]);
$msg = $new_active ? 'پروموتر مجدداً فعال شد.' : 'پروموتر غیرفعال و رمز جدید تصادفی شد.';
echo json_encode(['success'=>true,'message'=>$msg]);