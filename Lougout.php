<?php
/*------------------------------------
    🔒 أقصى إعدادات أمان للجلسة 10/10
------------------------------------*/

// يمنع JavaScript من قراءة الكوكي
ini_set('session.cookie_httponly', 1);

// لا يسمح للكوكي بالنقل إلا عبر HTTPS (فعّله إذا لديك SSL)
ini_set('session.cookie_secure', 1);

// منع إرسال الكوكي عبر روابط خارجية (أفضل حماية ضد CSRF)
ini_set('session.cookie_samesite', 'Strict');

// يمنع PHP من قبول أي Session ID من المستخدم
ini_set('session.use_strict_mode', 1);

// منع تمرير الـ Session عبر URL
ini_set('session.use_only_cookies', 1);

// تقليل عمر الجلسة
ini_set('session.gc_maxlifetime', 3600); // 60 دقيقة

// منع تخزين الصفحة في الكاش بعد تسجيل الخروج
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// بدء الجلسة
session_start();

/*------------------------------------
    🔥 إزالة كل بيانات الجلسة بشكل آمن
------------------------------------*/

// حذف كل المتغيرات داخل الجلسة
$_SESSION = [];

// حذف كوكي الجلسة إن وجد
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Strict'
        ]
    );
}

// تدمير الجلسة نهائيًا
session_destroy();

/*------------------------------------
    🔁 إعادة التوجيه برسالة آمنة
------------------------------------*/
echo '<script>
    alert("Déconnexion réussie !");
    window.location.href = "Login.php";
</script>';
exit;
?>
