<?php
require_once("../config.php");

// بررسی اگر کاربر قبلاً لاگین کرده (localStorage سمت کلاینت) و انتقال به پنل
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ورود پروموتر | S!team2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../vendor/lottie/lottie.min.js"></script>
    <script src="../js/sounds.js"></script>
    <script src="../js/popup.js"></script>
    <script src="../js/auth.js"></script>
</head>
<body>
    <div class="container">
        <div class="header-lottie" id="lottie-login"></div>
        <h2 style="text-align:center; margin-bottom:18px;">ورود پروموتر</h2>
        <form id="loginForm" autocomplete="off">
            <label for="username">کد پروموتر *</label>
            <input type="text" name="username" id="username" placeholder="کد پروموتر (نام کاربری)" required maxlength="32">
            <label for="password">رمز عبور *</label>
            <input type="password" name="password" id="password" placeholder="رمز عبور" required maxlength="32">
            <button type="submit" class="btn">ورود</button>
        </form>
        <div id="form-messages"></div>
    </div>
    <script>
    // بارگذاری انیمیشن Lottie
    lottie.loadAnimation({
        container: document.getElementById('lottie-login'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: '<?php echo LOTTIE_URL; ?>login.json'
    });

    // اگر قبلاً لاگین کرده، مستقیم به پنل منتقل شو
    if (localStorage.getItem('promoter_token')) {
        window.location.href = 'promoter_panel.php';
    }

    // مدیریت فرم ورود
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        playSound('touch');
        let username = document.getElementById('username').value.trim();
        let password = document.getElementById('password').value.trim();
        document.getElementById('form-messages').innerHTML = '';
        if (!username || !password) {
            popupError('لطفا همه فیلدها را پر کنید.');
            playSound('error');
            return;
        }
        try {
            let response = await fetch('../api/auth.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'login_promoter',
                    username: username,
                    password: password
                })
            });
            let data = await response.json();
            if (data.success) {
                playSound('success');
                popupSuccess('ورود موفقیت‌آمیز بود!', function(){
                    localStorage.setItem('promoter_token', data.token);
                    window.location.href = 'promoter_panel.php';
                });
            } else {
                playSound('error');
                popupError(data.message || 'خطا در ورود.');
            }
        } catch (err) {
            playSound('error');
            popupError('خطای ارتباط با سرور.');
        }
    });
    </script>
</body>
</html>