/**
 * Синхронизация cookie catalog_ps с шириной окна:
 * ≥1200px → 15, 768–1199 → 12, <768 → 14.
 * Cookie без Yii-подписи; сервер читает из $_COOKIE.
 */
(function () {
    function desired() {
        var w = window.innerWidth || document.documentElement.clientWidth || 0;
        if (w >= 1200) {
            return 15;
        }
        if (w >= 768) {
            return 12;
        }
        return 14;
    }

    function getCookie() {
        var m = document.cookie.match(/(?:^|; )catalog_ps=(\d+)(?:;|$)/);
        return m ? parseInt(m[1], 10) : null;
    }

    function setCookie(v) {
        document.cookie = 'catalog_ps=' + v + ';path=/;max-age=' + (86400 * 365) + ';SameSite=Lax';
    }

    function sync() {
        var need = desired();
        var cur = getCookie();
        if (cur === null) {
            setCookie(need);
            if (need !== 15) {
                location.reload();
            }
            return need;
        }
        if (cur !== need) {
            setCookie(need);
            location.reload();
            return need;
        }
        return need;
    }

    var last = sync();

    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            var need = desired();
            if (need !== last) {
                last = need;
                setCookie(need);
                location.reload();
            }
        }, 400);
    });
})();
