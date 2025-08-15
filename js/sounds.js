// مدیریت صداها برای تاچ، موفقیت، خطا
const sounds = {
    touch: new Audio('../assets/sounds/touch.mp3'),
    success: new Audio('../assets/sounds/success.mp3'),
    error: new Audio('../assets/sounds/error.mp3')
};

function playSound(type) {
    if (sounds[type]) {
        sounds[type].currentTime = 0; // برای پخش مجدد سریع
        sounds[type].play();
    }
}