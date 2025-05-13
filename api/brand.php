<?php
require_once __DIR__ . '/error_handler.php';
class BrandModel
{
    public static function getBrandInfo()
    {
        require_once __DIR__ . '/db.php';

        // $db = new DB();
        $conn = getDBConnection();

        // 獲取公司介紹
        $companyIntro = [];
        $stmt = $conn->query("SELECT introdution FROM brand_introduction");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $companyIntro[] = $row['introdution'];
        }

        // 獲取品牌資料
        $brands = [];
        $stmt = $conn->query("SELECT ab.brand_id, ab.brand_content, bi.introdution, ab.brand_name 
                              FROM agent_brand ab 
                              JOIN brand_introduction bi ON ab.brand_introduction_id = bi.brand_introduction_id");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $brandId = $row['brand_id'];
            $brands[$brandId] = [
                'name' => $row['brand_name'],
                'content' => $row['brand_content'],
                'description' => explode('\n', $row['introdution']),
                'logos' => [],
                'images' => []
            ];

            // 獲取品牌logo (路徑格式為brand/$brand_id/logo-*.png或.jpg)
            $logoDir = "imgs/brand/{$brandId}/";
            if (is_dir(__DIR__ . "/../$logoDir")) {
                $logos = glob(__DIR__ . "/../$logoDir" . "logo-*.{png,jpg,jpeg}", GLOB_BRACE);
                foreach ($logos as $logo) {
                    $brands[$brandId]['logos'][] = basename($logo);
                }
            }

            // 獲取品牌圖片 (路徑格式為brand/$brand_id/newAbout/newabout-*.jpg)
            $newAboutDir = "imgs/brand/{$brandId}/newAbout/";
            if (is_dir(__DIR__ . "/../$newAboutDir")) {
                $images = glob(__DIR__ . "/../$newAboutDir" . "newabout-*.jpg", GLOB_BRACE);
                foreach ($images as $image) {
                    $brands[$brandId]['images'][] = "brand/{$brandId}/newAbout/" . basename($image);
                }
            }
        }

        return [
            'company_intro' => $companyIntro,
            'brands' => $brands
        ];
    }
}
?>