<?php
// products.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// السماح بجميع الملفات الأصلي (للتطوير فقط)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// إضافة هذه السطور في البداية
error_reporting(E_ALL);
ini_set('display_errors', 1);

// استيراد إعدادات قاعدة البيانات من ملف منفصل
require_once 'db/config.php';

// إنشاء اتصال PDO
try {
    $pdo = new PDO(
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
    error_log("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في الاتصال بقاعدة البيانات. الرجاء المحاولة مرة أخرى لاحقاً.'
    ]);
    exit;
}

// الحصول على الإجراء المطلوب
$action = isset($_GET['action']) ? $_GET['action'] : '';

// معالجة الطلبات
switch ($action) {
    case 'getAll':
        getAllProducts($pdo);
        break;
    case 'get':
        getProduct($pdo);
        break;
    case 'create':
        createProduct($pdo);
        break;
    case 'update':
        updateProduct($pdo);
        break;
    case 'delete':
        deleteProduct($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
        break;
}

// لا نحتاج لإغلاق الاتصال يدوياً في PDO، لكن يمكننا تعيينه لـ null
$pdo = null;

// الدوال المساعدة
function getAllProducts($pdo) {
    try {
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        $products = $stmt->fetchAll();
        
        // معالجة البيانات
        foreach ($products as &$product) {
            $product['id'] = (int)$product['id'];
            $product['price'] = floatval($product['price']);
            $product['stock'] = (int)$product['stock'];
            $product['has_discount'] = (bool)$product['has_discount'];
            $product['discount_percentage'] = floatval($product['discount_percentage']);
        }
        
        echo json_encode($products);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'خطأ في جلب المنتجات: ' . $e->getMessage()]);
    }
}

function getProduct($pdo) {
    try {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'معرف غير صالح']);
            return;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            $product = $stmt->fetch();
            
            // تحويل القيم المنطقية
            $product['has_discount'] = (bool)$product['has_discount'];
            $product['discount_percentage'] = floatval($product['discount_percentage']);
            $product['id'] = (int)$product['id'];
            $product['price'] = floatval($product['price']);
            $product['stock'] = (int)$product['stock'];
            
            echo json_encode($product);
        } else {
            echo json_encode(['success' => false, 'message' => 'المنتج غير موجود']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'خطأ في جلب المنتج: ' . $e->getMessage()]);
    }
}

function createProduct($pdo) {
    try {
        // قراءة بيانات POST كـ JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
            return;
        }
        
        // الحصول على البيانات الأساسية
        $name = isset($data['name']) ? $data['name'] : '';
        $category = isset($data['category']) ? $data['category'] : 'أخرى';
        $price = isset($data['price']) ? floatval($data['price']) : 0.00;
        $stock = isset($data['stock']) ? intval($data['stock']) : 0;
        $description = isset($data['description']) ? $data['description'] : '';
        $image_url = isset($data['image_url']) ? $data['image_url'] : null;
        
        // تحقق من حجم الصورة (اختياري)
        if ($image_url && strlen($image_url) > 5 * 1024 * 1024) { // 5MB
            echo json_encode(['success' => false, 'message' => 'حجم الصورة كبير جداً (الحد الأقصى 5MB)']);
            return;
        }
        
        // بيانات التخفيض
        $has_discount = isset($data['has_discount']) ? (int)$data['has_discount'] : 0;
        $discount_percentage = isset($data['discount_percentage']) ? floatval($data['discount_percentage']) : 0.0;
        $discount_start_date = isset($data['discount_start_date']) && !empty($data['discount_start_date']) ? 
                               $data['discount_start_date'] : null;
        $discount_end_date = isset($data['discount_end_date']) && !empty($data['discount_end_date']) ? 
                             $data['discount_end_date'] : null;
        
        // استخدام prepared statement لمنع SQL injection
        $sql = "INSERT INTO products (name, category, price, stock, description, image_url, 
                has_discount, discount_percentage, discount_start_date, discount_end_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $name, 
            $category, 
            $price, 
            $stock, 
            $description, 
            $image_url,
            $has_discount,
            $discount_percentage,
            $discount_start_date,
            $discount_end_date
        ]);
        
        $product_id = $pdo->lastInsertId();
        
        // جلب المنتج المضافة لعرضه
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $new_product = $stmt->fetch();
        
        // تحويل القيم
        $new_product['has_discount'] = (bool)$new_product['has_discount'];
        $new_product['discount_percentage'] = floatval($new_product['discount_percentage']);
        $new_product['id'] = (int)$new_product['id'];
        $new_product['price'] = floatval($new_product['price']);
        $new_product['stock'] = (int)$new_product['stock'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'تم إضافة المنتج بنجاح',
            'id' => $product_id,
            'product' => $new_product
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'خطأ في إضافة المنتج: ' . $e->getMessage()
        ]);
    }
}

function updateProduct($pdo) {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
            return;
        }
        
        $id = intval($data['id']);
        
        // الحصول على البيانات
        $name = isset($data['name']) ? $data['name'] : '';
        $category = isset($data['category']) ? $data['category'] : 'أخرى';
        $price = isset($data['price']) ? floatval($data['price']) : 0.00;
        $stock = isset($data['stock']) ? intval($data['stock']) : 0;
        $description = isset($data['description']) ? $data['description'] : '';
        $image_url = isset($data['image_url']) ? $data['image_url'] : null;
        
        // تحقق من حجم الصورة (اختياري)
        if ($image_url && strlen($image_url) > 5 * 1024 * 1024) { // 5MB
            echo json_encode(['success' => false, 'message' => 'حجم الصورة كبير جداً (الحد الأقصى 5MB)']);
            return;
        }
        
        // بيانات التخفيض
        $has_discount = isset($data['has_discount']) ? (int)$data['has_discount'] : 0;
        $discount_percentage = isset($data['discount_percentage']) ? floatval($data['discount_percentage']) : 0.0;
        $discount_start_date = isset($data['discount_start_date']) && !empty($data['discount_start_date']) ? 
                               $data['discount_start_date'] : null;
        $discount_end_date = isset($data['discount_end_date']) && !empty($data['discount_end_date']) ? 
                             $data['discount_end_date'] : null;
        
        // استخدام prepared statement
        $sql = "UPDATE products SET 
                name = ?, 
                category = ?, 
                price = ?, 
                stock = ?, 
                description = ?, 
                image_url = ?,
                has_discount = ?,
                discount_percentage = ?,
                discount_start_date = ?,
                discount_end_date = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $name, 
            $category, 
            $price, 
            $stock, 
            $description, 
            $image_url,
            $has_discount,
            $discount_percentage,
            $discount_start_date,
            $discount_end_date,
            $id
        ]);
        
        if ($stmt->rowCount() > 0) {
            // جلب المنتج المحدث
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $updated_product = $stmt->fetch();
            
            // تحويل القيم
            $updated_product['has_discount'] = (bool)$updated_product['has_discount'];
            $updated_product['discount_percentage'] = floatval($updated_product['discount_percentage']);
            $updated_product['id'] = (int)$updated_product['id'];
            $updated_product['price'] = floatval($updated_product['price']);
            $updated_product['stock'] = (int)$updated_product['stock'];
            
            echo json_encode([
                'success' => true, 
                'message' => 'تم تحديث المنتج بنجاح',
                'product' => $updated_product
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'لم يتم تحديث المنتج أو المنتج غير موجود'
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'خطأ في تحديث المنتج: ' . $e->getMessage()
        ]);
    }
}

function deleteProduct($pdo) {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
            return;
        }
        
        $id = intval($data['id']);
        
        // استخدام prepared statement
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'تم حذف المنتج بنجاح'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'المنتج غير موجود'
            ]);
        }
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'خطأ في حذف المنتج: ' . $e->getMessage()
        ]);
    }
}
?>