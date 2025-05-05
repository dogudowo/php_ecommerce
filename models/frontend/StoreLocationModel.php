<?php
class StoreLocationModel
{
    public static function getStoreLocations()
    {
        return [
            [
                'name' => '台中旗艦店',
                'service_time' => [
                    '週一至週五： 09:00 ~ 16:00',
                    '週六： 09:00 ~ 17:00'
                ],
                'service_notice' => [
                    '非營業時間如有購箱需求，請另行致電預約。',
                    '每週日公休，國定例假日服務時間請參考FB公告。'
                ],
                'phone' => '(04) 2291-4226',
                'address' => '台中市北屯區敦化路一段565號 (敦化公園對面)',
                'map_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d765.1252954715466!2d120.66593454017718!3d24.186816428003123!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjTCsDExJzEyLjMiTiAxMjDCsDM5JzU5LjUiRQ!5e0!3m2!1szh-TW!2stw!4v1551863739774'
            ],
            [
                'name' => '半山夢工廠',
                'service_time' => [
                    '週日、週一、週三、週四、週五、週六： 10:00 ~ 18:00'
                ],
                'service_notice' => [
                    '每週二公休'
                ],
                'phone' => '0939-653-911',
                'address' => '南投縣南投市工業南六路6號8F',
                'map_url' => 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14588.895102050474!2d120.6567184!3d23.9171288!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3469317b62b1e2db%3A0xd4e123753d66b931!2z5Y2K5bGx5aSi5bel5bugIHwg5qi55b635pS257SN5a245peF5bel5aC0IEJhYmJ1emEgRHJlYW1mYWN0b3J5!5e0!3m2!1szh-TW!2stw!4v1675772631997!5m2!1szh-TW!2stw'
            ],
            [
                'name' => '歐印亞洲維修中心',
                'service_time' => [
                    '週一至週五：09:00~17:30'
                ],
                'service_notice' => [
                    '每週六、日公休'
                ],
                'phone' => '(04) 2336-9926 、 0935-793-911',
                'address' => '',
                'map_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3641.6141065391134!2d120.5866875!3d24.115062500000004!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2s7QP24H8P%2B2M!5e0!3m2!1szh-TW!2stw!4v1562910053227!5m2!1szh-TW!2stw'
            ],
            [
                'name' => '新竹大園百',
                'service_time' => [
                    '周一到周日：11:00~22:00'
                ],
                'service_notice' => [
                    '每週六、日公休'
                ],
                'phone' => '',
                'address' => '新竹市東區西大路323號 (7樓)',
                'map_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3621.8044103805896!2d120.96511199999999!3d24.802150100000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x346835eaceb0d369%3A0x646b553124f08434!2zNywgTm8uIDMyM-iZn-ilv-Wkp-i3r-adseWNgOaWsOerueW4gjMwMA!5e0!3m2!1szh-TW!2stw!4v1688868950557!5m2!1szh-TW!2stw'
            ]
        ];
    }
}
?>