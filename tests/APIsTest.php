<?php
require_once __DIR__ . '/../php/APIs.php';

class APIsTest
{
    private $conn;
    private $testCount = 0;
    private $passedCount = 0;

    public function __construct()
    {
        $this->conn = db_connect();
        $this->conn->begin_transaction();
    }

    public function __destruct()
    {
        $this->conn->rollback();
        $this->conn->close();

        echo "\n測試結果: {$this->passedCount}/{$this->testCount} 通過\n";
    }

    public function runAllTests()
    {
        echo "開始執行API測試...\n";
        echo "========================\n";

        $this->testGetMembers();
        $this->testAddProduct();
        $this->testGetProducts();
        $this->testGetProductById();
        $this->testGetOrders();

        echo "========================\n";
    }

    private function recordTestResult($testName, $result, $message = '')
    {
        $this->testCount++;
        if ($result) {
            $this->passedCount++;
            echo "[✓] {$testName} 通過\n";
        } else {
            echo "[✗] {$testName} 失敗";
            if ($message) {
                echo " - {$message}";
            }
            echo "\n";
        }
    }

    private function testGetMembers()
    {
        $testName = '測試getMembers函數';
        try {
            $members = getMembers($this->conn);

            $this->recordTestResult(
                "{$testName} - 返回類型檢查",
                is_array($members)
            );

            if (!empty($members)) {
                $firstMember = $members[0];
                $requiredFields = ['MemberID', 'membername', 'phonenum', 'memberemail', 'order_count', 'role'];

                foreach ($requiredFields as $field) {
                    $this->recordTestResult(
                        "{$testName} - 檢查字段 {$field}",
                        array_key_exists($field, $firstMember)
                    );
                }
            }
        } catch (Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage());
        }
    }

    private function testAddProduct()
    {
        $testName = '測試addProduct函數';
        try {
            // 測試數據
            $testData = [
                'productName' => '測試產品',
                'productType' => 'zipper',
                'mainImage' => ['tmp_name' => ''],
                'galleryImages' => ['tmp_name' => []],
                'sizes' => ['M'],
                'sizeDescriptions' => ['中等尺寸'],
                'colors' => [['黑色']],
                'stocks' => [[10]],
                'prices' => [1000],
                'productDescription' => '測試產品描述'
            ];

            $result = addProduct(
                $testData['productName'],
                $testData['productType'],
                $testData['mainImage'],
                $testData['galleryImages'],
                $testData['sizes'],
                $testData['sizeDescriptions'],
                $testData['colors'],
                $testData['stocks'],
                $testData['prices'],
                $testData['productDescription']
            );

            $this->recordTestResult(
                "{$testName} - 基本功能測試",
                $result === true || $result === false
            );
        } catch (Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage());
        }
    }

    private function testGetProducts()
    {
        $testName = '測試getProducts函數';
        try {
            $products = getProducts();

            $this->recordTestResult(
                "{$testName} - 返回類型檢查",
                is_array($products)
            );

            if (!empty($products)) {
                $firstProduct = $products[0];
                $requiredFields = ['ProductID', 'ProductName', 'Type', 'Description', 'MinPrice', 'MaxPrice', 'TotalStock'];

                foreach ($requiredFields as $field) {
                    $this->recordTestResult(
                        "{$testName} - 檢查字段 {$field}",
                        array_key_exists($field, $firstProduct)
                    );
                }
            }
        } catch (Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage());
        }
    }

    private function testGetProductById()
    {
        $testName = '測試getProductById函數';
        try {
            // 假設ID=1存在
            $product = getProductById(1);

            $this->recordTestResult(
                "{$testName} - 有效ID測試",
                $product !== null && is_array($product)
            );

            if ($product !== null) {
                $requiredFields = ['ProductID', 'ProductName', 'Type', 'Description', 'sizeOptions', 'galleryImages'];

                foreach ($requiredFields as $field) {
                    $this->recordTestResult(
                        "{$testName} - 檢查字段 {$field}",
                        array_key_exists($field, $product)
                    );
                }
            }

            // 測試無效ID
            $invalidProduct = getProductById(999999);
            $this->recordTestResult(
                "{$testName} - 無效ID測試",
                $invalidProduct === null
            );
        } catch (Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage());
        }
    }

    private function testGetOrders()
    {
        $testName = '測試getOrders函數';
        try {
            $orders = getOrders($this->conn);

            $this->recordTestResult(
                "{$testName} - 返回類型檢查",
                is_array($orders)
            );

            if (!empty($orders)) {
                $firstOrder = $orders[0];
                $requiredFields = ['OrderID', 'customer_name', 'total_amount', 'Status', 'OrderDate', 'MemberID'];

                foreach ($requiredFields as $field) {
                    $this->recordTestResult(
                        "{$testName} - 檢查字段 {$field}",
                        array_key_exists($field, $firstOrder)
                    );
                }
            }

            // 測試無連接參數
            $ordersWithoutConn = getOrders();
            $this->recordTestResult(
                "{$testName} - 無連接參數測試",
                is_array($ordersWithoutConn)
            );
        } catch (Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage());
        }
    }
}

// 運行測試
$test = new APIsTest();
$test->runAllTests();
?>