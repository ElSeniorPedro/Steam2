// توابع کمکی مدیریت لاگین و توکن (در صورت نیاز توسعه)
function getPromoterToken() {
    return localStorage.getItem('promoter_token');
}
function setPromoterToken(token) {
    localStorage.setItem('promoter_token', token);
}
function logoutPromoter() {
    localStorage.removeItem('promoter_token');
    window.location.href = 'login_promoter.php';
}