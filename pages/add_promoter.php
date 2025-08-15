<?php
require_once("../config.php");
session_start();
if (!isset($_SESSION['admin_login'])) {
    header("Location: login_admin.php");
    exit;
}
// دریافت استان و شهر
$db = getDB();
$provinces = $db->query("SELECT id,name FROM provinces ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>افزودن پروموتر | S!team2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../js/popup.js"></script>
    <script src="../js/sounds.js"></script>
</head>
<body>
    <div class="container" style="margin-top:30px;">
        <h2 style="text-align:center;margin-bottom:14px;">افزودن پروموتر جدید</h2>
        <form id="addPromoterForm" method="post" enctype="multipart/form-data" autocomplete="off">
            <label>نام *</label>
            <input type="text" name="first_name" maxlength="32" required>
            <label>نام خانوادگی *</label>
            <input type="text" name="last_name" maxlength="32" required>
            <label>شماره موبایل *</label>
            <input type="text" name="phone" maxlength="14" required>
            <label>کد پروموتر (نام کاربری) *</label>
            <input type="text" name="username" maxlength="32" required>
            <label>رمز عبور *</label>
            <input type="password" name="password" maxlength="32" required>
            <label>تاریخ تولد *</label>
            <input type="date" name="birth_date" required>
            <label>تاریخ عضویت *</label>
            <input type="date" name="join_date" required>
            <label>نوع پروموتر *</label>
            <select name="type" required>
                <option value="static">ثابت</option>
                <option value="tour">گردشی</option>
            </select>
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
            <label>عکس پروفایل (اختیاری)</label>
            <input type="file" name="profile_pic" accept="image/*">
            <button type="submit" class="btn">ثبت پروموتر</button>
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
    // ارسال فرم با Ajax
    document.getElementById('addPromoterForm').onsubmit = function(e){
        e.preventDefault();
        playSound('touch');
        let formData = new FormData(this);
        fetch('../api/add_promoter.php', {
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