<?php
require_once("../config.php");
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$action = $input['action'] ?? '';
$username = $input['username'] ?? '';

if (!$username) {
    echo json_encode(['success' => false, 'message' => 'ورود نامعتبر']);
    exit;
}

$db = getDB();

// دریافت اطلاعات پروموتر و نوع آن
$stmt = $db->prepare("SELECT p.id, p.type, p.status, u.active 
                      FROM promoters p 
                      JOIN users u ON u.id=p.user_id 
                      WHERE u.username=?");
$stmt->execute([$username]);
$promoter = $stmt->fetch();
if (!$promoter || !$promoter['active'] || $promoter['status'] !== 'active') {
    echo json_encode(['success' => false, 'message' => 'دسترسی پروموتر نامعتبر یا غیرفعال است.']);
    exit;
}

switch ($action) {
    // شروع فعالیت (پروموتر ثابت)
    case 'start_activity':
        $spot_code = trim($input['spot_code'] ?? '');
        $lat = $input['lat'] ?? null;
        $lng = $input['lng'] ?? null;
        if ($promoter['type'] === 'static') {
            if (!$spot_code || !$lat || !$lng) {
                echo json_encode(['success' => false, 'message' => 'کد اسپات و موقعیت جغرافیایی الزامی است.']);
                exit;
            }
            // دریافت مشخصات اسپات
            $spotStmt = $db->prepare("SELECT id, lat, lng, name FROM spots WHERE code=? LIMIT 1");
            $spotStmt->execute([$spot_code]);
            $spot = $spotStmt->fetch();
            if (!$spot) {
                echo json_encode(['success' => false, 'message' => 'کد اسپات تعریف نشده است.']);
                exit;
            }
            // محاسبه فاصله
            $distance = haversine($lat, $lng, $spot['lat'], $spot['lng']);
            if ($distance > 500) {
                echo json_encode(['success' => false, 'message' => 'فاصله شما با محل اسپات بیشتر از ۵۰۰ متر است.']);
                exit;
            }
            // ثبت فعالیت
            $db->prepare("INSERT INTO activities (promoter_id, spot_id, type, lat, lng, datetime) VALUES (?,?,?,?,?,NOW())")
                ->execute([$promoter['id'], $spot['id'], 'start', $lat, $lng]);
            echo json_encode(['success' => true, 'message' => 'فعالیت شما آغاز شد.', 'spot_name' => $spot['name']]);
            exit;
        } else {
            // پروموتر گردشی
            $db->prepare("INSERT INTO activities (promoter_id, type, lat, lng, datetime) VALUES (?,?,?,?,NOW())")
                ->execute([$promoter['id'], 'tour', $lat, $lng]);
            echo json_encode(['success' => true, 'message' => 'ثبت موقعیت گردشی موفق.']);
            exit;
        }
    // پایان فعالیت (پروموتر ثابت)
    case 'end_activity':
        $spot_code = trim($input['spot_code'] ?? '');
        $lat = $input['lat'] ?? null;
        $lng = $input['lng'] ?? null;
        if ($promoter['type'] === 'static') {
            if (!$spot_code || !$lat || !$lng) {
                echo json_encode(['success' => false, 'message' => 'کد اسپات و موقعیت جغرافیایی الزامی است.']);
                exit;
            }
            $spotStmt = $db->prepare("SELECT id, lat, lng, name FROM spots WHERE code=? LIMIT 1");
            $spotStmt->execute([$spot_code]);
            $spot = $spotStmt->fetch();
            if (!$spot) {
                echo json_encode(['success' => false, 'message' => 'کد اسپات تعریف نشده است.']);
                exit;
            }
            $distance = haversine($lat, $lng, $spot['lat'], $spot['lng']);
            if ($distance > 500) {
                echo json_encode(['success' => false, 'message' => 'فاصله شما با محل اسپات بیشتر از ۵۰۰ متر است.']);
                exit;
            }
            $db->prepare("INSERT INTO activities (promoter_id, spot_id, type, lat, lng, datetime) VALUES (?,?,?,?,?,NOW())")
                ->execute([$promoter['id'], $spot['id'], 'end', $lat, $lng]);
            echo json_encode(['success' => true, 'message' => 'پایان فعالیت ثبت شد.', 'spot_name' => $spot['name']]);
            exit;
        } else {
            $db->prepare("INSERT INTO activities (promoter_id, type, lat, lng, datetime) VALUES (?,?,?,?,NOW())")
                ->execute([$promoter['id'], 'tour', $lat, $lng]);
            echo json_encode(['success' => true, 'message' => 'ثبت موقعیت گردشی موفق.']);
            exit;
        }
    // پایان خودکار فعالیت در صورت خروج از محدوده (پروموتر ثابت)
    case 'location_end':
        $spot_code = trim($input['spot_code'] ?? '');
        $lat = $input['lat'] ?? null;
        $lng = $input['lng'] ?? null;
        if ($promoter['type'] === 'static') {
            if (!$spot_code || !$lat || !$lng) {
                echo json_encode(['success' => false, 'message' => 'کد اسپات و موقعیت جغرافیایی الزامی است.']);
                exit;
            }
            $spotStmt = $db->prepare("SELECT id, lat, lng FROM spots WHERE code=? LIMIT 1");
            $spotStmt->execute([$spot_code]);
            $spot = $spotStmt->fetch();
            if (!$spot) {
                echo json_encode(['success' => false, 'message' => 'کد اسپات تعریف نشده است.']);
                exit;
            }
            $distance = haversine($lat, $lng, $spot['lat'], $spot['lng']);
            if ($distance > 300) {
                $db->prepare("INSERT INTO activities (promoter_id, spot_id, type, lat, lng, datetime) VALUES (?,?,?,?,?,NOW())")
                    ->execute([$promoter['id'], $spot['id'], 'location_end', $lat, $lng]);
                echo json_encode(['success' => true, 'message' => 'فعالیت شما به علت خروج از محدوده خاتمه یافت.']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'شما هنوز در محدوده مجاز هستید.']);
                exit;
            }
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'عملیات نامعتبر']);
}

// تابع محاسبه فاصله دو نقطه جغرافیایی (متر)
function haversine($lat1, $lon1, $lat2, $lon2) {
    $R = 6371000; // متر
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $d = $R * $c;
    return $d;
}