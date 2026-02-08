  let cart = [];
        let deliveryCost = 0;
        let settings = {
            storeName: 'متجرنا الإلكتروني',
            whatsappNumber: '0671846613', // الرقم الجزائري +213 671 84 66 13 بدون مسافات أو رموز
            storeEmail: 'piyouma24@gmail.com',
            storeDescription: 'متجرنا الإلكتروني - تسوق آمن مع الدفع عند الاستلام، توصيل سريع لجميع ولايات الجزائر. منتجات أصلية بأسعار منافسة.',
            storePolicy: 'التوصيل يتم في فترة ما بين 24 ساعة إلى 48 ساعة. فترة الإرجاع المسموحة خلال يومين فقط',
            defaultDelivery: '300'
        };
        let allProducts = [];
        let categories = [];
        let currentCategory = "all";
        let defaultDeliveryCost = 300;

        // دالة لتحميل بيانات الولايات من قاعدة البيانات
        async function loadWilayas() {
            try {
                const response = await fetch('get.php?type=wilayas');
                if (!response.ok) throw new Error('خطأ في الاتصال بالخادم');
                
                const data = await response.json();
                if (data.success) {
                    populateWilayaSelect(data.wilayas);
                    showNotification(`✅ تم تحميل ${data.count} ولاية`, 'success');
                } else {
                    throw new Error(data.message || 'خطأ في تحميل بيانات الولايات');
                }
            } catch (error) {
                console.error('خطأ في تحميل الولايات:', error);
                // استخدام البيانات الافتراضية في حالة الخطأ
                showNotification('⚠️ استخدام بيانات الوليات الافتراضية', 'warning');
                populateWilayaSelect(getDefaultWilayas());
            }
        }

        // دالة لملء قائمة الاختيار بالولايات
        function populateWilayaSelect(wilayas) {
            const wilayaSelect = document.getElementById('wilaya');
            if (!wilayaSelect) return;
            
            // حفظ الخيار الأول (اختر ولايتك)
            const firstOption = wilayaSelect.options[0];
            wilayaSelect.innerHTML = '';
            wilayaSelect.appendChild(firstOption);
            
            // إضافة الولايات من قاعدة البيانات
            wilayas.forEach(wilaya => {
                const option = document.createElement('option');
                option.value = wilaya.delivery_price || 0;
                option.textContent = `${wilaya.wilaya_code} - ${wilaya.wilaya_name}`;
                option.setAttribute('data-code', wilaya.wilaya_code);
                wilayaSelect.appendChild(option);
            });
        }

        // بيانات الوليات الافتراضية في حالة عدم الاتصال بالخادم
        function getDefaultWilayas() {
            return [
                { wilaya_code: '01', wilaya_name: 'أدرار', delivery_price: 1500 },
                { wilaya_code: '02', wilaya_name: 'الشلف', delivery_price: 800 },
                { wilaya_code: '03', wilaya_name: 'الأغواط', delivery_price: 900 },
                { wilaya_code: '04', wilaya_name: 'أم البواقي', delivery_price: 850 },
                { wilaya_code: '05', wilaya_name: 'باتنة', delivery_price: 950 },
                { wilaya_code: '06', wilaya_name: 'بجاية', delivery_price: 750 },
                { wilaya_code: '07', wilaya_name: 'بسكرة', delivery_price: 1000 },
                { wilaya_code: '08', wilaya_name: 'بشار', delivery_price: 1300 },
                { wilaya_code: '09', wilaya_name: 'البليدة', delivery_price: 700 },
                { wilaya_code: '10', wilaya_name: 'البويرة', delivery_price: 750 },
                { wilaya_code: '11', wilaya_name: 'تمنراست', delivery_price: 1500 },
                { wilaya_code: '12', wilaya_name: 'تبسة', delivery_price: 950 },
                { wilaya_code: '13', wilaya_name: 'تلمسان', delivery_price: 850 },
                { wilaya_code: '14', wilaya_name: 'تيارت', delivery_price: 850 },
                { wilaya_code: '15', wilaya_name: 'تيزي وزو', delivery_price: 800 },
                { wilaya_code: '16', wilaya_name: 'الجزائر', delivery_price: 450 },
                { wilaya_code: '17', wilaya_name: 'الجلفة', delivery_price: 900 },
                { wilaya_code: '18', wilaya_name: 'جيجل', delivery_price: 750 },
                { wilaya_code: '19', wilaya_name: 'سطيف', delivery_price: 800 },
                { wilaya_code: '20', wilaya_name: 'سعيدة', delivery_price: 950 },
                { wilaya_code: '21', wilaya_name: 'سكيكدة', delivery_price: 800 },
                { wilaya_code: '22', wilaya_name: 'سيدي بلعباس', delivery_price: 900 },
                { wilaya_code: '23', wilaya_name: 'عنابة', delivery_price: 850 },
                { wilaya_code: '24', wilaya_name: 'قالمة', delivery_price: 850 },
                { wilaya_code: '25', wilaya_name: 'قسنطينة', delivery_price: 850 },
                { wilaya_code: '26', wilaya_name: 'المدية', delivery_price: 750 },
                { wilaya_code: '27', wilaya_name: 'مستغانم', delivery_price: 800 },
                { wilaya_code: '28', wilaya_name: 'المسيلة', delivery_price: 900 },
                { wilaya_code: '29', wilaya_name: 'معسكر', delivery_price: 850 },
                { wilaya_code: '30', wilaya_name: 'ورقلة', delivery_price: 1100 },
                { wilaya_code: '31', wilaya_name: 'وهران', delivery_price: 700 },
                { wilaya_code: '32', wilaya_name: 'البيض', delivery_price: 1000 },
                { wilaya_code: '33', wilaya_name: 'إليزي', delivery_price: 1400 },
                { wilaya_code: '34', wilaya_name: 'برج بوعريريج', delivery_price: 800 },
                { wilaya_code: '35', wilaya_name: 'بومرداس', delivery_price: 750 },
                { wilaya_code: '36', wilaya_name: 'الطارف', delivery_price: 850 },
                { wilaya_code: '37', wilaya_name: 'تندوف', delivery_price: 1600 },
                { wilaya_code: '38', wilaya_name: 'تيسمسيلت', delivery_price: 850 },
                { wilaya_code: '39', wilaya_name: 'الوادي', delivery_price: 1050 },
                { wilaya_code: '40', wilaya_name: 'خنشلة', delivery_price: 950 },
                { wilaya_code: '41', wilaya_name: 'سوق أهراس', delivery_price: 900 },
                { wilaya_code: '42', wilaya_name: 'تيبازة', delivery_price: 750 },
                { wilaya_code: '43', wilaya_name: 'ميلة', delivery_price: 800 },
                { wilaya_code: '44', wilaya_name: 'عين الدفلى', delivery_price: 800 },
                { wilaya_code: '45', wilaya_name: 'النعامة', delivery_price: 1100 },
                { wilaya_code: '46', wilaya_name: 'عين تموشنت', delivery_price: 900 },
                { wilaya_code: '47', wilaya_name: 'غرداية', delivery_price: 1000 },
                { wilaya_code: '48', wilaya_name: 'غليزان', delivery_price: 850 },
                { wilaya_code: '49', wilaya_name: 'تيميمون', delivery_price: 1250 },
                { wilaya_code: '50', wilaya_name: 'برج باجي مختار', delivery_price: 1450 },
                { wilaya_code: '51', wilaya_name: 'أولاد جلال', delivery_price: 1000 },
                { wilaya_code: '52', wilaya_name: 'بني عباس', delivery_price: 1350 },
                { wilaya_code: '53', wilaya_name: 'عين صالح', delivery_price: 1550 },
                { wilaya_code: '54', wilaya_name: 'عين قزام', delivery_price: 1650 },
                { wilaya_code: '55', wilaya_name: 'تقرت', delivery_price: 1150 },
                { wilaya_code: '56', wilaya_name: 'جانت', delivery_price: 1700 },
                { wilaya_code: '57', wilaya_name: 'المغير', delivery_price: 1200 },
                { wilaya_code: '58', wilaya_name: 'المنيعة', delivery_price: 1300 }
            ];
        }

        // دالة لتحديث واجهة المستخدم بالقيم الافتراضية
        function initializeDefaultSettings() {
            const storeName = settings.storeName;
            document.getElementById('storeNameNav').textContent = storeName;
            document.getElementById('storeNameHeader').textContent = storeName;
            document.getElementById('storeNameFooter').textContent = storeName;
            document.getElementById('storeNameCopyright').textContent = storeName;
            document.getElementById('pageTitle').textContent = `${storeName} | الدفع عند الاستلام | تسوق آمن في الجزائر`;
            
            document.getElementById('pageDescription').textContent = `${storeName} - ${settings.storeDescription}`;
            
            const whatsappNumber = settings.whatsappNumber;
            // تنسيق الرقم بالطريقة الفرنسية: +213 671 84 66 13 (من اليسار لليمين)
            const formattedWhatsapp = `+213 671 84 66 13`;
            
            // إضافة كلاس french-phone لعرض الأرقام من اليسار لليمين
            document.getElementById('whatsappNumber').textContent = formattedWhatsapp;
            document.getElementById('whatsappNumber2').textContent = formattedWhatsapp;
            document.getElementById('contactPhone').textContent = formattedWhatsapp;
            document.getElementById('contactPhone2').textContent = formattedWhatsapp;
            document.getElementById('footerPhone').textContent = formattedWhatsapp;
            
            // تحديث رابط الواتساب مع الرمز الدولي للجزائر (+213)
            document.getElementById('whatsappLink').href = `https://wa.me/213${whatsappNumber.substring(1)}`;
            
            const storeEmail = settings.storeEmail;
            document.getElementById('storeEmail').textContent = storeEmail;
            document.getElementById('storeEmail2').textContent = storeEmail;
            document.getElementById('footerEmail').textContent = storeEmail;
            
            document.getElementById('storeDescription').textContent = settings.storeDescription;
            document.getElementById('footerDescription').textContent = 'نقدم لكم تجربة تسوق آمنة وسهلة مع ضمان الجودة والدفع عند الاستلام.';
            
            const storePolicy = settings.storePolicy;
            document.getElementById('deliveryPolicy').innerHTML = `<strong>سياسة التوصيل:</strong> ${storePolicy}`;
        }

        // تحميل الفئات من قاعدة البيانات
        async function loadCategories() {
            try {
                const response = await fetch('get.php?type=categories');
                const data = await response.json();
                
                if (data.success) {
                    categories = data.categories || [];
                } else {
                    // إذا لم تكن هناك فئات، استخدم الفئات الأساسية
                    categories = ['الكترونيات', 'منزلية', 'إكسسوارات', 'أخرى'];
                    
                    // محاولة الحصول على الفئات من المنتجات الموجودة
                    if (allProducts.length > 0) {
                        const uniqueCategories = [...new Set(allProducts
                            .filter(p => p.category && p.category.trim() !== '')
                            .map(p => p.category))];
                        
                        if (uniqueCategories.length > 0) {
                            categories = [...new Set([...categories, ...uniqueCategories])];
                        }
                    }
                }
                
                renderCategories();
            } catch (error) {
                console.error('خطأ في تحميل الفئات:', error);
                categories = ['الكترونيات', 'منزلية', 'إكسسوارات', 'أخرى'];
                renderCategories();
            }
        }

        // تحميل المنتجات من قاعدة البيانات
        async function loadProducts() {
            try {
                const response = await fetch('get.php?type=products');
                const data = await response.json();
                
                if (data.success) {
                    allProducts = data.products || [];
                    if (allProducts.length === 0) {
                        showNotification('⚠️ لا توجد منتجات متاحة حالياً', 'warning');
                    }
                } else {
                    allProducts = [];
                    showNotification('❌ حدث خطأ في تحميل المنتجات', 'error');
                }
                
                await loadCategories();
                filterProductsByCategory(currentCategory);
                
                // بدء العد التنازلي بعد تحميل المنتجات
                setTimeout(() => {
                    updateGlobalDiscountTimer();
                }, 100);
                
            } catch (error) {
                console.error('خطأ في تحميل المنتجات:', error);
                allProducts = [];
                showNotification('❌ حدث خطأ في تحميل المنتجات', 'error');
                await loadCategories();
                filterProductsByCategory(currentCategory);
            }
        }

        // دالة لتنسيق الوقت المتبقي بشكل جميل
        function formatTimeRemaining(ms) {
            if (ms <= 0) return 'انتهى';
            
            const days = Math.floor(ms / (1000 * 60 * 60 * 24));
            const hours = Math.floor((ms % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((ms % (1000 * 60 * 60)) / (1000 * 60));
            
            const parts = [];
            if (days > 0) parts.push(`${days} يوم`);
            if (hours > 0) parts.push(`${hours} ساعة`);
            if (minutes > 0 && days === 0) parts.push(`${minutes} دقيقة`);
            
            return parts.join(' و ');
        }

        // دالة لتنسيق التاريخ
        function formatDate(date) {
            const d = new Date(date);
            return d.toLocaleDateString('ar-EG', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // عرض الفئات
        function renderCategories() {
            const container = document.getElementById('categoryButtons');
            if (!container) return;
            
            let buttonsHtml = `
                <button class="category-btn ${currentCategory === 'all' ? 'active' : ''}" 
                        onclick="filterProductsByCategory('all')">
                    <i class="fas fa-th-large"></i> جميع المنتجات
                </button>
            `;
            
            categories.forEach(category => {
                if (category && category.trim() !== '') {
                    buttonsHtml += `
                        <button class="category-btn ${currentCategory === category ? 'active' : ''}" 
                                onclick="filterProductsByCategory('${category}')">
                            <i class="fas fa-tag"></i> ${category}
                        </button>
                    `;
                }
            });
            
            container.innerHTML = buttonsHtml;
        }

        // تصفية المنتجات حسب الفئة
        function filterProductsByCategory(category) {
            currentCategory = category;
            
            let filteredProducts = allProducts;
            if (category !== 'all') {
                filteredProducts = allProducts.filter(p => p.category === category);
            }
            
            renderProducts(filteredProducts);
            
            // تحديث أزرار الفئات النشطة
            const buttons = document.querySelectorAll('.category-btn');
            buttons.forEach(btn => {
                const btnText = btn.textContent.includes(category) ? category : 
                               btn.textContent.includes('جميع المنتجات') ? 'all' : '';
                if (btnText === category || (category === 'all' && btnText === 'all')) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            
            if (category !== 'all') {
                showNotification(`✅ تم عرض منتجات ${category}`, 'success');
            }
        }

        // عرض المنتجات في الصفحة مع مدة التخفيض
        function renderProducts(productsToShow) {
            const container = document.getElementById('productsContainer');
            if (!container) return;
            
            if (productsToShow.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h4>لا توجد منتجات في هذه الفئة حالياً</h4>
                        <p class="text-muted">جرب فئة أخرى أو عد لاحقاً</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = productsToShow.map(product => {
                const originalPrice = parseFloat(product.price) || 0;
                let finalPrice = originalPrice;
                let discountHtml = '';
                let priceHtml = '';
                let discountTimerHtml = '';
                let discountProgressHtml = '';
                let discountDatesHtml = '';
                let featuredBadge = '';
                
                const hasDiscount = product.has_discount == 1 && product.discount_percentage > 0;
                const now = new Date();
                
                let discountValid = hasDiscount;
                let discountStatus = 'no_discount';
                
                if (hasDiscount && product.discount_start_date && product.discount_end_date) {
                    const startDate = new Date(product.discount_start_date);
                    const endDate = new Date(product.discount_end_date);
                    
                    if (now < startDate) {
                        discountValid = false;
                        discountStatus = 'upcoming';
                    } else if (now >= startDate && now <= endDate) {
                        discountValid = true;
                        discountStatus = 'active';
                    } else {
                        discountValid = false;
                        discountStatus = 'expired';
                    }
                }
                
                // حساب مدة التخفيض
                let timeRemainingHtml = '';
                let progressPercentage = 0;
                
                if (hasDiscount && product.discount_start_date && product.discount_end_date) {
                    const startDate = new Date(product.discount_start_date);
                    const endDate = new Date(product.discount_end_date);
                    const totalDuration = endDate - startDate;
                    
                    if (discountStatus === 'active') {
                        const elapsed = now - startDate;
                        const remaining = endDate - now;
                        progressPercentage = Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
                        
                        const daysRemaining = Math.floor(remaining / (1000 * 60 * 60 * 24));
                        const hoursRemaining = Math.floor((remaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutesRemaining = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
                        
                        timeRemainingHtml = `
                            <div class="discount-timer ${discountStatus}">
                                <i class="fas fa-clock"></i>
                                <span class="discount-countdown">${daysRemaining}</span> يوم
                                <span>${hoursRemaining}</span> ساعة
                                <span>${minutesRemaining}</span> دقيقة
                            </div>
                            <div class="discount-progress-container">
                                <div class="discount-progress-bar" style="width: ${100 - progressPercentage}%"></div>
                            </div>
                            <div class="discount-time-info">
                                <span>${formatTimeRemaining(remaining)} متبقي</span>
                                <span>${Math.round(100 - progressPercentage)}% متبقي</span>
                            </div>
                        `;
                    } else if (discountStatus === 'upcoming') {
                        const timeUntilStart = startDate - now;
                        const daysUntil = Math.floor(timeUntilStart / (1000 * 60 * 60 * 24));
                        const hoursUntil = Math.floor((timeUntilStart % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        
                        timeRemainingHtml = `
                            <div class="discount-timer upcoming">
                                <i class="fas fa-hourglass-start"></i>
                                <span>يبدأ بعد: ${daysUntil} يوم و ${hoursUntil} ساعة</span>
                            </div>
                        `;
                    } else if (discountStatus === 'expired') {
                        timeRemainingHtml = `
                            <div class="discount-timer expired">
                                <i class="fas fa-hourglass-end"></i>
                                <span>انتهى التخفيض</span>
                            </div>
                        `;
                    }
                    
                    // عرض تواريخ التخفيض
                    discountDatesHtml = `
                        <div class="discount-time-start">
                            <small><i class="far fa-calendar-alt"></i> يبدأ: ${formatDate(startDate)}</small>
                        </div>
                        <div class="discount-time-end">
                            <small><i class="far fa-calendar-times"></i> ينتهي: ${formatDate(endDate)}</small>
                        </div>
                    `;
                }
                
                if (discountValid) {
                    const discountPercentage = parseFloat(product.discount_percentage) || 0;
                    const discountAmount = (originalPrice * discountPercentage) / 100;
                    finalPrice = originalPrice - discountAmount;
                    
                    // شارة تخفيض مميزة للخصومات الكبيرة
                    if (discountPercentage >= 30) {
                        featuredBadge = `<span class="discount-featured ${discountPercentage >= 50 ? 'mega-discount' : ''}">خصم ${discountPercentage}%</span>`;
                    }
                    
                    discountHtml = `<span class="discount-badge ${discountPercentage >= 30 ? 'discount-limited' : ''}">${discountPercentage}% خصم</span>`;
                    priceHtml = `
                        <div class="product-price">
                            <span class="original-price">${originalPrice.toLocaleString()} دج</span>
                            <span class="discounted-price">${finalPrice.toLocaleString()} دج</span>
                        </div>
                        ${timeRemainingHtml}
                        ${discountDatesHtml}
                    `;
                } else {
                    priceHtml = `
                        <div class="product-price" style="color: var(--danger-color);">
                            ${originalPrice.toLocaleString()} دج
                        </div>
                        ${timeRemainingHtml}
                        ${discountDatesHtml}
                    `;
                }
                
                const imageHtml = product.image_url && product.image_url.startsWith('data:image')
                    ? `<img src="${product.image_url}" alt="${product.name}" class="product-img" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\\'fas fa-box text-primary\\'></i>';">`
                    : `<div class="product-img">
                            <i class="fas fa-box text-primary"></i>
                       </div>`;
                
                const categoryBadge = product.category 
                    ? `<span class="product-category">${product.category}</span>`
                    : '';
                
                const stockStatus = parseInt(product.stock) > 0 
                    ? `<span class="badge bg-success">متوفر: ${product.stock}</span>`
                    : `<span class="badge bg-danger">نفذ من المخزون</span>`;
                
                return `
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="product-card position-relative">
                        ${categoryBadge}
                        ${featuredBadge}
                        ${imageHtml}
                        <div class="p-3">
                            <h5>${product.name} ${discountHtml}</h5>
                            <p class="text-muted small">${product.description || 'منتج عالي الجودة'}</p>
                            ${priceHtml}
                            <div class="d-flex justify-content-between align-items-center mt-2 mb-2">
                                ${stockStatus}
                            </div>
                            <button class="btn-add-to-cart" onclick="addToCart(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${finalPrice})" ${parseInt(product.stock) <= 0 ? 'disabled' : ''}>
                                <i class="fas fa-cart-plus me-2"></i>${parseInt(product.stock) > 0 ? 'إضافة للسلة' : 'نفذ من المخزون'}
                            </button>
                        </div>
                    </div>
                </div>
                `;
            }).join('');
        }

        // إضافة منتج للسلة مع إشعار التخفيض
        function addToCart(id, name, price){
            const product = allProducts.find(p => p.id == id);
            const existingItemIndex = cart.findIndex(item => item.id === id);
            
            if (existingItemIndex > -1) {
                cart[existingItemIndex].quantity += 1;
            } else {
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: 1,
                    originalPrice: parseFloat(product?.price) || price,
                    hasDiscount: product?.has_discount == 1 && product?.discount_percentage > 0
                });
            }
            
            updateTotals();
            
            // إشعار خاص للمنتجات المخفضة
            if (product?.has_discount == 1 && product?.discount_percentage > 0) {
                const discountPercentage = parseFloat(product.discount_percentage) || 0;
                const savedAmount = (parseFloat(product.price) * discountPercentage) / 100;
                
                showNotification(`🎉 ${name} تمت إضافته للسلة مع خصم ${discountPercentage}% وفرت ${savedAmount.toLocaleString()} دج`, 'success');
            } else {
                showNotification(`✅ ${name} تمت إضافته للسلة`, 'success');
            }
            
            updateCartAnimation();
        }

        // حساب تكلفة التوصيل
        function calcDelivery(){
            let wilayaSelect = document.getElementById("wilaya");
            deliveryCost = parseInt(wilayaSelect.value) || defaultDeliveryCost;
            updateTotals();
            
            if (deliveryCost > 0 && wilayaSelect.selectedIndex > 0) {
                const wilayaName = wilayaSelect.options[wilayaSelect.selectedIndex].text.split(' - ')[1];
                showNotification(`🚚 سعر التوصيل لـ ${wilayaName}: ${deliveryCost.toLocaleString()} دج`, 'success');
            }
        }

        // تحديث الإجماليات
        function updateTotals(){
            let total = cart.reduce((sum, p) => sum + (p.price * p.quantity), 0);
            document.getElementById("productsTotal").innerText = total.toLocaleString() + ' دج';
            document.getElementById("delivery").innerText = deliveryCost.toLocaleString() + ' دج';
            document.getElementById("grandTotal").innerText = (total + deliveryCost).toLocaleString() + ' دج';
            
            const totalItems = cart.reduce((sum, p) => sum + p.quantity, 0);
            document.getElementById("cartCount").innerText = totalItems;
        }

        // تحديث العد التنازلي للتخفيضات في الوقت الحقيقي
        function updateDiscountTimers() {
            const timers = document.querySelectorAll('.discount-timer.active');
            
            timers.forEach(timer => {
                const countdownElement = timer.querySelector('.discount-countdown');
                const daysElement = timer.querySelector('.discount-countdown');
                const hoursElement = timer.querySelector('span:nth-child(4)');
                const minutesElement = timer.querySelector('span:nth-child(6)');
                
                if (countdownElement && daysElement && hoursElement && minutesElement) {
                    let days = parseInt(daysElement.textContent) || 0;
                    let hours = parseInt(hoursElement.textContent) || 0;
                    let minutes = parseInt(minutesElement.textContent) || 0;
                    
                    // تقليل الدقائق
                    minutes--;
                    
                    if (minutes < 0) {
                        minutes = 59;
                        hours--;
                        
                        if (hours < 0) {
                            hours = 23;
                            days--;
                            
                            if (days < 0) {
                                // التخفيض انتهى
                                timer.classList.remove('active');
                                timer.classList.add('expired');
                                timer.innerHTML = '<i class="fas fa-hourglass-end"></i><span>انتهى التخفيض</span>';
                                return;
                            }
                        }
                    }
                    
                    // تحديث القيم
                    daysElement.textContent = days;
                    hoursElement.textContent = hours.toString().padStart(2, '0');
                    minutesElement.textContent = minutes.toString().padStart(2, '0');
                }
            });
        }

        // تشغيل تحديث العد التنازلي كل دقيقة
        setInterval(updateDiscountTimers, 60000);

        // دالة لتحديث العداد العالمي للتخفيضات
        function updateGlobalDiscountTimer() {
            const activeDiscounts = allProducts.filter(p => 
                p.has_discount == 1 && 
                p.discount_start_date && 
                p.discount_end_date
            );
            
            if (activeDiscounts.length === 0) {
                document.getElementById('globalDiscountTimer').style.display = 'none';
                return;
            }
            
            // إيجاد أقرب تاريخ انتهاء للتخفيضات
            const now = new Date();
            let nearestEndDate = null;
            
            activeDiscounts.forEach(product => {
                const endDate = new Date(product.discount_end_date);
                if (endDate > now) {
                    if (!nearestEndDate || endDate < nearestEndDate) {
                        nearestEndDate = endDate;
                    }
                }
            });
            
            if (!nearestEndDate) {
                document.getElementById('globalDiscountTimer').innerHTML = '<i class="fas fa-hourglass-end"></i><span>انتهت العروض</span>';
                return;
            }
            
            const timeRemaining = nearestEndDate - now;
            
            if (timeRemaining > 0) {
                const days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);
                
                document.getElementById('discountDays').textContent = days;
                document.getElementById('discountHours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('discountMinutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('discountSeconds').textContent = seconds.toString().padStart(2, '0');
            } else {
                document.getElementById('globalDiscountTimer').innerHTML = '<i class="fas fa-hourglass-end"></i><span>انتهت العروض</span>';
            }
        }

        // تحديث العداد العالمي كل ثانية
        setInterval(updateGlobalDiscountTimer, 1000);

        // إظهار إشعارات
        function showNotification(message, type = 'success') {
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            let notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }

        // إرسال الطلب عبر واتساب
        function sendOrder(e){
            e.preventDefault();

            if(cart.length === 0){
                showNotification("❌ السلة فارغة، يرجى إضافة منتجات أولاً", 'error');
                document.querySelector('#products').scrollIntoView({behavior: 'smooth'});
                return;
            }

            let name = document.getElementById("name").value.trim();
            let phone = document.getElementById("phone").value.trim();
            let wilayaSelect = document.getElementById("wilaya");
            let wilaya = wilayaSelect.value;
            let wilayaText = wilayaSelect.options[wilayaSelect.selectedIndex].text;
            let address = document.getElementById("address").value.trim();

            if(!name || !phone || !wilaya || !address){
                showNotification("❌ يرجى ملء جميع الحقول المطلوبة", 'error');
                return;
            }

            const phoneRegex = /^[0-9]{10}$/;
            if (!phoneRegex.test(phone)) {
                showNotification("❌ يرجى إدخال رقم هاتف صحيح (10 أرقام)", 'error');
                return;
            }

            let total = document.getElementById("grandTotal").innerText;
            let productsTotal = document.getElementById("productsTotal").innerText;
            let place = document.getElementById("place").value;

            let message = `📋 *طلب جديد من ${settings.storeName}*%0A%0A`;
            message += `👤 *العميل:* ${name}%0A`;
            message += `📞 *الهاتف:* ${phone}%0A`;
            message += `📍 *الولاية:* ${wilayaText}%0A`;
            message += `🏠 *العنوان:* ${address}%0A`;
            message += `📦 *مكان التوصيل:* ${place}%0A%0A`;
            message += `🛒 *المنتجات المطلوبة:*%0A`;
            message += `═══════════════════════════%0A`;
            
            cart.forEach((p, index) => {
                message += `*${index + 1}. ${p.name}*%0A`;
                message += `الكمية: ${p.quantity}%0A`;
                message += `السعر: ${(p.price * p.quantity).toLocaleString()} دج%0A`;
                message += `────────────────────%0A`;
            });
            
            message += `%0A💰 *ملخص الطلب:*%0A`;
            message += `═══════════════════════════%0A`;
            message += `سعر المنتجات: ${productsTotal}%0A`;
            message += `سعر التوصيل: ${deliveryCost.toLocaleString()} دج%0A`;
            message += `*المجموع الإجمالي: ${total}*%0A%0A`;
            message += `💵 *طريقة الدفع:* الدفع عند الاستلام%0A`;
            message += `🚚 *مدة التوصيل:* 24-48 ساعة%0A%0A`;
            message += `📞 *للتواصل:* ${document.getElementById('whatsappNumber').textContent}%0A`;
            message += `شكراً لثقتكم بمتجرنا! 🛍️`;

            let whatsappNumber = settings.whatsappNumber;
            
            window.open(
                `https://wa.me/213${whatsappNumber.substring(1)}?text=${message}`,
                "_blank"
            );
            
            showNotification("📱 يتم فتح واتساب لإرسال الطلب...", 'success');
            
            setTimeout(() => {
                cart = [];
                deliveryCost = 0;
                updateTotals();
                document.getElementById("orderForm").reset();
            }, 1000);
        }

        // تحديث حركة عداد السلة
        function updateCartAnimation() {
            const cartCount = document.getElementById('cartCount');
            cartCount.classList.add('pulse');
            setTimeout(() => {
                cartCount.classList.remove('pulse');
            }, 2000);
        }

        // تحميل الصفحة
        window.onload = async function() {
            console.log("متجرنا الإلكتروني جاهز للاستخدام!");
            
            document.getElementById('currentYear').textContent = new Date().getFullYear();
            
            // استخدام الإعدادات الافتراضية مباشرة
            initializeDefaultSettings();
            
            // تحميل البيانات من قاعدة البيانات
            await Promise.all([
                loadWilayas(),
                loadProducts()
            ]);
            
            updateTotals();
            
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('shadow');
                } else {
                    navbar.classList.remove('shadow');
                }
            });
        };

        // تحسين تجربة المستخدم للهواتف
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
