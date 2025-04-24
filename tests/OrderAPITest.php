<?php
require_once __DIR__ . '/../php/APIs.php';

class OrderAPITest
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
        echo "開始執行訂單API測試...\n";
        echo "========================\n";

        $this->testGetOrdersStructure();
        $this->testGetOrdersWithInvalidConn();
        $this->testGetOrdersDataValidity();

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

    private function testGetOrdersStructure()
    {
        $testName = '測試getOrders返回結構';
        try {
            $orders = getOrders($this->conn);

            if (empty($orders)) {
                $this->recordTestResult($testName, false, '沒有獲取到訂單數據');
                return;
            }

            $firstOrder = $orders[0];
            $requiredFields = ['OrderID', 'customer_name', 'total_amount', 'Status', 'OrderDate', 'MemberID'];

            foreach ($requiredFields as $field) {
                $this->recordTestResult(
                    "{$testName} - 檢查字段 {$field}",
                    array_key_exists($field, $firstOrder)
                );
            }
        } catch (Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage());
        }
    }

    private function testGetOrdersWithInvalidConn()
    {
        $testName = '測試getOrders無效連接';
        try {
            $invalidConn = null;
            $orders = getOrders($invalidConn);

            $this->recordTestResult(
                $testName,
                is_array($orders),
                '應能處理無效連接參數'
            );
        } catch (Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage());
        }
    }

    private function testGetOrdersDataValidity()
    {
        $testName = '測試getOrders數據有效性';
        try {
            $orders = getOrders($this->conn);

            if (empty($orders)) {
                $this->recordTestResult($testName, false, '沒有獲取到訂單數據');
                return;
            }

            $firstOrder = $orders[0];

            // 修改後的OrderID有效性測試
            $this->recordTestResult(
                "{$testName} - OrderID有效性",
                isset($firstOrder['OrderID']) &&
                (is_int($firstOrder['OrderID']) || ctype_digit($firstOrder['OrderID'])) &&
                $firstOrder['OrderID'] > 0
            );

            // 測試total_amount是否為數字且大於0
            $this->recordTestResult(
                "{$testName} - total_amount有效性",
                is_numeric($firstOrder['total_amount']) && $firstOrder['total_amount'] > 0
            );

            // 測試OrderDate是否為有效日期格式
            $this->recordTestResult(
                "{$testName} - OrderDate有效性",
                (bool) strtotime($firstOrder['OrderDate'])
            );
        } catch (Exception $e) {
            $this->recordTestResult($testName, false, $e->getMessage());
        }
    }
}

// 運行測試
$test = new OrderAPITest();
$test->runAllTests();
?>