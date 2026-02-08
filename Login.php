<?php
/*----------------------------------------------------
    🔒 Configuration maximale de sécurité des sessions
----------------------------------------------------*/

// استيراد إعدادات قاعدة البيانات من ملف منفصل
require_once 'db/config.php';

// Ajoutez cette partie pour créer la connexion PDO
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false
        ]
    );
} catch (PDOException $e) {
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}

// Configuration de session avant démarrage
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_lifetime', '0');
ini_set('session.cache_limiter', 'nocache');
ini_set('session.gc_maxlifetime', '3600');

// Active le flag Secure uniquement si HTTPS est utilisé
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}

/*----------------------------------------------------
    ▶ Démarrage de la session
----------------------------------------------------*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Régénération périodique de l'ID de session
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 1800) {
        // Régénérer toutes les 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/*----------------------------------------------------
    🔐 Génération du token CSRF si absent
----------------------------------------------------*/
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*----------------------------------------------------
    🔐 Headers de sécurité HTTP
----------------------------------------------------*/
// Désactiver la mise en cache pour les pages sensibles
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Headers de sécurité
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self';");

// HSTS uniquement en HTTPS
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

/*----------------------------------------------------
    ⚠ Limitation des tentatives de connexion
----------------------------------------------------*/
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_TIME', 2700); // 45 minutes en secondes

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

/*----------------------------------------------------
    ▶ Si formulaire soumis
----------------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        echo '<script>alert("Nom d\'utilisateur ou mot de passe incorrect.");</script>';
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_attempt_time'] = time();
    } else {
        // Vérification du délai de verrouillage
        $elapsed = time() - ($_SESSION['last_attempt_time'] ?? 0);
        
        if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS && $elapsed < LOCKOUT_TIME) {
            $remaining = LOCKOUT_TIME - $elapsed;
            echo "<script>alert('Trop de tentatives échouées. Réessayez dans $remaining secondes.');</script>";
        } else {
            // Réinitialiser les tentatives si le délai de verrouillage est expiré
            if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS && $elapsed >= LOCKOUT_TIME) {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_attempt_time'] = 0;
            }
            
            // Validation et nettoyage des entrées
            $input_username = trim($_POST['username'] ?? '');
            $input_password = $_POST['mot_de_passe'] ?? '';
            
            // Validation des entrées
            if (empty($input_username) || empty($input_password)) {
                echo '<script>alert("Veuillez remplir tous les champs.");</script>';
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                $_SESSION['last_attempt_time'] = time();
            } elseif (strlen($input_username) > 50 || strlen($input_password) > 255) {
                echo '<script>alert("Nom d\'utilisateur ou mot de passe incorrect.");</script>';
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                $_SESSION['last_attempt_time'] = time();
            } else {
                // Préparation de la requête avec des requêtes préparées
                try {
                    $stmt = $conn->prepare("SELECT id, username, password, role, is_active, name FROM admin_users WHERE username = :username LIMIT 1");
                    $stmt->bindParam(':username', $input_username, PDO::PARAM_STR);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Délai artificiel constant pour éviter les attaques par timing
                    usleep(500000); // 500ms constant
                    
                    if ($user) {
                        // Vérification du mot de passe
                        $is_valid = password_verify($input_password, $user['password']);
                        $is_active = ($user['is_active'] == 1);
                        
                        if ($is_valid && $is_active) {
                            // Connexion réussie
                            session_regenerate_id(true);
                            
                            // Réinitialiser le token CSRF pour la nouvelle session
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                            
                            // Stockage des informations de session
                            $_SESSION['admin_id'] = $user['id'];
                            $_SESSION['admin_username'] = $user['username'];
                            $_SESSION['admin_name'] = $user['name'];
                            $_SESSION['admin_role'] = $user['role'];
                            $_SESSION['is_admin_logged_in'] = true;
                            $_SESSION['login_attempts'] = 0;
                            $_SESSION['last_attempt_time'] = 0;
                            $_SESSION['last_login'] = time();
                            $_SESSION['created'] = time(); // Réinitialiser le timestamp de création
                            
                            // Mise à jour de la dernière connexion
                            try {
                                $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = :id");
                                $update_stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
                                $update_stmt->execute();
                            } catch (Exception $e) {
                                error_log("Erreur lors de la mise à jour du dernier login: " . $e->getMessage());
                                // Ne pas bloquer la connexion si cette mise à jour échoue
                            }
                            
                            // Message de succès et redirection
                            echo '<script>alert("Connexion réussie !"); window.location.href = "espace_admin.php";</script>';
                            exit();
                            
                        } else {
                            // Incrémentation des tentatives échouées
                            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                            $_SESSION['last_attempt_time'] = time();
                            
                            // Message d'erreur selon الحالة
                            if ($user && !$is_active) {
                                echo '<script>alert("Votre compte administrateur est désactivé. Contactez le super administrateur.");</script>';
                            } else {
                                echo '<script>alert("Nom d\'utilisateur ou mot de passe incorrect.");</script>';
                            }
                        }
                    } else {
                        // Utilisateur non trouvé - même temps de réponse
                        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                        $_SESSION['last_attempt_time'] = time();
                        echo '<script>alert("Nom d\'utilisateur ou mot de passe incorrect.");</script>';
                    }
                    
                } catch (Exception $e) {
                    error_log("Erreur lors de l'authentification: " . $e->getMessage());
                    echo '<script>alert("Nom d\'utilisateur ou mot de passe incorrect.");</script>';
                    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                    $_SESSION['last_attempt_time'] = time();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول | لوحة التحكم الإدارية</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <!--icon-->
    <link rel="icon" href="img/login.ico" type="image/x-icon">
    <!-- CSS -->
     <link href="CSS/Login.css" rel="stylesheet">

</head>
<body>
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h1>تسجيل الدخول</h1>
            <p>لوحة التحكم الإدارية</p>
        </div>
        
        <div class="login-body">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" autocomplete="off" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">اسم المستخدم</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="أدخل اسم المستخدم" required autofocus 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    <div class="invalid-feedback" style="display: none; text-align: right;">
                        الرجاء إدخال اسم المستخدم
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <div class="password-input">
                        <input type="password" class="form-control" id="password" name="mot_de_passe" 
                               placeholder="أدخل كلمة المرور" required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback" style="display: none; text-align: right;">
                        الرجاء إدخال كلمة المرور
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember" style="cursor: pointer;">تذكرني</label>
                    </div>
                </div>
                
                <button type="submit" name="login" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                </button>
            </form>
            
            <?php if (($_SESSION['login_attempts'] ?? 0) > 0): ?>
                <?php 
                $elapsed = time() - ($_SESSION['last_attempt_time'] ?? 0);
                $remaining_attempts = MAX_LOGIN_ATTEMPTS - $_SESSION['login_attempts'];
                ?>
                <div class="attempts-info <?php echo ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS && $elapsed < LOCKOUT_TIME) ? 'lockout-danger' : 'attempts-warning'; ?>">
                    <?php if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS && $elapsed < LOCKOUT_TIME): ?>
                        <i class="fas fa-lock"></i> 
                        تم تجاوز الحد الأقصى للمحاولات. الرجاء الانتظار <?php echo gmdate("i:s", LOCKOUT_TIME - $elapsed); ?> دقيقة
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle"></i> 
                        المحاولات الفاشلة: <?php echo $_SESSION['login_attempts']; ?> / <?php echo MAX_LOGIN_ATTEMPTS; ?>
                        <?php if ($remaining_attempts > 0): ?>
                            <br><small>المحاولات المتبقية: <?php echo $remaining_attempts; ?></small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="login-footer">
                <p>جميع الحقوق محفوظة © <?php echo date('Y'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Login JS -->
<script src="JS/Login.js"></script>

</body>
</html>