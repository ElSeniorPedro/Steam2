<?php
require_once("../config.php");
session_start();
if (!isset($_SESSION['admin_login'])) {
    header("Location: login_admin.php");
    exit;
}
$username = $_GET['username'] ?? '';
if (!$username) {
    header("Location: manage_promoter.php");
    exit;
}
$db = getDB();
$stmt = $db->prepare("SELECT p.*,u.username FROM promoters p JOIN users u ON u.id=p.user_id WHERE u.username=?");
$stmt->execute([$username]);
$p = $stmt->fetch();
if (!$p) {
    header("Location: manage_promoter.php");
    exit;
}
$provinces = $db->query("SELECT id,name FROM provinces ORDER BY name")->fetchAll();
$cities = $db->prepare("SELECT id,name FROM cities WHERE province_id=? ORDER BY name");
$cities->execute([$p['province_id']]);
$cities = $cities->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ویرایش پروموتر | S!team2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../js/popup.js"></script>
    <script src="../js/sounds.js"></script>
</head>
<body>
    <div class="container" style="margin-top:30px;">
        <h2 style="text-align:center;margin-bottom:14px;">ویرایش پروموتر</h2>
        <form id="editPromoterForm" method="post" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="username" value="<?= htmlspecialchars($p['username']) ?>">
            <label>نام *</label>
            <input type="text" name="first_name" maxlength="32" required value="<?= htmlspecialchars($p['first_name']) ?>">
            <label>نام خانوادگی *</label>
            <input type="text" name="last_name" maxlength="32" required value="<?= htmlspecialchars($p['last_name']) ?>">
            <label>شماره موبایل *</label>
            <input type="text" name="phone" maxlength="14" required value="<?= htmlspecialchars($p['phone']) ?>">
            <label>تاریخ تولد *</label>
            <input type="date" name="birth_date" required value="<?= htmlspecialchars($p['birth_date']) ?>">
            <label>تاریخ عضویت *</label>
            <input type="date" name="join_date" required value="<?= htmlspecialchars($p['join_date']) ?>">
            <label>نوع پروموتر *</label>
            <select name="type" required>
                <option value="static" <?= $p['type']=='static'?'selected':'' ?>>ثابت</option>
                <option value="tour" <?= $p['type']=='tour'?'selected':'' ?>>گردشی</option>
            </select>
            <label>استان *</label>
            <select name="province_id" id="province_id" required>
                <?php foreach($provinces as $pr): ?>
                    <option value="<?= $pr['id'] ?>" <?= $pr['id']==$p['province_id']?'selected':'' ?>><?= htmlspecialchars($pr['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>شهر *</label>
            <select name="city_id" id="city_id" required>
                <?php foreach($cities as $ct): ?>
                    <option value="<?= $ct['id'] ?>" <?= $ct['id']==$p['city_id']?'selected':'' ?>><?= htmlspecialchars($ct['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>عکس پروفایل (در صورت نیاز)</label>
            <input type="file" name="profile_pic" accept="image/*">
            <button type="submit" class="btn">ثبت تغییرات</button>
        </form>
        <div id="form-messages"></div>
        <button style="margin:18px auto 0 auto;display:block;" class="btn" onclick="location.href='manage_promoter.php'">بازگشت</button>
    </div>
    <script>
    document.getElementById('province_id').onchange = function() {
        let pid = this.value;
        let citySel = document.getElementById('city_id');
        citySel.innerHTML = '<option value="">در حال بارگذاری...</option>';
        fetch('../api/locations.php?province_id='+pid)
            .then(res=>res.json())
            .then(data=>{
                if(data.cities){
                    let html = '';
                    data.cities.forEach(function(city){
                        html += `<option value="${city.id}">${city.name}</option>`;
                    });
                    citySel.innerHTML = html;
                }
            });
    }
    document.getElementById('editPromoterForm').onsubmit = function(e){
        e.preventDefault();
        playSound('touch');
        let formData = new FormData(this);
        fetch('../api/edit_promoter.php', {
            method:'POST',
            body:formData
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                playSound('success');
                popupSuccess(data.message, function(){location.href='manage_promoter.php';});
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