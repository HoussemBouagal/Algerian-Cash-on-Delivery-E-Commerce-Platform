<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="pageTitle">متجرنا الإلكتروني | الدفع عند الاستلام | تسوق آمن في الجزائر</title>
    <meta name="description" id="pageDescription" content="متجرنا الإلكتروني - تسوق آمن مع الدفع عند الاستلام، توصيل سريع لجميع ولايات الجزائر. منتجات أصلية بأسعار منافسة.">
    <link rel="icon" href="img/boutique.ico" type="image/x-icon">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <!-- CSS -->
     <link rel="stylesheet" href="CSS/index.css">

</head>

<body>
    <!-- شريط التنقل -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3" href="#">
                <i class="fas fa-store me-2"></i><span id="storeNameNav">متجرنا الإلكتروني</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home me-1"></i> الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products"><i class="fas fa-box me-1"></i> المنتجات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#order"><i class="fas fa-shopping-cart me-1"></i> الطلب</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact"><i class="fas fa-phone-alt me-1"></i> اتصل بنا</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- الهيدر -->
    <header>
        <div class="container">
            <div class="header-content">
                <h1 class="mb-3"><i class="fas fa-shopping-bag me-2"></i><span id="storeNameHeader">متجرنا الإلكتروني</span></h1>
                <p class="lead fs-4 mb-4" id="storeDescription">الدفع عند الاستلام | توصيل سريع وآمن لجميع ولايات الجزائر</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <span class="badge bg-light text-dark p-2 fs-6"><i class="fas fa-shield-alt me-1"></i> ضمان الجودة</span>
                    <span class="badge bg-light text-dark p-2 fs-6"><i class="fas fa-truck me-1"></i> توصيل 24-48 ساعة</span>
                    <span class="badge bg-light text-dark p-2 fs-6"><i class="fas fa-hand-holding-usd me-1"></i> الدفع عند الاستلام</span>
                    <span class="badge bg-light text-dark p-2 fs-6"><i class="fas fa-headset me-1"></i> دعم فني 24/7</span>
                </div>
            </div>
        </div>
    </header>

    <!-- قسم العروض الخاصة -->
    <div class="container mt-4 mb-4">
        <div class="alert alert-danger border-0 shadow-sm" style="background: linear-gradient(135deg, #dc2626, #ef4444); color: white;">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h4 class="mb-1"><i class="fas fa-bolt me-2"></i> عروض خاصة محدودة الوقت!</h4>
                    <p class="mb-0">استفد من تخفيضاتنا المميزة التي تنتهي قريباً. لا تفوت الفرصة!</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-2 mt-lg-0">
                    <div id="globalDiscountTimer" class="discount-timer active" style="display: inline-flex;">
                        <i class="fas fa-clock"></i>
                        <span id="discountDays">0</span> يوم 
                        <span id="discountHours">00</span>:<span id="discountMinutes">00</span>:<span id="discountSeconds">00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- رابط الواتساب -->
    <a href="#" class="whatsapp-contact" id="whatsappLink" target="_blank" aria-label="اتصل بنا على واتساب">
        <i class="fab fa-whatsapp fs-5"></i>
        <span id="whatsappNumber" class="french-phone">+213 671 84 66 13</span>
    </a>

    <!-- عداد السلة -->
    <div class="cart-count" id="cartCount" aria-live="polite">0</div>

    <!-- المحتوى الرئيسي -->
    <main class="container py-5">
        <!-- قسم المنتجات -->
        <section id="products" class="mb-5">
            <h2><i class="fas fa-boxes me-2"></i> منتجاتنا</h2>
            <p class="text-muted mb-4">اختر من بين منتجاتنا المتميزة بأسعار تنافسية وجودة عالية</p>
            
            <!-- تصفية حسب الفئة -->
            <div class="category-filter">
                <div class="category-buttons" id="categoryButtons">
                    <!-- سيتم تحميل الفئات من قاعدة البيانات هنا -->
                </div>
            </div>
            
            <div class="row g-4" id="productsContainer">
                <!-- سيتم تحميل المنتجات من قاعدة البيانات هنا -->
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                    <p class="mt-3 text-muted">جاري تحميل المنتجات...</p>
                </div>
            </div>
        </section>
        
        <!-- نموذج الطلب -->
        <section id="order">
            <h2><i class="fas fa-shopping-cart me-2"></i> إتمام الطلب</h2>
            <p class="text-muted mb-4">املأ معلوماتك وسنقوم بتوصيل طلبك خلال 24-48 ساعة</p>
            
            <form class="order-form" id="orderForm" onsubmit="sendOrder(event)">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">الاسم الكامل</label>
                        <input type="text" class="form-control" id="name" placeholder="أدخل اسمك الكامل" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">رقم الهاتف</label>
                        <input type="tel" class="form-control french-phone" id="phone" placeholder="مثال: 0670123456" pattern="[0-9]{10}" required>
                        <div class="form-text">يرجى إدخال 10 أرقام</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="wilaya" class="form-label">الولاية</label>
                        <select class="form-select" id="wilaya" required onchange="calcDelivery()">
                            <option value="" selected disabled>اختر ولايتك</option>
                            <!-- سيتم ملؤها تلقائياً من قاعدة البيانات -->
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="place" class="form-label">مكان التوصيل</label>
                        <select class="form-select" id="place" required>
                            <option value="" selected disabled>اختر مكان التوصيل</option>
                            <option value="المنزل">المنزل</option>
                            <option value="المكتب">المكتب</option>
                        </select>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="address" class="form-label">العنوان الكامل</label>
                        <textarea class="form-control" id="address" rows="3" placeholder="الحي، الشارع، رقم المنزل، أي معلومات إضافية تساعد في الوصول إليك" required></textarea>
                    </div>
                </div>
                
                <!-- ملخص الطلب -->
                <div class="order-summary">
                    <h4 class="mb-3"><i class="fas fa-receipt me-2"></i> ملخص الطلب</h4>
                    <div class="summary-item">
                        <span>سعر المنتجات:</span>
                        <span id="productsTotal">0 دج</span>
                    </div>
                    <div class="summary-item">
                        <span>سعر التوصيل:</span>
                        <span id="delivery">0 دج</span>
                    </div>
                    <div class="summary-total">
                        <span>المجموع الكلي:</span>
                        <span id="grandTotal">0 دج</span>
                    </div>
                </div>
                
                <!-- معلومات مهمة -->
                <div class="important-note" id="policySection">
                    <h5 class="d-flex align-items-center"><i class="fas fa-info-circle me-2"></i> معلومات مهمة</h5>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <p class="mb-1"><i class="fas fa-money-bill-wave me-2"></i> <strong>طريقة الدفع:</strong> الدفع عند الاستلام فقط</p>
                            <p class="mb-1"><i class="fas fa-truck me-2"></i> <strong>مدة التوصيل:</strong> 24-48 ساعة</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><i class="fas fa-phone me-2"></i> <strong>للاستفسار:</strong> <span id="contactPhone" class="french-phone">+213 671 84 66 13</span></p>
                            <p class="mb-0"><i class="fas fa-envelope me-2"></i> <strong>البريد الإلكتروني:</strong> <span id="storeEmail">piyouma24@gmail.com</span></p>
                        </div>
                    </div>
                    <div id="deliveryPolicy" class="mt-3 small">
                        <strong>سياسة التوصيل:</strong> التوصيل يتم في فترة ما بين 24 ساعة إلى 48 ساعة. فترة الإرجاع المسموحة خلال يومين فقط
                    </div>
                </div>
                
                <!-- زر الإرسال -->
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg py-3">
                        <i class="fab fa-whatsapp me-2"></i> إرسال الطلب عبر واتساب
                    </button>
                </div>
            </form>
        </section>
        
        <!-- قسم اتصل بنا -->
        <section id="contact" class="mt-5 pt-4">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2><i class="fas fa-headset me-2"></i> اتصل بنا</h2>
                    <p class="text-muted mb-4">نحن هنا لخدمتك على مدار الساعة. لا تتردد في الاتصال بنا لأي استفسار.</p>
                    
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="p-4 border rounded bg-white">
                                <i class="fas fa-phone-alt fa-2x text-primary mb-3"></i>
                                <h5>الهاتف</h5>
                                <p class="mb-0 french-phone" id="contactPhone2">+213 671 84 66 13</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="p-4 border rounded bg-white">
                                <i class="fab fa-whatsapp fa-2x text-success mb-3"></i>
                                <h5>واتساب</h5>
                                <p class="mb-0 french-phone" id="whatsappNumber2">+213 671 84 66 13</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="p-4 border rounded bg-white">
                                <i class="fas fa-envelope fa-2x text-danger mb-3"></i>
                                <h5>البريد الإلكتروني</h5>
                                <p class="mb-0" id="storeEmail2">piyouma24@gmail.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- الفوتر -->
    <footer id="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4 class="mb-3"><i class="fas fa-store me-2"></i><span id="storeNameFooter">متجرنا الإلكتروني</span></h4>
                    <p id="footerDescription">نقدم لكم تجربة تسوق آمنة وسهلة مع ضمان الجودة والدفع عند الاستلام.</p>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">روابط سريعة</h5>
                    <div class="footer-links">
                        <p><a href="#products"><i class="fas fa-chevron-left me-1"></i> المنتجات</a></p>
                        <p><a href="#order"><i class="fas fa-chevron-left me-1"></i> إتمام الطلب</a></p>
                        <p><a href="#contact"><i class="fas fa-chevron-left me-1"></i> اتصل بنا</a></p>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">معلومات الاتصال</h5>
                    <p><i class="fas fa-phone-alt me-2"></i> <span id="footerPhone" class="french-phone">+213 671 84 66 13</span></p>
                    <p><i class="fas fa-envelope me-2"></i> <span id="footerEmail">piyouma24@gmail.com</span></p>
                    <p><i class="fas fa-map-marker-alt me-2"></i> التوصيل لجميع ولايات الجزائر</p>
                </div>
            </div>
            
            <hr class="my-4 bg-light">
            
            <div class="text-center pt-3">
                <p class="mb-0">© <span id="currentYear">2026</span> <span id="storeNameCopyright">متجرنا الإلكتروني</span> - جميع الحقوق محفوظة</p>
                <p class="small">تسوق آمن | ضمان على المنتجات | خدمة عملاء 24/7</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
      <!-- index JS -->
     <script src="JS/index.js"></script>
    
    <!-- إضافة مكتبة animate.css للحركات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

</body>
</html>