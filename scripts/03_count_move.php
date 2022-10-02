<?php
require_once __DIR__ . '/vendor/autoload.php';
$rawPath = dirname(__DIR__) . '/raw';
$moveCityPath = dirname(__DIR__) . '/docs/csv/move_city';
if (!file_exists($moveCityPath)) {
    mkdir($moveCityPath, 0777, true);
}

$poolMoveOut = $poolMoveIn = [];

foreach (glob($moveCityPath . '/*.csv') as $csvFile) {
    unlink($csvFile);
}
for ($y = 2018; $y <= 2022; $y++) {
    for ($m = 1; $m <= 12; $m++) {
        $odsFile = "{$rawPath}/各縣市遷入、遷出及淨遷徙人數按性別分/{$y}/{$m}.ods";
        if (!file_exists($odsFile)) {
            continue;
        }
        echo "{$odsFile}\n";
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($odsFile);
        $sheetCount = $spreadsheet->getSheetCount();
        for ($i = 0; $i < $sheetCount; $i++) {
            $sheet = $spreadsheet->getSheet($i);
            $rows = $sheet->getHighestRow() + 1;
            if ($rows > 2) {
                for ($j = 7; $j <= $rows; $j += 3) {
                    $inSum = $sheet->getCell('C' . $j)->getValue();
                    $outSum = $sheet->getCell('R' . $j)->getValue();
                    $j++;
                    $label = $sheet->getCell('A' . $j)->getValue();
                    $inMale = $sheet->getCell('C' . $j)->getValue();
                    $outMale = $sheet->getCell('R' . $j)->getValue();
                    $j++;
                    $inFemale = $sheet->getCell('C' . $j)->getValue();
                    $outFemale = $sheet->getCell('R' . $j)->getValue();
                    $label = str_replace([' ', '　'], ['', ''], $label);

                    $oFile = $moveCityPath . '/' . $label . '.csv';
                    if (!file_exists($oFile)) {
                        $oFh = fopen($oFile, 'w');
                        fputcsv($oFh, ['ym', 'inTotal', 'inMale', 'inFemale', 'outTotal', 'outMale', 'outFemale']);
                    } else {
                        $oFh = fopen($oFile, 'a');
                    }
                    fputcsv($oFh, [$y . str_pad($m, 2, '0', STR_PAD_LEFT), $inSum, $inMale, $inFemale, $outSum, $outMale, $outFemale]);
                    fclose($oFh);
                }
            }
        }
    }
}
