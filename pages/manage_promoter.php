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
    <title>عملیات پروموترها | S!team2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/main.css">
    <script src="../js/sounds.js"></script>
    <script src="../js/popup.js"></script>
</head>
<body>
    <div class="container" style="margin-top:30px;">
        <h2 style="text-align:center;margin-bottom:18px;">عملیات روی پروموتر</h2>
        <form id="searchForm" autocomplete="off">
            <label>کد پروموتر (نام کاربری):</label>
            <input type="text" id="search_username" maxlength="32" required>
            <button type="submit" class="btn">جستجو</button>
        </form>
        <div id="result-box"></div>
        <button class="btn" style="margin-top:22px;" onclick="location.href='admin_dashboard.php'">بازگشت</button>
    </div>
    <script>
    document.getElementById('searchForm').onsubmit = function(e){
        e.preventDefault();
        playSound('touch');
        let username = document.getElementById('search_username').value.trim();
        let box = document.getElementById('result-box');
        box.innerHTML = 'در حال دریافت اطلاعات...';
        fetch('../api/get_promoter.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({username:username})
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                let p = data.promoter;
                box.innerHTML = `
                    <div style="margin:18px 0;">
                        <b>${p.first_name} ${p.last_name}</b><br>
                        <b>کد پروموتر:</b> ${p.username}<br>
                        <b>شماره:</b> ${p.phone}<br>
                        <b>استان/شهر:</b> ${p.province}، ${p.city}<br>
                        <b>نوع:</b> ${p.type==='static'?'ثابت':'گردشی'}<br>
                        <b>وضعیت:</b> <span style="color:${p.status==='active'?'#16a34a':'#e11d48'}">${p.status==='active'?'فعال':'غیرفعال'}</span>
                    </div>
                    <button class="btn" onclick="editPromoter('${p.username}')">ویرایش اطلاعات</button>
                    <button class="btn" onclick="deactivatePromoter('${p.username}')">غیرفعال/فعال‌سازی</button>
                    <button class="btn" style="background:#e11d48" onclick="deletePromoter('${p.username}')">حذف کامل</button>
                `;
            } else {
                playSound('error');
                box.innerHTML = `<span style="color:#e11d48">${data.message}</span>`;
            }
        }).catch(()=>{
            playSound('error');
            box.innerHTML = `<span style="color:#e11d48">خطا در ارتباط با سرور.</span>`;
        });
    };
    function editPromoter(username){
        location.href = 'edit_promoter.php?username=' + encodeURIComponent(username);
    }
    function deactivatePromoter(username){
        if(confirm('آیا مطمئن هستید؟')){
            fetch('../api/deactivate_promoter.php',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({username:username})
            })
            .then(res=>res.json())
            .then(data=>{
                if(data.success){
                    playSound('success');
                    popupSuccess(data.message, ()=>location.reload());
                } else {
                    playSound('error');
                    popupError(data.message);
                }
            });
        }
    }
    function deletePromoter(username){
        if(confirm('حذف کامل و بدون بازگشت. ادامه؟')){
            fetch('../api/delete_promoter.php',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({username:username})
            })
            .then(res=>res.json())
            .then(data=>{
                if(data.success){
                    playSound('success');
                    popupSuccess(data.message, ()=>location.reload());
                } else {
                    playSound('error');
                    popupError(data.message);
                }
            });
        }
    }
    </script>
</body>
</html>