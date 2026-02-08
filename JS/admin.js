        // ========== CONFIGURATION ==========
        const CONFIG = {
            API_BASE_URL: './',
            IMAGE: {
                MAX_SIZE: 2 * 1024 * 1024, // 2MB
                VALID_TYPES: ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
            }
        };
        
        // ========== STATE MANAGEMENT ==========
        const AppState = {
            currentImageData: null,
            wilayasData: [],
            currentPage: 'products',
            
            setWilayasData(data) {
                this.wilayasData = data;
            },
            
            getWilayaById(id) {
                return this.wilayasData.find(w => w.id === id);
            }
        };
        
        // ========== DISCOUNT UTILITIES ==========
        const DiscountUtils = {
            calculateTimeRemaining(startDate, endDate) {
                const now = new Date();
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (now < start) {
                    // التخفيض لم يبدأ بعد
                    const diff = start - now;
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    
                    return {
                        status: 'upcoming',
                        message: `يبدأ بعد: ${days} يوم و ${hours} ساعة`,
                        days: days,
                        percentage: 0
                    };
                } else if (now >= start && now <= end) {
                    // التخفيض فعال
                    const totalDuration = end - start;
                    const elapsed = now - start;
                    const remaining = end - now;
                    
                    const daysRemaining = Math.floor(remaining / (1000 * 60 * 60 * 24));
                    const hoursRemaining = Math.floor((remaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    
                    const percentageComplete = Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
                    
                    return {
                        status: 'active',
                        message: `ينتهي بعد: ${daysRemaining} يوم و ${hoursRemaining} ساعة`,
                        days: daysRemaining,
                        percentage: Math.round(100 - percentageComplete)
                    };
                } else {
                    // التخفيض انتهى
                    return {
                        status: 'expired',
                        message: 'انتهى التخفيض',
                        days: 0,
                        percentage: 0
                    };
                }
            },
            
            formatDiscountStatus(status, startDate, endDate) {
                if (!startDate || !endDate) {
                    return '<span class="discount-expired">لا توجد تواريخ محددة</span>';
                }
                
                const timeInfo = this.calculateTimeRemaining(startDate, endDate);
                
                let badgeClass = '';
                switch (timeInfo.status) {
                    case 'active':
                        badgeClass = 'discount-active';
                        break;
                    case 'upcoming':
                        badgeClass = 'discount-upcoming';
                        break;
                    case 'expired':
                        badgeClass = 'discount-expired';
                        break;
                }
                
                return `
                    <div class="discount-time-badge ${badgeClass}">
                        ${timeInfo.message}
                    </div>
                    ${timeInfo.status === 'active' ? `
                    <div class="discount-progress mt-1">
                        <div class="discount-progress-bar" style="width: ${timeInfo.percentage}%"></div>
                    </div>
                    ` : ''}
                `;
            },
            
            validateDiscountDates(startDate, endDate) {
                if (!startDate || !endDate) return true;
                
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (start >= end) {
                    return 'تاريخ البداية يجب أن يكون قبل تاريخ النهاية';
                }
                
                if (end < new Date()) {
                    return 'تاريخ النهاية لا يمكن أن يكون في الماضي';
                }
                
                return true;
            },
            
            getDiscountDates(product) {
                return {
                    start: product.discount_start_date || null,
                    end: product.discount_end_date || null,
                    hasDiscount: product.has_discount == 1 && product.discount_percentage > 0
                };
            }
        };
        
        // ========== UTILITIES ==========
        const Utilities = {
            async fetchData(endpoint) {
                try {
                    const response = await fetch(`${CONFIG.API_BASE_URL}${endpoint}`);
                    
                    if (!response.ok) {
                        throw new Error(`خطأ في الاتصال: ${response.status}`);
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('الخادم يعيد بيانات غير JSON');
                    }
                    
                    return await response.json();
                } catch (error) {
                    console.error('خطأ في fetchData:', error.message);
                    UI.showNotification('خطأ في الاتصال بالخادم: ' + error.message, 'error');
                    return null;
                }
            },
            
            async postData(endpoint, data) {
                try {
                    const response = await fetch(`${CONFIG.API_BASE_URL}${endpoint}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data)
                    });
                    
                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(`خطأ في الخادم: ${response.status} - ${errorText}`);
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('الخادم يعيد بيانات غير JSON');
                    }
                    
                    return await response.json();
                } catch (error) {
                    console.error('خطأ في postData:', error.message);
                    UI.showNotification('خطأ في الاتصال بالخادم: ' + error.message, 'error');
                    return null;
                }
            },
            
            async getImageBase64(file) {
                return new Promise((resolve, reject) => {
                    if (!file) {
                        resolve(null);
                        return;
                    }
                    
                    // التحقق من حجم الصورة
                    if (file.size > CONFIG.IMAGE.MAX_SIZE) {
                        UI.showNotification('حجم الصورة كبير جداً. الحد الأقصى 2MB', 'error');
                        reject(new Error('File too large'));
                        return;
                    }
                    
                    // التحقق من نوع الصورة
                    if (!CONFIG.IMAGE.VALID_TYPES.includes(file.type)) {
                        UI.showNotification('نوع الملف غير مدعوم. الرجاء اختيار صورة', 'error');
                        reject(new Error('Invalid file type'));
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        resolve(e.target.result);
                    };
                    reader.onerror = function(error) {
                        reject(error);
                    };
                    reader.readAsDataURL(file);
                });
            },
            
            calculateDiscountPrice(price, percentage) {
                if (price > 0 && percentage > 0) {
                    const discountAmount = (price * percentage) / 100;
                    return price - discountAmount;
                }
                return 0;
            },
            
            formatDateTime(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleString('ar-EG', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        };
        
        // ========== UI MANAGEMENT ==========
        const UI = {
            showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                `;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.style.animation = 'slideOutRight 0.3s ease';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            },
            
            showLoading(text = 'جاري حفظ التغييرات...') {
                document.getElementById('loadingText').textContent = text;
                document.getElementById('loadingOverlay').style.display = 'flex';
            },
            
            hideLoading() {
                document.getElementById('loadingOverlay').style.display = 'none';
            },
            
            previewImage(file) {
                const preview = document.getElementById('previewImage');
                const previewContainer = document.getElementById('imagePreview');
                const removeBtn = document.getElementById('removeImageBtn');
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        previewContainer.style.display = 'block';
                        removeBtn.style.display = 'inline-block';
                    }
                    reader.readAsDataURL(file);
                }
            },
            
            updatePriceSummary() {
                const activeWilayas = AppState.wilayasData.filter(w => w.active);
                const supportedCount = activeWilayas.length;
                
                if (supportedCount > 0) {
                    const prices = activeWilayas.map(w => w.price);
                    const total = prices.reduce((sum, price) => sum + price, 0);
                    const average = Math.round(total / supportedCount);
                    const maxPrice = Math.max(...prices);
                    const minPrice = Math.min(...prices);
                    
                    document.getElementById('supportedCount').textContent = supportedCount;
                    document.getElementById('averagePrice').textContent = average.toLocaleString() + ' دج';
                    document.getElementById('maxPrice').textContent = maxPrice.toLocaleString() + ' دج';
                    document.getElementById('minPrice').textContent = minPrice.toLocaleString() + ' دج';
                } else {
                    document.getElementById('supportedCount').textContent = '0';
                    document.getElementById('averagePrice').textContent = '0 دج';
                    document.getElementById('maxPrice').textContent = '0 دج';
                    document.getElementById('minPrice').textContent = '0 دج';
                }
            },
            
            resetProductForm() {
                document.getElementById('productForm').reset();
                document.getElementById('imagePreview').style.display = 'none';
                document.getElementById('removeImageBtn').style.display = 'none';
                AppState.currentImageData = null;
                document.getElementById('productImageUrl').value = '';
                document.getElementById('discountFields').style.display = 'none';
                document.getElementById('discountInfo').style.display = 'none';
            },
            
            switchPage(pageId) {
                document.querySelectorAll('.content-page').forEach(page => {
                    page.classList.remove('active');
                });
                
                document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                
                document.querySelector(`.sidebar a[data-page="${pageId}"]`).classList.add('active');
                document.getElementById(pageId + 'Page').classList.add('active');
                
                const pageTitles = {
                    products: 'إدارة المنتجات',
                    delivery: 'إدارة أسعار التوصيل'
                };
                
                document.getElementById('pageTitle').textContent = pageTitles[pageId];
                AppState.currentPage = pageId;
                
                // تحديث بيانات الصفحة عند التبديل
                if (pageId === 'products') {
                    Products.loadProducts();
                } else if (pageId === 'delivery') {
                    Delivery.loadDeliveryData();
                }
            },
            
            setupDateTimePickers() {
                const startDateInput = document.getElementById('discountStartDate');
                const endDateInput = document.getElementById('discountEndDate');
                
                const updateTimeRemaining = () => {
                    const startDate = startDateInput.value;
                    const endDate = endDateInput.value;
                    
                    if (startDate && endDate) {
                        const validation = DiscountUtils.validateDiscountDates(startDate, endDate);
                        if (validation !== true) {
                            document.getElementById('discountInfo').innerHTML = `
                                <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ${validation}</span>
                            `;
                            document.getElementById('discountInfo').style.display = 'block';
                            return;
                        }
                        
                        const timeInfo = DiscountUtils.calculateTimeRemaining(startDate, endDate);
                        document.getElementById('discountInfo').innerHTML = `
                            <span class="time-remaining ${timeInfo.status}">
                                <i class="fas fa-clock"></i> ${timeInfo.message}
                            </span>
                        `;
                        document.getElementById('discountInfo').style.display = 'block';
                    } else {
                        document.getElementById('discountInfo').style.display = 'none';
                    }
                };
                
                if (startDateInput) {
                    startDateInput.addEventListener('change', updateTimeRemaining);
                }
                
                if (endDateInput) {
                    endDateInput.addEventListener('change', updateTimeRemaining);
                }
                
                // إعداد Flatpickr للتاريخ والوقت
                flatpickr('.discount-date-input', {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: false,
                    locale: "ar",
                    minDate: "today",
                    allowInput: true
                });
            }
        };
        
        // ========== PRODUCTS MANAGEMENT ==========
        const Products = {
            async loadProducts() {
                try {
                    const products = await Utilities.fetchData('products.php?action=getAll');
                    this.renderProductsTable(products);
                } catch (error) {
                    console.error('خطأ في تحميل المنتجات:', error);
                    document.getElementById('productsTable').innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-danger py-4">
                                <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                <p>حدث خطأ في تحميل المنتجات</p>
                            </td>
                        </tr>
                    `;
                }
            },
            
            renderProductsTable(products) {
                const productsTable = document.getElementById('productsTable');
                
                if (products && products.length > 0) {
                    productsTable.innerHTML = products.map(product => {
                        const originalPrice = parseFloat(product.price || 0);
                        let finalPrice = originalPrice;
                        let discountText = '';
                        let discountInfo = '';
                        
                        const discountDates = DiscountUtils.getDiscountDates(product);
                        
                        if (discountDates.hasDiscount) {
                            const discountAmount = (originalPrice * product.discount_percentage) / 100;
                            finalPrice = originalPrice - discountAmount;
                            discountText = `
                                <span class="discount-badge">
                                    ${product.discount_percentage}% خصم
                                </span>
                            `;
                            
                            // عرض معلومات الوقت المتبقي
                            if (discountDates.start && discountDates.end) {
                                discountInfo = DiscountUtils.formatDiscountStatus(
                                    'active',
                                    discountDates.start,
                                    discountDates.end
                                );
                            }
                        }
                        
                        return `
                        <tr>
                            <td>
                                ${product.image_url ? 
                                    `<img src="${product.image_url}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">` : 
                                    `<div style="width: 50px; height: 50px; background: #f3f4f6; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-box text-muted"></i>
                                    </div>`}
                            </td>
                            <td>
                                ${product.name}
                                ${discountText}
                            </td>
                            <td>${product.category || 'غير محدد'}</td>
                            <td>
                                <div class="price-container">
                                    ${discountDates.hasDiscount ? 
                                        `<span class="original-price">${originalPrice.toLocaleString()} دج</span>
                                         <span class="discounted-price">${finalPrice.toLocaleString()} دج</span>` : 
                                        `<span>${originalPrice.toLocaleString()} دج</span>`
                                    }
                                </div>
                            </td>
                            <td>${parseInt(product.stock || 0)}</td>
                            <td>
                                ${discountInfo || 'لا يوجد تخفيض'}
                            </td>
                            <td>
                                <button class="btn-admin btn-admin-primary btn-sm edit-product-btn" data-id="${product.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-admin btn-admin-danger btn-sm ms-1 delete-product-btn" data-id="${product.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `}).join('');
                    
                    this.attachProductEvents();
                } else {
                    productsTable.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-box-open fa-2x mb-2"></i>
                                <p>لا توجد منتجات</p>
                            </td>
                        </tr>
                    `;
                }
            },
            
            attachProductEvents() {
                document.querySelectorAll('.edit-product-btn').forEach(btn => {
                    btn.onclick = () => {
                        const productId = btn.getAttribute('data-id');
                        this.editProduct(productId);
                    };
                });
                
                document.querySelectorAll('.delete-product-btn').forEach(btn => {
                    btn.onclick = () => {
                        const productId = btn.getAttribute('data-id');
                        this.deleteProduct(productId);
                    };
                });
                
                // البحث في المنتجات
                document.getElementById('searchProduct').addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('#productsTable tr');
                    
                    rows.forEach(row => {
                        const productName = row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '';
                        const category = row.querySelector('td:nth-child(3)')?.textContent?.toLowerCase() || '';
                        
                        if (productName.includes(searchTerm) || category.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            },
            
            async editProduct(id) {
                try {
                    const product = await Utilities.fetchData(`products.php?action=get&id=${id}`);
                    if (product) {
                        document.getElementById('productModalTitle').textContent = 'تعديل المنتج';
                        document.getElementById('productId').value = product.id;
                        document.getElementById('productName').value = product.name || '';
                        document.getElementById('productCategory').value = product.category || 'أخرى';
                        document.getElementById('productPrice').value = product.price || 0;
                        document.getElementById('productStock').value = product.stock || 0;
                        document.getElementById('productDescription').value = product.description || '';
                        
                        // إعداد حقول التخفيض
                        const hasDiscount = product.has_discount == 1;
                        document.getElementById('hasDiscount').checked = hasDiscount;
                        
                        if (hasDiscount) {
                            document.getElementById('discountFields').style.display = 'block';
                            document.getElementById('discountPercentage').value = product.discount_percentage || 0;
                            
                            const discountPrice = Utilities.calculateDiscountPrice(
                                parseFloat(product.price || 0),
                                parseFloat(product.discount_percentage || 0)
                            );
                            document.getElementById('discountPrice').value = discountPrice.toFixed(2);
                            
                            if (product.discount_start_date) {
                                const startDate = new Date(product.discount_start_date);
                                document.getElementById('discountStartDate').value = startDate.toISOString().slice(0, 16);
                            }
                            if (product.discount_end_date) {
                                const endDate = new Date(product.discount_end_date);
                                document.getElementById('discountEndDate').value = endDate.toISOString().slice(0, 16);
                            }
                            
                            // تحديث معلومات الوقت المتبقي
                            const startDate = document.getElementById('discountStartDate').value;
                            const endDate = document.getElementById('discountEndDate').value;
                            if (startDate && endDate) {
                                const timeInfo = DiscountUtils.calculateTimeRemaining(startDate, endDate);
                                document.getElementById('discountInfo').innerHTML = `
                                    <span class="time-remaining ${timeInfo.status}">
                                        <i class="fas fa-clock"></i> ${timeInfo.message}
                                    </span>
                                `;
                                document.getElementById('discountInfo').style.display = 'block';
                            }
                        } else {
                            document.getElementById('discountFields').style.display = 'none';
                        }
                        
                        // إعداد الصورة
                        AppState.currentImageData = null;
                        document.getElementById('imagePreview').style.display = 'none';
                        document.getElementById('removeImageBtn').style.display = 'none';
                        document.getElementById('productImageUrl').value = product.image_url || '';
                        
                        if (product.image_url) {
                            document.getElementById('previewImage').src = product.image_url;
                            document.getElementById('imagePreview').style.display = 'block';
                            document.getElementById('removeImageBtn').style.display = 'inline-block';
                        }
                        
                        // إعداد Flatpickr
                        UI.setupDateTimePickers();
                        
                        new bootstrap.Modal(document.getElementById('productModal')).show();
                    }
                } catch (error) {
                    console.error('خطأ في تحميل المنتج:', error);
                }
            },
            
            async saveProduct() {
                const productId = document.getElementById('productId').value;
                const productData = {
                    name: document.getElementById('productName').value,
                    category: document.getElementById('productCategory').value,
                    price: parseFloat(document.getElementById('productPrice').value),
                    stock: parseInt(document.getElementById('productStock').value),
                    description: document.getElementById('productDescription').value,
                    has_discount: document.getElementById('hasDiscount').checked ? 1 : 0
                };
                
                if (productData.has_discount) {
                    productData.discount_percentage = parseFloat(document.getElementById('discountPercentage').value) || 0;
                    productData.discount_start_date = document.getElementById('discountStartDate').value || null;
                    productData.discount_end_date = document.getElementById('discountEndDate').value || null;
                    
                    // التحقق من صحة التواريخ
                    const validation = DiscountUtils.validateDiscountDates(
                        productData.discount_start_date,
                        productData.discount_end_date
                    );
                    
                    if (validation !== true) {
                        UI.showNotification(validation, 'error');
                        return;
                    }
                }
                
                if (AppState.currentImageData) {
                    productData.image_url = AppState.currentImageData;
                } else if (document.getElementById('productImageUrl').value) {
                    productData.image_url = document.getElementById('productImageUrl').value;
                }
                
                UI.showLoading('جاري حفظ المنتج...');
                
                try {
                    const result = await Utilities.postData(
                        `products.php?action=${productId ? 'update' : 'create'}`,
                        { id: productId || null, ...productData }
                    );
                    
                    UI.hideLoading();
                    
                    if (result && result.success) {
                        UI.showNotification(productId ? 'تم تحديث المنتج بنجاح' : 'تم إضافة المنتج بنجاح', 'success');
                        this.loadProducts();
                        
                        bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
                        UI.resetProductForm();
                    } else {
                        UI.showNotification(result.message || 'حدث خطأ أثناء حفظ المنتج', 'error');
                    }
                } catch (error) {
                    UI.hideLoading();
                    console.error('خطأ في حفظ المنتج:', error);
                    UI.showNotification('حدث خطأ أثناء حفظ المنتج', 'error');
                }
            },
            
            async deleteProduct(id) {
                if (confirm('هل أنت متأكد من حذف هذا المنتج؟')) {
                    try {
                        const result = await Utilities.postData('products.php?action=delete', { id });
                        
                        if (result && result.success) {
                            UI.showNotification('تم حذف المنتج بنجاح', 'success');
                            this.loadProducts();
                        } else {
                            UI.showNotification('حدث خطأ أثناء حذف المنتج', 'error');
                        }
                    } catch (error) {
                        console.error('خطأ في حذف المنتج:', error);
                        UI.showNotification('حدث خطأ أثناء حذف المنتج', 'error');
                    }
                }
            }
        };
        
        // ========== DELIVERY MANAGEMENT ==========
        const Delivery = {
            async loadDeliveryData() {
                const deliveryTable = document.getElementById('deliveryPricesTable');
                
                deliveryTable.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                            <p class="mt-2">جاري تحميل البيانات...</p>
                        </td>
                    </tr>
                `;
                
                const success = await this.fetchWilayasData();
                
                if (success) {
                    const searchTerm = document.getElementById('searchWilaya').value;
                    this.renderDeliveryTable(searchTerm);
                } else {
                    deliveryTable.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-danger py-4">
                                <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                <p>حدث خطأ في تحميل بيانات الولايات</p>
                            </td>
                        </tr>
                    `;
                }
            },
            
            async fetchWilayasData() {
                try {
                    const response = await Utilities.fetchData('delivery.php?action=getAll');
                    
                    if (response && response.success) {
                        const wilayas = response.data.map(wilaya => ({
                            id: parseInt(wilaya.id) || 0,
                            code: wilaya.wilaya_code.toString(),
                            name: wilaya.wilaya_name,
                            price: parseFloat(wilaya.delivery_price) || 0,
                            active: wilaya.is_active == 1 || wilaya.is_active === true
                        }));
                        
                        AppState.setWilayasData(wilayas);
                        return true;
                    }
                    return false;
                } catch (error) {
                    console.error('خطأ في جلب بيانات الولايات:', error);
                    return false;
                }
            },
            
            renderDeliveryTable(searchTerm = '') {
                const deliveryTable = document.getElementById('deliveryPricesTable');
                const searchLower = searchTerm.toLowerCase();
                const filteredWilayas = AppState.wilayasData.filter(wilaya => 
                    wilaya.name.toLowerCase().includes(searchLower) || 
                    wilaya.code.toString().includes(searchTerm)
                );
                
                if (filteredWilayas.length > 0) {
                    deliveryTable.innerHTML = filteredWilayas.map(wilaya => `
                        <tr data-id="${wilaya.id}">
                            <td>
                                <span class="wilaya-badge">${wilaya.code}</span>
                            </td>
                            <td>${wilaya.name}</td>
                            <td>
                                <input type="number" 
                                       class="form-control-admin wilaya-price-input" 
                                       value="${wilaya.price}" 
                                       min="0"
                                       data-id="${wilaya.id}">
                            </td>
                            <td>
                                <span class="badge ${wilaya.active ? 'bg-success' : 'bg-danger'}">
                                    ${wilaya.active ? 'مفعل' : 'معطل'}
                                </span>
                            </td>
                            <td>
                                <button class="btn-admin btn-admin-primary btn-sm edit-wilaya-btn" 
                                        data-id="${wilaya.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-admin btn-admin-danger btn-sm ms-1 delete-wilaya-btn" 
                                        data-id="${wilaya.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                    
                    this.attachDeliveryEvents();
                    UI.updatePriceSummary();
                } else {
                    deliveryTable.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-2"></i>
                                <p>لا توجد ولايات تطابق البحث</p>
                            </td>
                        </tr>
                    `;
                }
            },
            
            attachDeliveryEvents() {
                const deliveryTable = document.getElementById('deliveryPricesTable');
                
                deliveryTable.onclick = (e) => {
                    const target = e.target;
                    
                    if (target.closest('.edit-wilaya-btn')) {
                        const btn = target.closest('.edit-wilaya-btn');
                        const wilayaId = parseInt(btn.getAttribute('data-id'));
                        this.editWilaya(wilayaId);
                    }
                    
                    if (target.closest('.delete-wilaya-btn')) {
                        const btn = target.closest('.delete-wilaya-btn');
                        const wilayaId = parseInt(btn.getAttribute('data-id'));
                        this.deleteWilaya(wilayaId);
                    }
                };
                
                document.querySelectorAll('.wilaya-price-input').forEach(input => {
                    input.onchange = () => {
                        const wilayaId = parseInt(input.getAttribute('data-id'));
                        const price = parseFloat(input.value) || 0;
                        this.updateWilayaPrice(wilayaId, price);
                    };
                });
            },
            
            editWilaya(id) {
                const wilaya = AppState.getWilayaById(id);
                
                if (wilaya) {
                    document.getElementById('editWilayaId').value = wilaya.id;
                    document.getElementById('editWilayaName').value = `${wilaya.code} - ${wilaya.name}`;
                    document.getElementById('editWilayaPrice').value = wilaya.price;
                    document.getElementById('editWilayaActive').checked = wilaya.active;
                    
                    new bootstrap.Modal(document.getElementById('editWilayaModal')).show();
                }
            },
            
            async updateWilayaPrice(id, price) {
                const wilaya = AppState.getWilayaById(id);
                if (wilaya) {
                    wilaya.price = parseInt(price) || 0;
                    
                    try {
                        const result = await Utilities.postData('delivery.php?action=update', {
                            id: wilaya.id,
                            delivery_price: wilaya.price
                        });
                        
                        if (result && result.success) {
                            UI.showNotification('تم تحديث سعر الولاية', 'success');
                            UI.updatePriceSummary();
                        }
                    } catch (error) {
                        console.error('خطأ في تحديث سعر الولاية:', error);
                    }
                }
            },
            
            async deleteWilaya(id) {
                const wilaya = AppState.getWilayaById(id);
                const wilayaName = wilaya ? wilaya.name : 'هذه الولاية';
                
                if (confirm(`هل أنت متأكد من حذف ولاية "${wilayaName}"؟`)) {
                    try {
                        const result = await Utilities.postData('delivery.php?action=delete', { id: id });
                        
                        if (result && result.success) {
                            UI.showNotification(`تم حذف ولاية "${wilayaName}" بنجاح`, 'success');
                            await this.loadDeliveryData();
                        }
                    } catch (error) {
                        console.error('خطأ في حذف الولاية:', error);
                        UI.showNotification('حدث خطأ في حذف الولاية', 'error');
                    }
                }
            },
            
            async saveWilaya() {
                const id = parseInt(document.getElementById('editWilayaId').value);
                const price = parseInt(document.getElementById('editWilayaPrice').value) || 0;
                const active = document.getElementById('editWilayaActive').checked ? 1 : 0;
                
                const wilaya = AppState.getWilayaById(id);
                if (wilaya) {
                    UI.showLoading('جاري تحديث بيانات الولاية...');
                    
                    try {
                        const result = await Utilities.postData('delivery.php?action=update', {
                            id: wilaya.id,
                            delivery_price: price,
                            is_active: active
                        });
                        
                        UI.hideLoading();
                        
                        if (result && result.success) {
                            wilaya.price = price;
                            wilaya.active = active;
                            
                            this.loadDeliveryData();
                            bootstrap.Modal.getInstance(document.getElementById('editWilayaModal')).hide();
                            UI.showNotification('تم تحديث بيانات الولاية بنجاح', 'success');
                        }
                    } catch (error) {
                        UI.hideLoading();
                        console.error('خطأ في تحديث بيانات الولاية:', error);
                        UI.showNotification('حدث خطأ أثناء تحديث البيانات', 'error');
                    }
                }
            },
            
            async saveAllDeliveryPrices() {
                if (AppState.wilayasData.length === 0) {
                    UI.showNotification('لا توجد بيانات لحفظها', 'info');
                    return;
                }
                
                UI.showLoading('جاري حفظ أسعار التوصيل...');
                
                try {
                    const result = await Utilities.postData('delivery.php?action=saveAll', AppState.wilayasData);
                    
                    UI.hideLoading();
                    
                    if (result && result.success) {
                        UI.showNotification('تم حفظ جميع أسعار التوصيل بنجاح', 'success');
                        await this.loadDeliveryData();
                    }
                } catch (error) {
                    UI.hideLoading();
                    console.error('خطأ في حفظ أسعار التوصيل:', error);
                    UI.showNotification('حدث خطأ أثناء حفظ أسعار التوصيل', 'error');
                }
            },
            
            async addNewWilaya() {
                const wilayaCode = document.getElementById('newWilayaCode').value.trim();
                const wilayaName = document.getElementById('newWilayaName').value.trim();
                const deliveryPrice = parseInt(document.getElementById('newWilayaPrice').value) || 0;
                const isActive = document.getElementById('newWilayaActive').checked ? 1 : 0;
                
                if (!wilayaCode || !wilayaName) {
                    UI.showNotification('الرجاء إدخال رقم الولاية واسمها', 'error');
                    return;
                }
                
                UI.showLoading('جاري إضافة الولاية...');
                
                try {
                    const result = await Utilities.postData('delivery.php?action=create', {
                        wilaya_code: wilayaCode,
                        wilaya_name: wilayaName,
                        delivery_price: deliveryPrice,
                        is_active: isActive
                    });
                    
                    UI.hideLoading();
                    
                    if (result && result.success) {
                        bootstrap.Modal.getInstance(document.getElementById('addWilayaModal')).hide();
                        UI.showNotification('تم إضافة الولاية بنجاح', 'success');
                        await this.loadDeliveryData();
                        document.getElementById('addWilayaForm').reset();
                    }
                } catch (error) {
                    UI.hideLoading();
                    console.error('خطأ في إضافة الولاية:', error);
                    UI.showNotification('حدث خطأ أثناء إضافة الولاية', 'error');
                }
            }
        };
        
        // ========== EVENT HANDLERS ==========
        const EventHandlers = {
            setupImageUpload() {
                const chooseImageBtn = document.getElementById('chooseImageBtn');
                const removeImageBtn = document.getElementById('removeImageBtn');
                const fileUploadArea = document.querySelector('.file-upload-area');
                
                chooseImageBtn.addEventListener('click', () => {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = 'image/*';
                    input.onchange = async (e) => {
                        if (e.target.files && e.target.files[0]) {
                            const file = e.target.files[0];
                            
                            try {
                                AppState.currentImageData = await Utilities.getImageBase64(file);
                                UI.previewImage(file);
                                UI.showNotification('تم تحميل الصورة بنجاح', 'success');
                            } catch (error) {
                                UI.showNotification('حدث خطأ أثناء تحميل الصورة', 'error');
                            }
                        }
                    };
                    input.click();
                });
                
                removeImageBtn.addEventListener('click', () => {
                    AppState.currentImageData = null;
                    document.getElementById('imagePreview').style.display = 'none';
                    removeImageBtn.style.display = 'none';
                    document.getElementById('productImageUrl').value = '';
                    UI.showNotification('تم حذف الصورة', 'info');
                });
                
                // سحب وإفلات الصور
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    fileUploadArea.addEventListener(eventName, this.preventDefaults, false);
                });
                
                ['dragenter', 'dragover'].forEach(eventName => {
                    fileUploadArea.addEventListener(eventName, this.highlight, false);
                });
                
                ['dragleave', 'drop'].forEach(eventName => {
                    fileUploadArea.addEventListener(eventName, this.unhighlight, false);
                });
                
                fileUploadArea.addEventListener('drop', async (e) => {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    
                    if (files && files[0] && files[0].type.startsWith('image/')) {
                        const file = files[0];
                        
                        try {
                            AppState.currentImageData = await Utilities.getImageBase64(file);
                            UI.previewImage(file);
                            UI.showNotification('تم تحميل الصورة بنجاح', 'success');
                        } catch (error) {
                            UI.showNotification('حدث خطأ أثناء تحميل الصورة', 'error');
                        }
                    }
                });
            },
            
            preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            },
            
            highlight() {
                document.querySelector('.file-upload-area').classList.add('dragover');
            },
            
            unhighlight() {
                document.querySelector('.file-upload-area').classList.remove('dragover');
            },
            
            setupDiscountEvents() {
                const hasDiscountCheckbox = document.getElementById('hasDiscount');
                const priceInput = document.getElementById('productPrice');
                const discountPercentageInput = document.getElementById('discountPercentage');
                
                hasDiscountCheckbox.addEventListener('change', function() {
                    document.getElementById('discountFields').style.display = this.checked ? 'block' : 'none';
                    if (!this.checked) {
                        document.getElementById('discountInfo').style.display = 'none';
                    }
                });
                
                const calculateDiscount = () => {
                    const price = parseFloat(document.getElementById('productPrice').value) || 0;
                    const discountPercentage = parseFloat(document.getElementById('discountPercentage').value) || 0;
                    
                    if (price > 0 && discountPercentage > 0) {
                        const discountPrice = Utilities.calculateDiscountPrice(price, discountPercentage);
                        document.getElementById('discountPrice').value = discountPrice.toFixed(2);
                    } else {
                        document.getElementById('discountPrice').value = '';
                    }
                };
                
                priceInput.addEventListener('input', calculateDiscount);
                discountPercentageInput.addEventListener('input', calculateDiscount);
                
                discountPercentageInput.addEventListener('change', function() {
                    let value = parseFloat(this.value);
                    if (value < 0) this.value = 0;
                    if (value > 100) this.value = 100;
                    calculateDiscount();
                });
                
                // إعداد Flatpickr
                UI.setupDateTimePickers();
            },
            
            setupNavigation() {
                // التنقل بين الصفحات
                document.querySelectorAll('.sidebar a[data-page]').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const pageId = link.getAttribute('data-page');
                        UI.switchPage(pageId);
                        
                        if (window.innerWidth < 992) {
                            document.getElementById('sidebar').classList.remove('active');
                        }
                    });
                });
                
                // عرض/إخفاء الشريط الجانبي
                document.getElementById('toggleSidebar').addEventListener('click', () => {
                    document.getElementById('sidebar').classList.toggle('active');
                });
                
                // إضافة منتج جديد
                document.getElementById('addProductBtn').addEventListener('click', () => {
                    document.getElementById('productModalTitle').textContent = 'إضافة منتج جديد';
                    document.getElementById('productId').value = '';
                    UI.resetProductForm();
                    UI.setupDateTimePickers();
                    new bootstrap.Modal(document.getElementById('productModal')).show();
                });
                
                // حفظ المنتج
                document.getElementById('saveProductBtn').addEventListener('click', () => {
                    Products.saveProduct();
                });
                
                // إضافة ولاية جديدة
                document.getElementById('addWilayaBtn').addEventListener('click', () => {
                    new bootstrap.Modal(document.getElementById('addWilayaModal')).show();
                });
                
                // حفظ ولاية جديدة
                document.getElementById('saveNewWilayaBtn').addEventListener('click', () => {
                    Delivery.addNewWilaya();
                });
                
                // حفظ سعر ولاية
                document.getElementById('saveWilayaPriceBtn').addEventListener('click', () => {
                    Delivery.saveWilaya();
                });
                
                // حفظ جميع الأسعار
                document.getElementById('saveDeliveryPricesBtn').addEventListener('click', () => {
                    Delivery.saveAllDeliveryPrices();
                });
                
                // البحث في جدول الولايات
                document.getElementById('searchWilaya').addEventListener('input', (e) => {
                    Delivery.renderDeliveryTable(e.target.value);
                });
                
                // تسجيل الخروج
                document.getElementById('logoutBtn').addEventListener('click', (e) => {
                    e.preventDefault();
                    if (confirm('هل تريد تسجيل الخروج؟')) {
                        localStorage.removeItem('isLoggedIn');
                        localStorage.removeItem('currentUser');
                        window.location.href = 'Lougout.php';
                    }
                });
            }
        };
        
        // ========== INITIALIZATION ==========
        async function initialize() {
            console.log('بدء تهيئة لوحة الإدارة...');
            
            try {
                // إعداد الأحداث
                EventHandlers.setupImageUpload();
                EventHandlers.setupDiscountEvents();
                EventHandlers.setupNavigation();
                
                // تحميل البيانات الأولية
                await Products.loadProducts();
                
                UI.showNotification('تم تحميل لوحة الإدارة بنجاح', 'success');
                console.log('لوحة إدارة المنتجات والتوصيل جاهزة!');
                
            } catch (error) {
                console.error('خطأ في التهيئة:', error);
                UI.showNotification('حدث خطأ في تحميل البيانات', 'error');
            }
        }
        
        // تشغيل التهيئة عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', initialize);
