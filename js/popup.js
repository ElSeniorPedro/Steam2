// نمایش پاپ‌آپ پیام موفقیت و خطا
function popupSuccess(msg, cb) {
    showPopup(msg, 'success', cb);
}
function popupError(msg, cb) {
    showPopup(msg, 'error', cb);
}
function showPopup(msg, type, cb) {
    let popup = document.createElement('div');
    popup.style.position = 'fixed';
    popup.style.top = '50%';
    popup.style.left = '50%';
    popup.style.transform = 'translate(-50%, -50%)';
    popup.style.background = '#fff';
    popup.style.boxShadow = '0 8px 40px #0004';
    popup.style.zIndex = 9999;
    popup.style.borderRadius = '18px';
    popup.style.padding = '28px 34px';
    popup.style.fontSize = '1.2rem';
    popup.style.textAlign = 'center';
    popup.style.color = (type === 'success' ? '#16a34a' : '#e11d48');
    popup.innerHTML = msg + '<br><button style="margin-top:20px; background:'+(type === 'success' ? '#16a34a' : '#e11d48')+';color:#fff;padding:8px 28px;border-radius:8px;border:none;font-size:1.1rem;cursor:pointer;">باشه</button>';
    document.body.appendChild(popup);
    popup.querySelector('button').focus();
    popup.querySelector('button').onclick = () => {
        popup.remove();
        if (cb) cb();
    }
}