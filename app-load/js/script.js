const cookieCheck = (cookieName = 'app-load-time', daysUntilExpire = 365) => {
    const cookieElement = document.querySelector('.app-load');
    const acceptBtn = document.querySelector('.setCookieBtn');
    let showCookieMessage = true;

    const cookies = document.cookie.split(';').map((cookie) => {
        if(cookie.replace(/ /g, '') === `${cookieName}=true`) {
            showCookieMessage = false;
        };
    });

    if(showCookieMessage) {
        cookieElement.classList.add('is-visible');
        acceptBtn.addEventListener('click', e => {
            e.preventDefault();
            setCookie();
            cookieElement.classList.remove('is-visible');
        });
    }

    function setCookie() {
        let expireInDays = new Date();
        expireInDays.setDate(expireInDays.getDate() + daysUntilExpire);
        document.cookie = `${cookieName}=true; expires=${expireInDays} path=/`;
    }
};
// Set cookie with name and days until expire
//cookieCheck('app-load-time', 0);


document.getElementById('app-load-close').onclick = function() {
    document.getElementById('app-load').style.display = ('none');
}

function showAppLoad() {
    var link = document.getElementById("add-load-link");
    var appPopup = document.getElementById("app-load");

    if (device.android() || device.androidPhone() || device.androidTablet()) {
        link.setAttribute("href", "https://play.google.com/store/apps/details?id=com.axibort.pro&hl=ru");
        appPopup.style.display = ('block');
    }

    if (device.iphone() || device.ipod() || device.ipad()) {
        link.setAttribute("href", "https://apps.apple.com/ru/app/axibort-%D1%81%D0%B5%D1%80%D0%B2%D0%B8%D1%81-%D0%B7%D0%B0%D0%BA%D0%B0%D0%B7%D0%B0-%D1%82%D0%B0%D0%BA%D1%81%D0%B8/id1508327702");
        appPopup.style.display = ('block');
    }
}
setTimeout(showAppLoad, 5000);