<?php
require_once("../config.php");
header('Content-Type: application/json');

// فقط POST مجاز است
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $organization = $_POST['organization'] ?? '';
    $description = $_POST['description'] ?? '';
    $lat = $_POST['lat'] ?? null;
    $lng = $_POST['lng'] ?? null;

    $photo = $_FILES['photo'] ?? null;
    $video = $_FILES['video'] ?? null;
    $audio = $_FILES['audio'] ?? null;

    if (!$username || !$organization || !$description) {
        echo json_encode(['success' => false, 'message' => 'همه فیلدهای ستاره‌دار الزامی است.']);
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

    $targetDir = "../assets/uploads/incidents/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    // آپلود عکس
    $photoName = null;
    if ($photo && $photo['tmp_name']) {
        $photoType = mime_content_type($photo['tmp_name']);
        if (strpos($photoType, 'image/') === 0) {
            $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
            $photoName = uniqid('photo_') . '.' . $ext;
            move_uploaded_file($photo['tmp_name'], $targetDir . $photoName);
        }
    }
    // آپلود ویدیو
    $videoName = null;
    if ($video && $video['tmp_name']) {
        $videoType = mime_content_type($video['tmp_name']);
        if (strpos($videoType, 'video/') === 0) {
            $ext = pathinfo($video['name'], PATHINFO_EXTENSION);
            $videoName = uniqid('video_') . '.' . $ext;
            move_uploaded_file($video['tmp_name'], $targetDir . $videoName);
        }
    }
    // آپلود صوت
    $audioName = null;
    if ($audio && $audio['tmp_name']) {
        $audioType = mime_content_type($audio['tmp_name']);
        if (strpos($audioType, 'audio/') === 0) {
            $ext = pathinfo($audio['name'], PATHINFO_EXTENSION);
            $audioName = uniqid('audio_') . '.' . $ext;
            move_uploaded_file($audio['tmp_name'], $targetDir . $audioName);
        }
    }

    // ثبت در دیتابیس
    $stmt2 = $db->prepare("INSERT INTO incidents (promoter_id, datetime, organization, description, photo, video, audio, lat, lng) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)");
    $stmt2->execute([
        $p['id'], $organization, $description, $photoName, $videoName, $audioName, $lat, $lng
    ]);
    echo json_encode(['success' => true, 'message' => 'گزارش با موفقیت ثبت شد.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر.']);