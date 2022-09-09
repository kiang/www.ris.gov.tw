<?php
require_once __DIR__ . '/vendor/autoload.php';
$rawPath = dirname(__DIR__) . '/raw';
$docsPath = dirname(__DIR__) . '/docs/csv/birth_death';
if (!file_exists($docsPath)) {
    mkdir($docsPath, 0777, true);
}
$bdCityPath = dirname(__DIR__) . '/docs/csv/birth_death_city';
if (!file_exists($bdCityPath)) {
    mkdir($bdCityPath, 0777, true);
}

$poolDeath = $poolBirth = [];
$pickDeath = $pickBirth = [];

foreach (glob($bdCityPath . '/*.csv') as $csvFile) {
    unlink($csvFile);
}
for ($y = 2008; $y <= 2022; $y++) {
    for ($m = 1; $m <= 12; $m++) {
        $odsFile = "{$rawPath}/各縣市人口總增加出生死亡結婚離婚數及其比率/{$y}/{$m}.ods";
        if (!isset($poolDeath[$y])) {
            $poolDeath[$y] = [];
            $poolBirth[$y] = [];
            $pickDeath[$y] = [];
            $pickBirth[$y] = [];
        }
        if (!file_exists($odsFile)) {
            $poolDeath[$y][$m] = '';
            $poolBirth[$y][$m] = '';
            $pickDeath[$y][$m] = '';
            $pickBirth[$y][$m] = '';
            continue;
        }
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($odsFile);
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->getHighestRow() + 1;
        $max = 0;
        for ($i = 1; $i <= $rows; $i++) {
            $death = $sheet->getCell('K' . $i)->getCalculatedValue();
            if (intval($death) > $max) {
                $max = $death;
            }
            if (!empty($death)) {
                $city = $sheet->getCell('A' . $i)->getCalculatedValue();
                $city = str_replace([' ', '巿'], ['', '市'], $city);
                if (!empty($city) && $city !== '區域別') {
                    $cityFile = $bdCityPath . '/' . $city . '.csv';
                    if (!file_exists($cityFile)) {
                        $oFh = fopen($cityFile, 'w');
                        fputcsv($oFh, ['y/m', '月增加人口數', '月增加率', '折合年增加率', '自然增加人口數', '自然增加月增加率', '自然增加折合年增加率', '出生數', '月出生率', '折合年出生率', '死亡數', '月死亡率', '折合年死亡率']);
                    } else {
                        $oFh = fopen($cityFile, 'a');
                    }
                    $cityLine = [$y . '/' . $m];
                    for ($j = 66; $j <= 77; $j++) {
                        $c = chr($j);
                        $cityLine[] = round($sheet->getCell($c . $i)->getCalculatedValue(), 2);
                    }
                    fputcsv($oFh, $cityLine);
                    fclose($oFh);
                }
            }
        }
        if (!isset($poolDeath[$y][$m])) {
            $pickDeath[$y][$m] = $max;
            if ($m > 1) {
                $n = $m - 1;
                $poolDeath[$y][$m] = $max + $poolDeath[$y][$n];
            } else {
                $poolDeath[$y][$m] = $max;
            }
        }

        $max = 0;
        for ($i = 1; $i <= $rows; $i++) {
            $birth = $sheet->getCell('H' . $i)->getCalculatedValue();
            if (intval($birth) > $max) {
                $max = $birth;
            }
        }
        if (!isset($poolBirth[$y])) {
            $poolBirth[$y] = [];
        }
        if (!isset($poolBirth[$y][$m])) {
            $pickBirth[$y][$m] = $max;
            if ($m > 1) {
                $n = $m - 1;
                $poolBirth[$y][$m] = $max + $poolBirth[$y][$n];
            } else {
                $poolBirth[$y][$m] = $max;
            }
        }
    }
}

$fh = fopen($docsPath . '/death_sum.csv', 'w');
fputcsv($fh, array_merge(['year'], range(1, 12)));
foreach ($poolDeath as $y => $lv1) {
    fputcsv($fh, array_merge([$y], $lv1));
}

$fh = fopen($docsPath . '/birth_sum.csv', 'w');
fputcsv($fh, array_merge(['year'], range(1, 12)));
foreach ($poolBirth as $y => $lv1) {
    fputcsv($fh, array_merge([$y], $lv1));
}

$fh = fopen($docsPath . '/death.csv', 'w');
fputcsv($fh, array_merge(['year'], range(1, 12)));
foreach ($pickDeath as $y => $lv1) {
    fputcsv($fh, array_merge([$y], $lv1));
}

$fh = fopen($docsPath . '/birth.csv', 'w');
fputcsv($fh, array_merge(['year'], range(1, 12)));
foreach ($pickBirth as $y => $lv1) {
    fputcsv($fh, array_merge([$y], $lv1));
}
