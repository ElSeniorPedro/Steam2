<?php
require_once("../config.php");
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>درخواست پشتیبانی فوری | S!team2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../vendor/lottie/lottie.min.js"></script>
    <script src="../js/sounds.js"></script>
    <script src="../js/popup.js"></script>
    <script src="../js/auth.js"></script>
</head>
<body>
    <div class="container" style="margin-top:30px;">
        <div class="header-lottie" id="lottie-support"></div>
        <h2 style="text-align:center;margin-bottom:18px;">درخواست پشتیبانی فوری</h2>
        <form id="supportForm" autocomplete="off">
            <label>کد اسپات (در صورت وجود)</label>
            <input type="text" name="spot_code" maxlength="32" placeholder="کد اسپات">

            <button type="submit" class="btn">ارسال درخواست</button>
        </form>
        <div id="form-messages"></div>
    </div>
    <script>
    lottie.loadAnimation({
        container: document.getElementById('lottie-support'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: '<?php echo LOTTIE_URL; ?>support.json'
    });

    document.getElementById('supportForm').onsubmit = function(e) {
        e.preventDefault();
        playSound('touch');
        let form = e.target;
        let formData = new FormData(form);
        formData.append('username', getPromoterToken());
        fetch('../api/support.php', {
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
            popupError('خطا در ارسال درخواست.');
        });
    }
    </script>
</body>
</html>