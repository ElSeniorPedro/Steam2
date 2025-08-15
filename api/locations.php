<?php
require_once("../config.php");
header('Content-Type: application/json');
$province_id = (int)($_GET['province_id'] ?? 0);
if ($province_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name FROM cities WHERE province_id=? ORDER BY name");
    $stmt->execute([$province_id]);
    $cities = $stmt->fetchAll();
    echo json_encode(['cities'=>$cities]);
} else {
    echo json_encode(['cities'=>[]]);
}