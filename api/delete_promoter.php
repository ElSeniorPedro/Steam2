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
$stmt = $db->prepare("SELECT u.id FROM users u WHERE u.username=?");
$stmt->execute([$username]);
$u = $stmt->fetch();
if(!$u){
    echo json_encode(['success'=>false,'message'=>'پروموتر یافت نشد.']);
    exit;
}
$stmt1 = $db->prepare("DELETE FROM promoters WHERE user_id=?");
$stmt1->execute([$u['id']]);
$stmt2 = $db->prepare("DELETE FROM users WHERE id=?");
$stmt2->execute([$u['id']]);
echo json_encode(['success'=>true,'message'=>'پروموتر با موفقیت حذف شد.']);