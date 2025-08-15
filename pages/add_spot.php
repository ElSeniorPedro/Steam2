<?php
require_once("../config.php");
session_start();
if (!isset($_SESSION['admin_login'])) {
    header("Location: login_admin.php");
    exit;
}
$db = getDB();
$provinces = $db->query("SELECT id,name FROM provinces ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>افزودن اسپات | S!team2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../vendor/leaflet/leaflet.css">
    <script src="../vendor/leaflet/leaflet.js"></script>
    <script src="../js/sounds.js"></script>
    <script src="../js/popup.js"></script>
</head>
<body>
    <div class="container" style="margin-top:30px;">
        <h2 style="text-align:center;margin-bottom:14px;">افزودن اسپات جدید</h2>
        <form id="addSpotForm" autocomplete="off">
            <div style="display:flex;gap:16px;">
                <div style="flex:1;">
                    <label>نام اسپات *</label>
                    <input type="text" name="name" maxlength="64" required>
                    <label>کد اسپات *</label>
                    <input type="text" name="code" maxlength="32" required>
                    <label>استان *</label>
                    <select name="province_id" id="province_id" required>
                        <option value="">انتخاب استان</option>
                        <?php foreach($provinces as $pr): ?>
                            <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>شهر *</label>
                    <select name="city_id" id="city_id" required>
                        <option value="">اول استان را انتخاب کنید</option>
                    </select>
                </div>
                <div style="flex:1;">
                    <div id="map" style="width:100%;height:200px;border-radius:12px;margin-bottom:14px;"></div>
                    <input type="hidden" name="lat" id="lat" required>
                    <input type="hidden" name="lng" id="lng" required>
                    <div style="font-size:.93rem;color:#888;">با کلیک روی نقشه، موقعیت اسپات را انتخاب کنید.</div>
                </div>
            </div>
            <button type="submit" class="btn">ثبت اسپات</button>
        </form>
        <div id="form-messages"></div>
        <button style="margin:18px auto 0 auto;display:block;" class="btn" onclick="location.href='admin_dashboard.php'">بازگشت</button>
    </div>
    <script>
    // لود شهرها بر اساس استان
    document.getElementById('province_id').onchange = function() {
        let pid = this.value;
        let citySel = document.getElementById('city_id');
        citySel.innerHTML = '<option value="">در حال بارگذاری...</option>';
        fetch('../api/locations.php?province_id='+pid)
            .then(res=>res.json())
            .then(data=>{
                if(data.cities){
                    let html = '<option value="">انتخاب شهر</option>';
                    data.cities.forEach(function(city){
                        html += `<option value="${city.id}">${city.name}</option>`;
                    });
                    citySel.innerHTML = html;
                }
            });
    }
    // نقشه leaflet
    var map = L.map('map').setView([35.6892, 51.3890], 11); // مرکز ایران
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    var marker = null;
    map.on('click', function(e){
        if(marker) map.removeLayer(marker);
        marker = L.marker(e.latlng).addTo(map);
        document.getElementById('lat').value = e.latlng.lat;
        document.getElementById('lng').value = e.latlng.lng;
    });

    // ارسال فرم با Ajax
    document.getElementById('addSpotForm').onsubmit = function(e){
        e.preventDefault();
        playSound('touch');
        let formData = new FormData(this);
        fetch('../api/add_spot.php', {
            method:'POST',
            body:formData
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                playSound('success');
                popupSuccess(data.message, function(){location.href='admin_dashboard.php';});
            } else {
                playSound('error');
                popupError(data.message);
            }
        }).catch(()=>{
            playSound('error');
            popupError('خطا در ارتباط با سرور.');
        });
    }
    </script>
</body>
</html>