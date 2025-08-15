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
$stmt = $db->prepare("SELECT p.*,u.username,pr.name as province,ci.name as city
    FROM promoters p
    JOIN users u ON u.id=p.user_id
    LEFT JOIN provinces pr ON pr.id=p.province_id
    LEFT JOIN cities ci ON ci.id=p.city_id
    WHERE u.username=?");
$stmt->execute([$username]);
$p = $stmt->fetch();
if(!$p) {
    echo json_encode(['success'=>false,'message'=>'پروموتر یافت نشد.']);
    exit;
}
echo json_encode(['success'=>true,'promoter'=>$p]);