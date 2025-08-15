<?php
require_once("../config.php");
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $join_date = $_POST['join_date'] ?? '';
    $type = $_POST['type'] ?? 'static';
    $province_id = (int)($_POST['province_id'] ?? 0);
    $city_id = (int)($_POST['city_id'] ?? 0);
    $profile_pic = $_FILES['profile_pic'] ?? null;

    if (!$username || !$first_name || !$last_name || !$phone || !$birth_date || !$join_date || !$province_id || !$city_id) {
        echo json_encode(['success'=>false,'message'=>'همه فیلدهای ستاره‌دار الزامی است.']);
        exit;
    }
    $db = getDB();
    $stmt = $db->prepare("SELECT p.id, p.profile_pic, u.id as user_id FROM promoters p JOIN users u ON u.id=p.user_id WHERE u.username=?");
    $stmt->execute([$username]);
    $p = $stmt->fetch();
    if(!$p){
        echo json_encode(['success'=>false,'message'=>'پروموتر یافت نشد.']);
        exit;
    }
    // عکس جدید
    $profile_pic_name = $p['profile_pic'];
    if ($profile_pic && $profile_pic['tmp_name']) {
        $typeImg = mime_content_type($profile_pic['tmp_name']);
        if (strpos($typeImg, 'image/') === 0) {
            $ext = pathinfo($profile_pic['name'], PATHINFO_EXTENSION);
            $profile_pic_name = uniqid('profile_') . '.' . $ext;
            move_uploaded_file($profile_pic['tmp_name'], "../assets/images/" . $profile_pic_name);
        }
    }
    $stmt2 = $db->prepare("UPDATE promoters SET first_name=?, last_name=?, phone=?, province_id=?, city_id=?, birth_date=?, join_date=?, type=?, profile_pic=? WHERE id=?");
    $stmt2->execute([
        $first_name, $last_name, $phone, $province_id, $city_id, $birth_date, $join_date, $type, $profile_pic_name, $p['id']
    ]);
    echo json_encode(['success'=>true,'message'=>'ویرایش اطلاعات با موفقیت انجام شد.']);
    exit;
}
echo json_encode(['success'=>false,'message'=>'درخواست نامعتبر.']);