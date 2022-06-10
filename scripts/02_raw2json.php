<?php
require_once __DIR__ . '/vendor/autoload.php';
$rawPath = dirname(__DIR__) . '/raw';
$docsPath = dirname(__DIR__) . '/docs/json/city_population';

foreach (glob($rawPath . '/各鄉鎮市區戶數及人口數統計表/*/*.ods') as $odsFile) {
    $parts = explode('/', $odsFile);
    $fileName = str_replace('.ods', '.json', array_pop($parts));
    $filePath = $docsPath . '/' . array_pop($parts);
    if (!file_exists($filePath)) {
        mkdir($filePath, 0777, true);
    }
    $targetFile = $filePath . '/' . $fileName;
    if (file_exists($targetFile)) {
        continue;
    }
    $json = [];
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($odsFile);
    $count = $spreadsheet->getSheetCount();
    for ($i = 1; $i < $count; $i++) {
        $sheet = $spreadsheet->getSheet($i);
        $county = $sheet->getCell('A4');
        $county = str_replace(['※', ' ', '　'], '', $county);
        $rows = $sheet->getHighestRow() + 1;
        for ($j = 5; $j < $rows; $j++) {
            $city = (string)$sheet->getCell('A' . $j)->getValue();
            $city = str_replace(['※', ' ', '　'], '', $city);
            $male = $sheet->getCell('D' . $j)->getValue();
            $female = $sheet->getCell('E' . $j)->getValue();
            $data = [
                'county' => $county,
                'city' => $city,
                'households' => $sheet->getCell('B' . $j)->getValue(),
                'population' => $male + $female,
                'male' => $male,
                'female' => $female,
            ];
            if (!empty($data['households'])) {
                $json[] = $data;
            }
        }
    }
    file_put_contents($filePath . '/' . $fileName, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
