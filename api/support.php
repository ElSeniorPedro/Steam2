<?php
require_once("../config.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $spot_code = $_POST['spot_code'] ?? '';
    $db = getDB();

    $stmt = $db->prepare("SELECT p.id FROM promoters p JOIN users u ON u.id=p.user_id WHERE u.username=?");
    $stmt->execute([$username]);
    $p = $stmt->fetch();
    if (!$p) {
        echo json_encode(['success' => false, 'message' => 'پروموتر یافت نشد.']);
        exit;
    }

    $spot_id = null;
    if ($spot_code) {
        $stmt2 = $db->prepare("SELECT id FROM spots WHERE code=?");
        $stmt2->execute([$spot_code]);
        $s = $stmt2->fetch();
        if ($s) $spot_id = $s['id'];
    }

    $db->prepare("INSERT INTO support_requests (promoter_id, spot_id, datetime) VALUES (?, ?, NOW())")
        ->execute([$p['id'], $spot_id]);
    echo json_encode(['success' => true, 'message' => 'درخواست پشتیبانی فوری ثبت شد.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر.']);