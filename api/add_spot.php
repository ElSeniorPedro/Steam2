<?php
require_once("../config.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $province_id = (int)($_POST['province_id'] ?? 0);
    $city_id = (int)($_POST['city_id'] ?? 0);
    $lat = floatval($_POST['lat'] ?? 0);
    $lng = floatval($_POST['lng'] ?? 0);

    if (!$name || !$code || !$province_id || !$city_id || !$lat || !$lng) {
        echo json_encode(['success'=>false,'message'=>'همه فیلدها الزامی است.']);
        exit;
    }
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM spots WHERE code=?");
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        echo json_encode(['success'=>false,'message'=>'کد اسپات تکراری است.']);
        exit;
    }
    $stmt = $db->prepare("INSERT INTO spots (name, code, province_id, city_id, lat, lng) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$name, $code, $province_id, $city_id, $lat, $lng]);
    echo json_encode(['success'=>true,'message'=>'اسپات با موفقیت افزوده شد.']);
    exit;
}
echo json_encode(['success'=>false,'message'=>'درخواست نامعتبر.']);