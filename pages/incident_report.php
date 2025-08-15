<?php
require_once("../config.php");
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارش حادثه / برخورد | S!team2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../vendor/lottie/lottie.min.js"></script>
    <script src="../js/sounds.js"></script>
    <script src="../js/popup.js"></script>
    <script src="../js/auth.js"></script>
</head>
<body>
    <div class="container" style="margin-top:30px;">
        <div class="header-lottie" id="lottie-incident"></div>
        <h2 style="text-align:center;margin-bottom:14px;">گزارش حادثه / برخورد</h2>
        <form id="incidentForm" enctype="multipart/form-data" autocomplete="off">
            <label>ارگان برخوردکننده <span style="color:#e11d48">*</span></label>
            <input type="text" name="organization" required maxlength="128" placeholder="نام ارگان یا نهاد">

            <label>توضیحات <span style="color:#e11d48">*</span></label>
            <textarea name="description" required maxlength="600" placeholder="شرح حادثه یا توضیحات" style="width:80%;height:70px;"></textarea>

            <label>عکس محل حادثه (دوربین یا گالری) <span style="font-size:.93rem;color:#888;">عکس از سانحه</span></label>
            <input type="file" accept="image/*" name="photo">

            <label>ویدیو محل حادثه (دوربین یا گالری) <span style="font-size:.93rem;color:#888;">ویدیو از محل حادثه</span></label>
            <input type="file" accept="video/*" name="video">

            <label>فایل صوتی (ضبط یا گالری) <span style="font-size:.93rem;color:#888;">ضبط صوتی ماجرا</span></label>
            <input type="file" accept="audio/*" name="audio">

            <button type="submit" class="btn">ثبت گزارش</button>
        </form>
        <div id="form-messages"></div>
    </div>
    <script>
    lottie.loadAnimation({
        container: document.getElementById('lottie-incident'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: '<?php echo LOTTIE_URL; ?>incident.json'
    });

    document.getElementById('incidentForm').onsubmit = function(e) {
        e.preventDefault();
        playSound('touch');
        let form = e.target;
        let formData = new FormData(form);

        navigator.geolocation.getCurrentPosition(function(pos){
            formData.append('lat', pos.coords.latitude);
            formData.append('lng', pos.coords.longitude);
            formData.append('username', getPromoterToken());
            fetch('../api/incidents.php', {
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
                popupError('خطا در ارسال گزارش.');
            });
        }, function(){
            playSound('error');
            popupError('دسترسی به موقعیت مکانی فعال نیست.');
        });
    }
    </script>
</body>
</html>