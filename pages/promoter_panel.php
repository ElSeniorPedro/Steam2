<?php
require_once("../config.php");
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>پنل پروموتر | S!team2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../vendor/lottie/lottie.min.js"></script>
    <script src="../js/sounds.js"></script>
    <script src="../js/popup.js"></script>
    <script src="../js/auth.js"></script>
</head>
<body>
    <div class="container" id="main-panel" style="padding:0; max-width:100vw;">
        <!-- هدر -->
        <div style="height:25vh; display:flex; flex-direction:column; align-items:center; justify-content:center; background:linear-gradient(135deg, #000 0%, #333 100%); border-radius:0 0 30px 30px; box-shadow:0 4px 18px #0003; position:relative;">
            <!-- عکس پروفایل در بالا -->
            <div style="margin-bottom:15px;">
                <div id="profile-pic" style="width:90px;height:90px;border-radius:50%;background:#eee;box-shadow:0 0 20px #ffffff33, 0 6px 20px #0003;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                    <img id="profile-img" src="../assets/images/profile-default.png" alt="پروفایل" style="width:100%;height:100%;object-fit:cover;">
                </div>
            </div>
            <!-- اطلاعات کاربر زیر عکس -->
            <div style="text-align:center;" id="user-info">
                <div id="promoter-name" style="font-size:1.2rem;font-weight:bold;color:#fff;margin-bottom:8px;"></div>
                <div id="promoter-province-city" style="font-size:1rem;color:#ccc;margin-bottom:8px;"></div>
                <div id="promoter-status" style="font-size:1rem;font-weight:bold;"></div>
            </div>
            <!-- انیمیشن Lottie در گوشه -->
            <div style="position:absolute;top:15px;left:15px;">
                <div id="lottie-promoter-panel" style="height:60px;width:60px;opacity:0.7;"></div>
            </div>
        </div>
        <!-- بخش اصلی: دکمه‌ها -->
        <div style="height:65vh;display:flex;flex-direction:column;justify-content:center;align-items:center;">
            <!-- فاصله تا اسپات و وضعیت -->
            <div id="distance-info" style="font-size:1.14rem;margin-top:18px;margin-bottom:12px;"></div>
            <!-- دکمه‌ها -->
            <button class="btn" id="btn-activity">شروع فعالیت</button>
            <button class="btn" id="btn-qc">بررسی کیفیت</button>
            <button class="btn" id="btn-incident">گزارش برخورد یا حادثه</button>
            <button class="btn" id="btn-support">درخواست پشتیبانی فوری</button>
        </div>
    </div>
    <!-- فوتر: دکمه خروج -->
    <div class="footer">
        <div class="steam-animation">S!Team</div>
        <button class="logout-btn btn" onclick="logoutPromoter()">خروج</button>
    </div>
    <script>
    // بارگذاری انیمیشن Lottie
    lottie.loadAnimation({
        container: document.getElementById('lottie-promoter-panel'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: '<?php echo LOTTIE_URL; ?>promoter-panel.json'
    });

    // دریافت اطلاعات کاربر و اسپات
    async function loadPromoterPanel() {
        let token = getPromoterToken();
        if (!token) {
            window.location.href = 'login_promoter.php';
            return;
        }
        // فرض: یوزرنیم در توکن ذخیره شده است (در نسخه واقعی باید JWT یا مشابه باشد)
        let username = token;
        try {
            let resp = await fetch('../api/promoters.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'get_promoter_info',
                    username: username
                })
            });
            let data = await resp.json();
            if (!data.success) {
                popupError(data.message || 'خطا در دریافت اطلاعات');
                return;
            }
            // اطلاعات پروموتر
            let p = data.promoter;
            document.getElementById('promoter-name').textContent = `${p.first_name} ${p.last_name}`;
            document.getElementById('promoter-province-city').textContent = `${p.province || ''}، ${p.city || ''}`;
            document.getElementById('promoter-status').textContent = (p.status === 'active' ? 'فعال' : 'غیرفعال');
            document.getElementById('promoter-status').style.color = (p.status === 'active' ? '#4ade80' : '#f87171');
            if (p.profile_pic) {
                document.getElementById('profile-img').src = '../assets/images/'+p.profile_pic;
            }
            // اطلاعات اسپات (نمونه: اولین اسپات دیتابیس)
            let s = data.spot;
            if (s && p.status === 'active') {
                navigator.geolocation.getCurrentPosition(function(position){
                    let dist = getDistanceFromLatLonInMeter(
                        position.coords.latitude, position.coords.longitude,
                        s.lat, s.lng
                    );
                    let color = '#22c55e';
                    if (dist > 300) color = '#e11d48';
                    else if (dist > 200) color = '#f59e42';
                    document.getElementById('distance-info').innerHTML = `
                        فاصله شما تا اسپات <span style="color:${color};font-weight:bold;">${Math.round(dist)} متر</span>
                        <br><span style="font-size:.97rem;color:#888">${s.name} (${s.code}) - ${s.province}، ${s.city}</span>
                    `;
                }, function(err){
                    document.getElementById('distance-info').textContent = 'دسترسی به موقعیت جغرافیایی غیرفعال است';
                });
            } else if (p.status !== 'active') {
                document.getElementById('distance-info').innerHTML = '<span style="color:#e11d48;font-weight:bold;">غیرفعال</span>';
            } else {
                document.getElementById('distance-info').textContent = 'اسپات تخصیص داده نشده است.';
            }
        } catch (err) {
            popupError('خطا در بارگذاری اطلاعات');
        }
    }

    // محاسبه فاصله دو نقطه جغرافیایی (متر)
    function getDistanceFromLatLonInMeter(lat1, lon1, lat2, lon2) {
        let R = 6371000; // متر
        let dLat = (lat2 - lat1) * Math.PI/180;
        let dLon = (lon2 - lon1) * Math.PI/180;
        let a =
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
            Math.sin(dLon/2) * Math.sin(dLon/2)
        ;
        let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        let d = R * c;
        return d;
    }

    // بارگذاری اولیه پنل
    loadPromoterPanel();

    // دکمه‌های اصلی (برای ادامه توسعه هر بخش)
    document.getElementById('btn-activity').onclick = function() {
        playSound('touch');
        window.location.href = 'promoter_activity.php';
    };
    document.getElementById('btn-qc').onclick = function() {
        playSound('touch');
        window.location.href = 'qc_form.php';
    };
    document.getElementById('btn-incident').onclick = function() {
        playSound('touch');
        window.location.href = 'incident_report.php';
    };
    document.getElementById('btn-support').onclick = function() {
        playSound('touch');
        window.location.href = 'support_request.php';
    };
    </script>
</body>
</html>