<?php
require_once("../config.php");
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>شروع/پایان فعالیت | S!team2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../vendor/lottie/lottie.min.js"></script>
    <script src="../js/sounds.js"></script>
    <script src="../js/popup.js"></script>
    <script src="../js/auth.js"></script>
</head>
<body>
    <div class="container" style="margin-top:30px;">
        <div class="header-lottie" id="lottie-activity"></div>
        <h2 style="text-align:center;margin-bottom:14px;">شروع / پایان فعالیت</h2>
        <div id="activity-box" style="display:none;">
            <!-- فرم کد اسپات فقط برای ثابت -->
            <form id="spotForm" style="display:none;">
                <label for="spot_code">لطفا کد اسپات محل فعالیت خود را مشخص کنید *</label>
                <input type="text" id="spot_code" name="spot_code" placeholder="کد اسپات" required maxlength="32" autocomplete="off">
                <button type="button" class="btn" id="btn-verify-spot">بررسی و شروع</button>
            </form>
            <!-- دکمه پایان فعالیت -->
            <button type="button" class="btn" id="btn-end-activity" style="display:none;">پایان فعالیت</button>
            <div id="status-msg" style="text-align:center;margin:20px 0 0 0;color:#666;font-size:1.07rem;"></div>
        </div>
    </div>
    <script>
    lottie.loadAnimation({
        container: document.getElementById('lottie-activity'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: '<?php echo LOTTIE_URL; ?>activity.json'
    });

    let promoterType = null, username = null;
    let spotCode = "";
    let activityStarted = false;
    let locationCheckInterval = null;
    let warned250 = false, warned300 = false;

    async function initActivityPanel() {
        // دریافت نوع پروموتر
        username = getPromoterToken();
        if (!username) {
            window.location.href = 'login_promoter.php';
            return;
        }
        // دریافت اطلاعات پروموتر
        let resp = await fetch('../api/promoters.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'get_promoter_info', username: username})
        });
        let data = await resp.json();
        if (data.success) {
            promoterType = data.promoter.type;
            document.getElementById('activity-box').style.display = 'block';
            if (promoterType === 'static') {
                document.getElementById('spotForm').style.display = 'block';
                document.getElementById('btn-end-activity').style.display = 'none';
            } else {
                // پروموتر گردشی: فقط ارسال لوکیشن هر ۱۰ دقیقه
                document.getElementById('status-msg').innerHTML = 'وضعیت: <span style="color:#22c55e;font-weight:bold;">گردشی</span><br>موقعیت شما هر ۱۰ دقیقه ثبت خواهد شد.';
                startTourLocationLoop();
            }
        } else {
            popupError('خطا در دریافت اطلاعات کاربر');
        }
    }

    // برای گردشی: ثبت اتوماتیک هر ۱۰ دقیقه
    function startTourLocationLoop() {
        sendTourLocation(); // اولین بار
        setInterval(sendTourLocation, 10 * 60 * 1000);
    }
    function sendTourLocation() {
        navigator.geolocation.getCurrentPosition(function(pos){
            fetch('../api/activities.php', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({
                    action: 'start_activity',
                    username: username,
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude
                })
            });
        });
    }

    // رویداد دکمه بررسی و شروع
    document.getElementById('btn-verify-spot').onclick = async function() {
        playSound('touch');
        spotCode = document.getElementById('spot_code').value.trim();
        if (!spotCode) {
            popupError('کد اسپات را وارد کنید.');
            playSound('error');
            return;
        }
        document.getElementById('status-msg').textContent = 'در حال دریافت موقعیت...';
        navigator.geolocation.getCurrentPosition(async function(pos){
            let lat = pos.coords.latitude, lng = pos.coords.longitude;
            let resp = await fetch('../api/activities.php', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({
                    action:'start_activity',
                    username: username,
                    spot_code: spotCode,
                    lat: lat,
                    lng: lng
                })
            });
            let data = await resp.json();
            if (data.success) {
                playSound('success');
                document.getElementById('status-msg').innerHTML = `<span style="color:#16a34a">${data.message}</span>`;
                document.getElementById('spotForm').style.display = 'none';
                document.getElementById('btn-end-activity').style.display = 'block';
                activityStarted = true;
                startLocationCheckLoop();
            } else {
                playSound('error');
                document.getElementById('status-msg').innerHTML = `<span style="color:#e11d48">${data.message}</span>`;
            }
        }, function(){
            playSound('error');
            document.getElementById('status-msg').innerHTML = `<span style="color:#e11d48">دسترسی به موقعیت مکانی فعال نیست.</span>`;
        });
    };

    // دکمه پایان فعالیت
    document.getElementById('btn-end-activity').onclick = function() {
        playSound('touch');
        navigator.geolocation.getCurrentPosition(async function(pos){
            let lat = pos.coords.latitude, lng = pos.coords.longitude;
            let resp = await fetch('../api/activities.php', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify({
                    action:'end_activity',
                    username: username,
                    spot_code: spotCode,
                    lat: lat,
                    lng: lng
                })
            });
            let data = await resp.json();
            if (data.success) {
                playSound('success');
                popupSuccess(data.message, function(){
                    window.location.href = 'promoter_panel.php';
                });
            } else {
                playSound('error');
                popupError(data.message);
            }
        }, function(){
            playSound('error');
            popupError('دسترسی به موقعیت مکانی فعال نیست.');
        });
    };

    // حلقه چک فاصله حین فعالیت (برای هشدار و پایان خودکار)
    function startLocationCheckLoop() {
        locationCheckInterval = setInterval(function(){
            navigator.geolocation.getCurrentPosition(async function(pos){
                let lat = pos.coords.latitude, lng = pos.coords.longitude;
                // بررسی فاصله با اسپات
                let resp = await fetch('../api/promoters.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'get_promoter_info', username: username})
                });
                let data = await resp.json();
                if (data.success && data.spot) {
                    let s = data.spot;
                    let dist = getDistanceFromLatLonInMeter(lat, lng, s.lat, s.lng);
                    if (dist > 250 && !warned250 && dist <= 300) {
                        warned250 = true;
                        playSound('error');
                        popupError('شما در حال خروج از محل فعالیت هستید، در صورت پایان فعالیت، لطفا دکمه پایان فعالیت را لمس کنید.');
                    }
                    if (dist > 300 && !warned300) {
                        warned300 = true;
                        // پایان خودکار
                        let resp2 = await fetch('../api/activities.php', {
                            method:'POST',
                            headers:{'Content-Type':'application/json'},
                            body: JSON.stringify({
                                action:'location_end',
                                username: username,
                                spot_code: spotCode,
                                lat: lat,
                                lng: lng
                            })
                        });
                        let data2 = await resp2.json();
                        playSound('error');
                        popupError('به علت خروج از محدوده، فعالیت شما پایان یافت!', function(){
                            window.location.href = 'promoter_panel.php';
                        });
                        clearInterval(locationCheckInterval);
                    }
                }
            });
        }, 7000); // هر ۷ ثانیه چک شود
    }

    // محاسبه فاصله جغرافیایی
    function getDistanceFromLatLonInMeter(lat1, lon1, lat2, lon2) {
        let R = 6371000; // متر
        let dLat = (lat2-lat1) * Math.PI/180;
        let dLon = (lon2-lon1) * Math.PI/180;
        let a =
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
            Math.sin(dLon/2) * Math.sin(dLon/2)
        ;
        let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        let d = R * c;
        return d;
    }

    // بارگذاری اولیه
    initActivityPanel();
    </script>
</body>
</html>