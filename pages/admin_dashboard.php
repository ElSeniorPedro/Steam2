<?php
require_once("../config.php");
session_start();
if (!isset($_SESSION['admin_login'])) {
    header("Location: login_admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>داشبورد ادمین | S!team2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../vendor/lottie/lottie.min.js"></script>
    <script src="../js/sounds.js"></script>
    <script src="../js/popup.js"></script>
</head>
<body>
    <div class="container" style="margin-top:40px;">
        <div class="header-lottie" id="lottie-admin"></div>
        <h2 style="text-align:center;margin-bottom:22px;">داشبورد ادمین</h2>
        <button class="btn" onclick="location.href='add_promoter.php'">افزودن پروموتر جدید</button>
        <button class="btn" onclick="location.href='manage_promoter.php'">عملیات پروموترها</button>
        <button class="btn" onclick="location.href='add_spot.php'">افزودن اسپات جدید</button>
        <button class="logout-btn btn" style="margin-top:24px;" onclick="location.href='login_admin.php?logout=1'">خروج</button>
    </div>
    <script>
    lottie.loadAnimation({
        container: document.getElementById('lottie-admin'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: '<?php echo LOTTIE_URL; ?>admin.json'
    });
    </script>
</body>
</html>