<?php
$reports = [
    's000' => '戶籍人口統計速報',
    'm0s1' => '各縣市戶數人口數性別比例及人口密度統計表',
    'm0s2' => '各縣市人口總增加出生死亡結婚離婚數及其比率',
    'm0s3' => '各縣市嬰兒出生數按婚生、非婚生、無依兒童及生母原屬國籍分',
    'm0s4' => '各縣市結婚人數按雙方國籍分',
    'm0s5' => '各縣市離婚人數按雙方國籍分',
    'm0s6' => '各縣市遷入、遷出及淨遷徙人數按性別分',
    'm0s7' => '各縣市人口數按性別及單一年齡分',
    'm0s8' => '各鄉鎮市區戶數及人口數統計表',
    'm0s9' => '各縣市人口年齡結構重要指標',
    'm0sa' => '各縣市戶籍登記統計表',
    'm0sb' => '各縣市外籍與大陸配偶人數',
];
$rawPath = dirname(__DIR__) . '/raw';

$theYear = date('Y') - 1911;
$theMonth = date('n');
for ($y = 97; $y <= $theYear; $y++) {
    for ($m = 1; $m <= 12; $m++) {
        foreach ($reports as $key => $report) {
            if ($y == $theYear && $m >= $theMonth) {
                continue;
            }
            $pathYear = $y + 1911;
            $reportPath = $rawPath . '/' . $report . '/' . $pathYear;
            if (!file_exists($reportPath)) {
                mkdir($reportPath, 0777, true);
            }
            $reportFile = $reportPath . '/' . $m . '.ods';
            if (!file_exists($reportFile)) {
                $ym = str_pad($y, 3, '0', STR_PAD_LEFT) . str_pad($m, 2, '0', STR_PAD_LEFT);
                $content = file_get_contents('https://www.ris.gov.tw/info-popudata/app/awFastDownload/file/' . $key . '-' . $ym . '.ods/' . $key . '/' . $ym . '/');
                if (strlen($content) > 500) {
                    file_put_contents($reportFile, $content);
                }
            }
        }
    }
}
