<?php
require_once __DIR__ . '/vendor/autoload.php';
$rawPath = dirname(__DIR__) . '/raw';
$docsPath = dirname(__DIR__) . '/docs/csv/birth_death';
if (!file_exists($docsPath)) {
    mkdir($docsPath, 0777, true);
}

$poolDeath = $poolBirth = [];

for ($y = 2008; $y <= 2022; $y++) {
    for ($m = 1; $m <= 12; $m++) {
        if ($y == 2022 && $m > 7) {
            continue;
        }
        $odsFile = "{$rawPath}/各縣市人口總增加出生死亡結婚離婚數及其比率/{$y}/{$m}.ods";
        if (!file_exists($odsFile)) {
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
        }
        if (!isset($poolDeath[$y])) {
            $poolDeath[$y] = [];
        }
        if (!isset($poolDeath[$y][$m])) {
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
            if ($m > 1) {
                $n = $m - 1;
                $poolBirth[$y][$m] = $max + $poolBirth[$y][$n];
            } else {
                $poolBirth[$y][$m] = $max;
            }
        }
    }
}

$fh = fopen($docsPath . '/death.csv', 'w');
fputcsv($fh, array_merge(['year'], range(1, 12)));
foreach ($poolDeath as $y => $lv1) {
    fputcsv($fh, array_merge([$y], $lv1));
}

$fh = fopen($docsPath . '/birth.csv', 'w');
fputcsv($fh, array_merge(['year'], range(1, 12)));
foreach ($poolBirth as $y => $lv1) {
    fputcsv($fh, array_merge([$y], $lv1));
}
