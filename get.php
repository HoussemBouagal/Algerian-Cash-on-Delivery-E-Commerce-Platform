<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

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
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Erreur de connexion à la base de données. Veuillez réessayer plus tard.'
    ]);
    exit;
}

// التحقق من وجود معلمة type وتنظيفها
$type = isset($_GET['type']) ? trim($_GET['type']) : '';

// توجيه الطلب بناءً على نوع البيانات المطلوبة
switch ($type) {
    case 'wilayas':
        getDeliveryPrices($pdo);
        break;
    case 'products':
        getProducts($pdo);
        break;
    case 'categories':
        getCategories($pdo);
        break;
    case '':
        getAllData($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'message' => 'Type de données non valide'
        ]);
        break;
}

/**
 * دالة لجلب أسعار التوصيل للولايات
 */
function getDeliveryPrices($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM delivery_prices WHERE is_active = 1 ORDER BY wilaya_code");
        $stmt->execute();
        $wilayas = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'wilayas' => $wilayas,
            'count' => count($wilayas)
        ]);
        
    } catch(PDOException $e) {
        error_log("Erreur getDeliveryPrices: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => 'خطأ في جلب بيانات الولايات'
        ]);
    }
}

/**
 * دالة لجلب المنتجات
 */
function getProducts($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE stock > 0 ORDER BY created_at DESC");
        $stmt->execute();
        $products = $stmt->fetchAll();
        
        // تحويل القيم الرقمية إلى الأرقام المناسبة
        foreach ($products as &$product) {
            if (isset($product['price'])) {
                $product['price'] = (float) $product['price'];
            }
            if (isset($product['stock'])) {
                $product['stock'] = (int) $product['stock'];
            }
        }
        
        echo json_encode([
            'success' => true,
            'products' => $products,
            'count' => count($products)
        ]);
        
    } catch(PDOException $e) {
        error_log("Erreur getProducts: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => 'خطأ في جلب المنتجات'
        ]);
    }
}

/**
 * دالة لجلب الفئات
 */
function getCategories($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        echo json_encode([
            'success' => true,
            'categories' => $categories,
            'count' => count($categories)
        ]);
        
    } catch(PDOException $e) {
        error_log("Erreur getCategories: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => 'خطأ في جلب الفئات'
        ]);
    }
}

/**
 * دالة لجلب جميع البيانات معاً
 */
function getAllData($pdo) {
    try {
        // جلب الولايات
        $stmt1 = $pdo->prepare("SELECT * FROM delivery_prices WHERE is_active = 1 ORDER BY wilaya_code");
        $stmt1->execute();
        $wilayas = $stmt1->fetchAll();
        
        // جلب المنتجات
        $stmt2 = $pdo->prepare("SELECT * FROM products WHERE stock > 0 ORDER BY created_at DESC");
        $stmt2->execute();
        $products = $stmt2->fetchAll();
        
        // تحويل القيم الرقمية للمنتجات
        foreach ($products as &$product) {
            if (isset($product['price'])) {
                $product['price'] = (float) $product['price'];
            }
            if (isset($product['stock'])) {
                $product['stock'] = (int) $product['stock'];
            }
        }
        
        // جلب الفئات
        $stmt3 = $pdo->prepare("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $stmt3->execute();
        $categories = $stmt3->fetchAll(PDO::FETCH_COLUMN, 0);
        
        echo json_encode([
            'success' => true,
            'wilayas' => $wilayas,
            'products' => $products,
            'categories' => $categories,
            'counts' => [
                'wilayas' => count($wilayas),
                'products' => count($products),
                'categories' => count($categories)
            ]
        ]);
        
    } catch(PDOException $e) {
        error_log("Erreur getAllData: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => 'خطأ في جلب البيانات'
        ]);
    }
}
?>