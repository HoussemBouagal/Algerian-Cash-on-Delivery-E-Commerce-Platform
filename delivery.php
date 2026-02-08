<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

// التعامل مع طلبات OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// استيراد إعدادات قاعدة البيانات من ملف منفصل
require_once 'db/config.php';

class DeliveryManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // جلب جميع الولايات
    public function getAll() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM delivery_prices ORDER BY CAST(wilaya_code AS UNSIGNED)");
            $wilayas = $stmt->fetchAll();
            
            // تحويل الأنواع الرقمية
            foreach ($wilayas as &$wilaya) {
                $wilaya['delivery_price'] = (float) $wilaya['delivery_price'];
                $wilaya['is_active'] = (bool) $wilaya['is_active'];
            }
            
            return [
                'success' => true,
                'data' => $wilayas,
                'count' => count($wilayas)
            ];
            
        } catch (PDOException $e) {
            error_log("Error in getAll: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في جلب البيانات'
            ];
        }
    }
    
    // جلب ولاية محددة
    public function get($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM delivery_prices WHERE id = ?");
            $stmt->execute([$id]);
            $wilaya = $stmt->fetch();
            
            if ($wilaya) {
                $wilaya['delivery_price'] = (float) $wilaya['delivery_price'];
                $wilaya['is_active'] = (bool) $wilaya['is_active'];
                
                return [
                    'success' => true,
                    'data' => $wilaya
                ];
            }
            
            return [
                'success' => false,
                'message' => 'الولاية غير موجودة'
            ];
            
        } catch (PDOException $e) {
            error_log("Error in get: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في جلب البيانات'
            ];
        }
    }
    
    // تحديث سعر ولاية
    public function update($id, $data) {
        try {
            $allowedFields = ['delivery_price', 'is_active', 'wilaya_name', 'wilaya_code'];
            $updates = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $updates[] = "$key = ?";
                    
                    // معالجة الأنواع الخاصة
                    if ($key === 'delivery_price') {
                        $params[] = (float) $value;
                    } elseif ($key === 'is_active') {
                        $params[] = $value ? 1 : 0;
                    } else {
                        $params[] = $value;
                    }
                }
            }
            
            if (empty($updates)) {
                return [
                    'success' => false, 
                    'message' => 'لا توجد بيانات لتحديثها'
                ];
            }
            
            $params[] = (int) $id;
            $query = "UPDATE delivery_prices SET " . implode(', ', $updates) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'تم تحديث بيانات الولاية بنجاح'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'لم يتم تحديث أي بيانات'
            ];
            
        } catch (PDOException $e) {
            error_log("Error in update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في تحديث البيانات'
            ];
        }
    }
    
    // تحديث جميع الأسعار دفعة واحدة
    public function saveAll($wilayasData) {
        try {
            $this->pdo->beginTransaction();
            
            foreach ($wilayasData as $wilaya) {
                if (!isset($wilaya['id']) || !isset($wilaya['price'])) {
                    continue;
                }
                
                $stmt = $this->pdo->prepare("UPDATE delivery_prices SET delivery_price = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([(float) $wilaya['price'], (int) $wilaya['id']]);
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'تم حفظ جميع أسعار التوصيل بنجاح'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error in saveAll: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في حفظ البيانات'
            ];
        }
    }
    
    // إضافة ولاية جديدة
    public function create($data) {
        try {
            // التحقق من البيانات المطلوبة
            $requiredFields = ['wilaya_code', 'wilaya_name', 'delivery_price'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "حقل $field مطلوب"
                    ];
                }
            }
            
            // التحقق من عدم تكرار رقم الولاية
            $checkStmt = $this->pdo->prepare("SELECT id FROM delivery_prices WHERE wilaya_code = ?");
            $checkStmt->execute([$data['wilaya_code']]);
            
            if ($checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'رقم الولاية موجود مسبقاً'
                ];
            }
            
            $stmt = $this->pdo->prepare("INSERT INTO delivery_prices (wilaya_code, wilaya_name, delivery_price, is_active) VALUES (?, ?, ?, ?)");
            
            $wilayaCode = $data['wilaya_code'];
            $wilayaName = $data['wilaya_name'];
            $deliveryPrice = (float) $data['delivery_price'];
            $isActive = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;
            
            $stmt->execute([$wilayaCode, $wilayaName, $deliveryPrice, $isActive]);
            
            return [
                'success' => true,
                'message' => 'تم إضافة الولاية بنجاح',
                'id' => $this->pdo->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            error_log("Error in create: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في إضافة الولاية'
            ];
        }
    }
    
    // حذف ولاية
    public function delete($id) {
        try {
            // التحقق من وجود الولاية
            $checkStmt = $this->pdo->prepare("SELECT id FROM delivery_prices WHERE id = ?");
            $checkStmt->execute([(int) $id]);
            
            if (!$checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'الولاية غير موجودة'
                ];
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM delivery_prices WHERE id = ?");
            $stmt->execute([(int) $id]);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'تم حذف الولاية بنجاح'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'لم يتم حذف الولاية'
            ];
            
        } catch (PDOException $e) {
            error_log("Error in delete: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في حذف الولاية'
            ];
        }
    }
    
    // البحث عن ولاية
    public function search($term) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM delivery_prices WHERE wilaya_name LIKE ? OR wilaya_code LIKE ? ORDER BY CAST(wilaya_code AS UNSIGNED)");
            $searchTerm = "%" . $term . "%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $wilayas = $stmt->fetchAll();
            
            // تحويل الأنواع الرقمية
            foreach ($wilayas as &$wilaya) {
                $wilaya['delivery_price'] = (float) $wilaya['delivery_price'];
                $wilaya['is_active'] = (bool) $wilaya['is_active'];
            }
            
            return [
                'success' => true,
                'data' => $wilayas,
                'count' => count($wilayas)
            ];
            
        } catch (PDOException $e) {
            error_log("Error in search: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في البحث'
            ];
        }
    }
    
    // جلب إحصائيات
    public function getStats() {
        try {
            $stmt = $this->pdo->query("SELECT 
                COUNT(*) as total_wilayas,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_wilayas,
                AVG(delivery_price) as average_price,
                MIN(delivery_price) as min_price,
                MAX(delivery_price) as max_price
            FROM delivery_prices");
            
            $stats = $stmt->fetch();
            
            // تحويل الأنواع الرقمية
            $stats['total_wilayas'] = (int) $stats['total_wilayas'];
            $stats['active_wilayas'] = (int) $stats['active_wilayas'];
            $stats['average_price'] = (float) $stats['average_price'];
            $stats['min_price'] = (float) $stats['min_price'];
            $stats['max_price'] = (float) $stats['max_price'];
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (PDOException $e) {
            error_log("Error in getStats: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في جلب الإحصائيات'
            ];
        }
    }
}

// التحقق من وجود ملف الإعدادات
if (!file_exists('db/config.php')) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'ملف الإعدادات غير موجود'
    ]);
    exit;
}

// إنشاء اتصال PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'خطأ في الاتصال بقاعدة البيانات'
    ]);
    exit;
}

// معالجة الطلبات
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// إنشاء كائن المدير
$deliveryManager = new DeliveryManager($pdo);

// الحصول على البيانات المرسلة
$inputData = json_decode(file_get_contents('php://input'), true);

// إذا لم يكن JSON، حاول الحصول من POST
if ($inputData === null && $method === 'POST') {
    $inputData = $_POST;
}

// معالجة الطلبات المختلفة
$response = [
    'success' => false,
    'message' => 'طلب غير معروف'
];

try {
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'getAll':
                    $response = $deliveryManager->getAll();
                    break;
                    
                case 'get':
                    $id = $_GET['id'] ?? 0;
                    $response = $deliveryManager->get($id);
                    break;
                    
                case 'search':
                    $term = $_GET['term'] ?? '';
                    $response = $deliveryManager->search($term);
                    break;
                    
                case 'stats':
                    $response = $deliveryManager->getStats();
                    break;
                    
                default:
                    $response = [
                        'success' => false,
                        'message' => 'إجراء غير معروف'
                    ];
                    break;
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'create':
                    $response = $deliveryManager->create($inputData);
                    break;
                    
                case 'update':
                    $id = $_GET['id'] ?? ($inputData['id'] ?? 0);
                    unset($inputData['id']); // إزالة id من البيانات ليتم تمريره كمعلمة منفصلة
                    $response = $deliveryManager->update($id, $inputData);
                    break;
                    
                case 'saveAll':
                    $response = $deliveryManager->saveAll($inputData);
                    break;
                    
                case 'delete':
                    $id = $_GET['id'] ?? ($inputData['id'] ?? 0);
                    $response = $deliveryManager->delete($id);
                    break;
                    
                default:
                    $response = [
                        'success' => false,
                        'message' => 'إجراء غير معروف'
                    ];
                    break;
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? 0;
            $response = $deliveryManager->delete($id);
            break;
            
        case 'PUT':
            $id = $_GET['id'] ?? ($inputData['id'] ?? 0);
            unset($inputData['id']);
            $response = $deliveryManager->update($id, $inputData);
            break;
            
        default:
            http_response_code(405);
            $response = [
                'success' => false,
                'message' => 'طريقة الطلب غير مدعومة'
            ];
            break;
    }
} catch (Exception $e) {
    error_log("Request processing error: " . $e->getMessage());
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'خطأ في معالجة الطلب'
    ];
}

// تعيين كود الاستجابة HTTP المناسب
if (!$response['success']) {
    http_response_code(isset($response['code']) ? $response['code'] : 400);
} else {
    http_response_code(200);
}

// إرجاع الاستجابة
echo json_encode($response, JSON_UNESCAPED_UNICODE);

?>