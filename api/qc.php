<?php
require_once("../config.php");
header('Content-Type: application/json');

// فقط POST مجاز است
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $answers = $_POST['answers'] ?? '';
    $lat = $_POST['lat'] ?? null;
    $lng = $_POST['lng'] ?? null;

    // فایل‌های سلفی و ویدیو
    $selfie = $_FILES['selfie'] ?? null;
    $video = $_FILES['video'] ?? null;

    if (!$username || !$answers || !$selfie || !$video) {
        echo json_encode(['success' => false, 'message' => 'همه فیلدها الزامی است.']);
        exit;
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT p.id FROM promoters p JOIN users u ON u.id=p.user_id WHERE u.username=?");
    $stmt->execute([$username]);
    $p = $stmt->fetch();
    if (!$p) {
        echo json_encode(['success' => false, 'message' => 'پروموتر یافت نشد.']);
        exit;
    }

    // آپلود فایل‌ها
    $targetDir = "../assets/uploads/qc/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    // سلفی: فقط تصویر و فقط دوربین
    $selfieType = mime_content_type($selfie['tmp_name']);
    if (strpos($selfieType, 'image/') !== 0) {
        echo json_encode(['success' => false, 'message' => 'فایل سلفی باید تصویر باشد.']);
        exit;
    }
    $extSelfie = pathinfo($selfie['name'], PATHINFO_EXTENSION);
    $selfieName = uniqid('selfie_') . '.' . $extSelfie;
    move_uploaded_file($selfie['tmp_name'], $targetDir . $selfieName);

    // ویدیو: فقط ویدیو
    $videoType = mime_content_type($video['tmp_name']);
    if (strpos($videoType, 'video/') !== 0) {
        echo json_encode(['success' => false, 'message' => 'فایل ویدیو باید ویدیو باشد.']);
        exit;
    }
    $extVideo = pathinfo($video['name'], PATHINFO_EXTENSION);
    $videoName = uniqid('video_') . '.' . $extVideo;
    move_uploaded_file($video['tmp_name'], $targetDir . $videoName);

    // ثبت در دیتابیس
    $stmt2 = $db->prepare("INSERT INTO qc_reports (promoter_id, datetime, answers, selfie, video, lat, lng) VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    $stmt2->execute([
        $p['id'], $answers, $selfieName, $videoName, $lat, $lng
    ]);
    echo json_encode(['success' => true, 'message' => 'اطلاعات با موفقیت ارسال شد.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر.']);