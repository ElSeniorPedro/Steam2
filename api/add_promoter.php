<?php
require_once("../config.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $join_date = $_POST['join_date'] ?? '';
    $type = $_POST['type'] ?? 'static';
    $province_id = (int)($_POST['province_id'] ?? 0);
    $city_id = (int)($_POST['city_id'] ?? 0);
    $profile_pic = $_FILES['profile_pic'] ?? null;

    if (!$first_name || !$last_name || !$phone || !$username || !$password || !$birth_date || !$join_date || !$province_id || !$city_id) {
        echo json_encode(['success'=>false,'message'=>'همه فیلدهای ستاره‌دار الزامی است.']);
        exit;
    }
    // چک یکتایی یوزرنیم
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE username=?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success'=>false,'message'=>'کد پروموتر تکراری است.']);
        exit;
    }
    // ثبت کاربر
    $stmt = $db->prepare("INSERT INTO users (username,password,role,active) VALUES (?,?,?,1)");
    $stmt->execute([$username, password_hash($password, PASSWORD_BCRYPT), 'promoter']);
    $user_id = $db->lastInsertId();

    // آپلود عکس پروفایل
    $profile_pic_name = null;
    if ($profile_pic && $profile_pic['tmp_name']) {
        $type = mime_content_type($profile_pic['tmp_name']);
        if (strpos($type, 'image/') === 0) {
            $ext = pathinfo($profile_pic['name'], PATHINFO_EXTENSION);
            $profile_pic_name = uniqid('profile_') . '.' . $ext;
            move_uploaded_file($profile_pic['tmp_name'], "../assets/images/" . $profile_pic_name);
        }
    }
    // ثبت اطلاعات پروموتر
    $stmt = $db->prepare("INSERT INTO promoters (user_id, first_name, last_name, phone, province_id, city_id, birth_date, join_date, type, profile_pic, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $user_id, $first_name, $last_name, $phone, $province_id, $city_id, $birth_date, $join_date, $type, $profile_pic_name, 'active'
    ]);
    echo json_encode(['success'=>true,'message'=>'پروموتر با موفقیت افزوده شد.']);
    exit;
}
echo json_encode(['success'=>false,'message'=>'درخواست نامعتبر.']);