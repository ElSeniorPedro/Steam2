<?php
require_once("../config.php");
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بررسی کیفیت | S!team2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../vendor/lottie/lottie.min.js"></script>
    <script src="../js/sounds.js"></script>
    <script src="../js/popup.js"></script>
    <script src="../js/auth.js"></script>
</head>
<body>
    <div class="container" style="margin-top:30px;">
        <div class="header-lottie" id="lottie-qc"></div>
        <h2 style="text-align:center;margin-bottom:14px;">فرم بررسی کیفیت (QC)</h2>
        <form id="qcForm" enctype="multipart/form-data" autocomplete="off">
            <label>آیا در نزدیکی شما پروموتر شرکت رقیب وجود دارد؟ <span style="color:#e11d48">*</span></label>
            <select name="q1" required>
                <option value="">انتخاب کنید</option>
                <option value="بله">بله</option>
                <option value="خیر">خیر</option>
            </select>

            <label>آیا شرایط فعالیت شما مناسب است؟ <span style="color:#e11d48">*</span></label>
            <select name="q2" required>
                <option value="">انتخاب کنید</option>
                <option value="بله">بله</option>
                <option value="خیر">خیر</option>
            </select>

            <label>آیا از طرح روزانه و یا هفتگی خود اطلاع دارید؟ <span style="color:#e11d48">*</span></label>
            <select name="q3" required>
                <option value="">انتخاب کنید</option>
                <option value="بله">بله</option>
                <option value="خیر">خیر</option>
            </select>

            <label>آیا از سمت شرکت رقیب پیشنهادی جهت همکاری دریافت کرده‌اید؟ <span style="color:#e11d48">*</span></label>
            <select name="q4" required>
                <option value="">انتخاب کنید</option>
                <option value="خیر">خیر</option>
                <option value="بله">بله</option>
            </select>
            <input type="text" name="rival_offer" placeholder="توضیح پیشنهاد دریافتی (در صورت وجود)" maxlength="255" style="margin-bottom:14px;">

            <label>عکس سلفی (فقط دوربین) <span style="color:#e11d48">*</span><br><span style="font-size:.96rem;color:#888;">لطفا برای ما یک عکس سلفی از خودتون و محل فعالیتتون بفرستید تا به یادگار از امروز داشته باشیم.</span></label>
            <input type="file" accept="image/*;capture=camera" name="selfie" required>

            <label>ویدیو محیط (فقط دوربین) <span style="color:#e11d48">*</span><br><span style="font-size:.96rem;color:#888;">لطفا یک ویدیو از کل محیط فعالیت ضبط کنید.</span></label>
            <input type="file" accept="video/*;capture=camcorder" name="video" required>

            <button type="submit" class="btn">ثبت اطلاعات</button>
        </form>
        <div id="form-messages"></div>
    </div>
    <script>
    lottie.loadAnimation({
        container: document.getElementById('lottie-qc'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: '<?php echo LOTTIE_URL; ?>qc.json'
    });

    document.getElementById('qcForm').onsubmit = function(e) {
        e.preventDefault();
        playSound('touch');
        let form = e.target;
        let formData = new FormData(form);

        // جمع‌بندی پاسخ‌ها به صورت JSON
        let answers = {
            q1: form.q1.value,
            q2: form.q2.value,
            q3: form.q3.value,
            q4: form.q4.value,
            rival_offer: form.rival_offer.value
        };
        // چک پر بودن سوالات
        for (let k of ['q1','q2','q3','q4']) {
            if (!answers[k]) {
                playSound('error');
                popupError('همه سوالات ستاره‌دار باید پر شود.');
                return;
            }
        }

        // افزودن موقعیت جغرافیایی
        navigator.geolocation.getCurrentPosition(function(pos){
            formData.append('lat', pos.coords.latitude);
            formData.append('lng', pos.coords.longitude);
            formData.append('answers', JSON.stringify(answers));
            formData.append('username', getPromoterToken());
            fetch('../api/qc.php', {
                method: 'POST',
                body: formData
            })
            .then(res=>res.json())
            .then(data=>{
                if (data.success) {
                    playSound('success');
                    popupSuccess(data.message, function(){
                        window.location.href = 'promoter_panel.php';
                    });
                } else {
                    playSound('error');
                    popupError(data.message);
                }
            })
            .catch(()=>{
                playSound('error');
                popupError('خطا در ارسال اطلاعات.');
            });
        }, function(){
            playSound('error');
            popupError('دسترسی به موقعیت مکانی فعال نیست.');
        });
    }
    </script>
</body>
</html>