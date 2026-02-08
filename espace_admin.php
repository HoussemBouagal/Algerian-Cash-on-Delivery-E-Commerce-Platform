<?php
/*----------------------------------------------------
    🔒 التحقق من تسجيل الدخول
----------------------------------------------------*/

// إعدادات الأمان للجلسة - يجب وضعها قبل session_start()
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// فحص ما إذا كان المستخدم مسجلاً دخولاً
$isLoggedIn = isset($_SESSION['is_admin_logged_in']) && $_SESSION['is_admin_logged_in'] === true;
$isValidUser = isset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_role']);

// إذا لم يكن مسجلاً دخولاً، توجيهه إلى صفحة تسجيل الدخول
if (!$isLoggedIn || !$isValidUser) {
    // تسجيل محاولة الوصول غير المصرح به
    error_log("[" . date('Y-m-d H:i:s') . "] محاولة وصول غير مصرح بها إلى صفحة الإدارة من IP: " . $_SERVER['REMOTE_ADDR']);
    
    // إنهاء الجلسة الحالية
    session_unset();
    session_destroy();
    
    // التوجيه إلى صفحة تسجيل الدخول
    header("Location: Login.php");
    exit();
}

// التحقق من وقت آخر نشاط (تسجيل خروج تلقائي بعد 30 دقيقة من عدم النشاط)
$sessionTimeout = 1800; // 30 دقيقة بالثواني
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionTimeout)) {
    // تسجيل الخروج التلقائي
    error_log("[" . date('Y-m-d H:i:s') . "] خروج تلقائي للمستخدم: " . $_SESSION['admin_username'] . " بسبب عدم النشاط");
    
    // إنهاء الجلسة
    session_unset();
    session_destroy();
    
    // التوجيه إلى صفحة تسجيل الدخول
    header("Location: Login.php?timeout=1");
    exit();
}

// تحديث وقت آخر نشاط
$_SESSION['last_activity'] = time();

// Headers الأمان
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// إضافة CSRF token للجلسة إذا لم يكن موجوداً
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// تجديد معرف الجلسة دورياً (كل 10 دقائق)
if (!isset($_SESSION['session_regenerated'])) {
    $_SESSION['session_regenerated'] = time();
} elseif (time() - $_SESSION['session_regenerated'] > 600) {
    session_regenerate_id(true);
    $_SESSION['session_regenerated'] = time();
}

// معلومات المستخدم للاستخدام في الصفحة
$adminName = htmlspecialchars($_SESSION['admin_name'] ?? 'مدير النظام', ENT_QUOTES, 'UTF-8');
$adminRole = htmlspecialchars($_SESSION['admin_role'] ?? 'مدير', ENT_QUOTES, 'UTF-8');
$adminUsername = htmlspecialchars($_SESSION['admin_username'] ?? 'admin', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات | متجرنا الإلكتروني</title>
    <meta name="description" content="لوحة إدارة المنتجات للمتجر الإلكتروني">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr for Date/Time -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
     <!-- CSS -->
    <link rel="stylesheet" href="CSS/admin.css">
     <!-- Icons -->
    <link rel="icon" href="img/gestion.ico" type="image/x-icon">
    
</head>

<body>
    <!-- ========== STRUCTURE: الشريط الجانبي ========== -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-store me-2"></i>إدارة المتجر</h3>
            <p class="text-muted mt-2 small">إدارة متجرنا الإلكتروني</p>
        </div>
        
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#" data-page="products">
                        <i class="fas fa-box"></i>
                        <span>المنتجات</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-page="delivery">
                        <i class="fas fa-truck"></i>
                        <span>أسعار التوصيل</span>
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link" href="#" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>تسجيل الخروج</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="sidebar-footer p-3 text-center">
            <p class="small text-muted mb-0">الإصدار 1.0.0</p>
        </div>
    </div>
    
    <!-- ========== STRUCTURE: المحتوى الرئيسي ========== -->
    <div class="main-content" id="mainContent">
        <!-- الهيدر العلوي -->
        <div class="topbar">
            <button class="toggle-sidebar" id="toggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            <h4 class="mb-0" id="pageTitle">إدارة المنتجات</h4>
            
            <div class="user-info">
                <div class="user-avatar">أ</div>
                <div>
                    <div class="fw-bold">الإدارة</div>
                    <div class="small text-muted">مدير النظام</div>
                </div>
            </div>
        </div>
        
        <!-- ========== STRUCTURE: صفحات المحتوى ========== -->
        <!-- صفحة المنتجات -->
        <div class="content-page active" id="productsPage">
            <div class="table-container">
                <div class="table-header">
                    <h5 class="table-title">إدارة المنتجات</h5>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control-admin table-search" id="searchProduct" placeholder="بحث عن منتج...">
                        <button class="btn-admin btn-admin-primary" id="addProductBtn">
                            <i class="fas fa-plus me-1"></i>إضافة منتج جديد
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>الصورة</th>
                                <th>اسم المنتج</th>
                                <th>الفئة</th>
                                <th>السعر (دج)</th>
                                <th>المخزون</th>
                                <th>التخفيض</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="productsTable">
                            <!-- سيتم تعبئة هذا الجدول بالبيانات -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- صفحة إدارة أسعار التوصيل -->
        <div class="content-page" id="deliveryPage">
            <div class="table-container">
                <div class="table-header">
                    <h5 class="table-title">إدارة أسعار التوصيل للولايات</h5>
                    <div class="d-flex gap-2">
                        <button class="btn-admin btn-admin-success" id="addWilayaBtn">
                            <i class="fas fa-plus me-1"></i>إضافة ولاية جديدة
                        </button>
                        <input type="text" class="form-control-admin table-search" id="searchWilaya" placeholder="بحث عن ولاية...">
                        <button class="btn-admin btn-admin-primary" id="saveDeliveryPricesBtn">
                            <i class="fas fa-save me-1"></i>حفظ جميع التغييرات
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th width="80">رقم الولاية</th>
                                <th>اسم الولاية</th>
                                <th width="200">سعر التوصيل (دج)</th>
                                <th width="150">الحالة</th>
                                <th width="120">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="deliveryPricesTable">
                            <!-- سيتم تعبئة هذا الجدول بالبيانات -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="price-summary">
                <h5>ملخص أسعار التوصيل</h5>
                <div class="price-summary-item">
                    <span>عدد الولايات المدعومة:</span>
                    <span id="supportedCount">0</span>
                </div>
                <div class="price-summary-item">
                    <span>متوسط سعر التوصيل:</span>
                    <span id="averagePrice">0 دج</span>
                </div>
                <div class="price-summary-item">
                    <span>أعلى سعر توصيل:</span>
                    <span id="maxPrice">0 دج</span>
                </div>
                <div class="price-summary-item">
                    <span>أقل سعر توصيل:</span>
                    <span id="minPrice">0 دج</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ========== STRUCTURE: النوافذ المنبثقة ========== -->
    <!-- نافذة إضافة/تعديل منتج -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalTitle">إضافة منتج جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="productForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">اسم المنتج</label>
                                    <input type="text" class="form-control-admin" id="productName" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">الفئة</label>
                                    <select class="form-control-admin" id="productCategory">
                                        <option value="الكترونيات">الكترونيات</option>
                                        <option value="اكسسوارات">اكسسوارات</option>
                                        <option value="منزلية">منزلية</option>
                                        <option value="أخرى">أخرى</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">السعر الأساسي (دج)</label>
                                    <input type="number" class="form-control-admin" id="productPrice" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">الكمية في المخزون</label>
                                    <input type="number" class="form-control-admin" id="productStock" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- قسم التخفيض -->
                        <div class="form-group">
                            <div class="toggle-discount mb-3">
                                <label class="form-label mb-0">تفعيل التخفيض</label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="hasDiscount">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div id="discountFields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">نسبة التخفيض (%)</label>
                                            <input type="number" class="form-control-admin" id="discountPercentage" min="0" max="100" step="1">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">سعر التخفيض (دج)</label>
                                            <input type="number" class="form-control-admin" id="discountPrice" readonly>
                                            <div class="time-remaining" id="discountInfo" style="display: none;"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">تاريخ بداية التخفيض</label>
                                            <input type="datetime-local" class="form-control-admin discount-date-input" id="discountStartDate">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">تاريخ نهاية التخفيض</label>
                                            <input type="datetime-local" class="form-control-admin discount-date-input" id="discountEndDate">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="alert alert-info p-2">
                                            <small>
                                                <i class="fas fa-info-circle me-1"></i>
                                                سيتم حساب المدة المتبقية تلقائياً بناءً على التاريخين المحددين
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">وصف المنتج</label>
                            <textarea class="form-control-admin" id="productDescription" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">صورة المنتج</label>
                            <div class="file-upload-area">
                                <div class="upload-preview" id="imagePreview" style="margin-top: 10px; display: none;">
                                    <img id="previewImage" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;">
                                </div>
                                <div class="upload-actions mt-2">
                                    <button type="button" class="btn-admin btn-admin-primary btn-sm" id="chooseImageBtn">
                                        <i class="fas fa-upload me-1"></i>اختر صورة
                                    </button>
                                    <button type="button" class="btn-admin btn-admin-danger btn-sm ms-2" id="removeImageBtn" style="display: none;">
                                        <i class="fas fa-trash me-1"></i>حذف الصورة
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="productImageUrl" value="">
                        </div>
                        
                        <input type="hidden" id="productId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin btn-admin-danger" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn-admin btn-admin-primary" id="saveProductBtn">حفظ المنتج</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- نافذة تعديل سعر توصيل ولاية -->
    <div class="modal fade" id="editWilayaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل سعر التوصيل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editWilayaForm">
                        <div class="form-group mb-3">
                            <label class="form-label">الولاية</label>
                            <input type="text" class="form-control-admin" id="editWilayaName" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">سعر التوصيل (دج)</label>
                            <input type="number" class="form-control-admin" id="editWilayaPrice" min="0" required>
                        </div>
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editWilayaActive">
                                <label class="form-check-label" for="editWilayaActive">
                                    التوصيل متاح لهذه الولاية
                                </label>
                            </div>
                        </div>
                        <input type="hidden" id="editWilayaId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin btn-admin-danger" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn-admin btn-admin-primary" id="saveWilayaPriceBtn">حفظ التغييرات</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- نافذة إضافة ولاية جديدة -->
    <div class="modal fade" id="addWilayaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة ولاية جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addWilayaForm">
                        <div class="form-group mb-3">
                            <label class="form-label">رقم الولاية</label>
                            <input type="text" class="form-control-admin" id="newWilayaCode" required maxlength="10">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">اسم الولاية</label>
                            <input type="text" class="form-control-admin" id="newWilayaName" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">سعر التوصيل (دج)</label>
                            <input type="number" class="form-control-admin" id="newWilayaPrice" min="0" required>
                        </div>
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="newWilayaActive" checked>
                                <label class="form-check-label" for="newWilayaActive">
                                    تفعيل الولاية
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin btn-admin-danger" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn-admin btn-admin-primary" id="saveNewWilayaBtn">إضافة الولاية</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مؤشر التحميل -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p id="loadingText">جاري حفظ التغييرات...</p>
        </div>
    </div>
    
    <!-- ========== SCRIPTS ========== -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/ar.js"></script>
    
<script src="JS/admin.js"></script>
</body>
</html>