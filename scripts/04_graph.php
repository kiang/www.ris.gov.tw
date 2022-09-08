<?php
require_once __DIR__ . '/vendor/autoload.php';

$basePath = dirname(__DIR__);

$docsPath = dirname(__DIR__) . '/docs/csv/birth_death';

$pngPath = dirname(__DIR__) . '/docs/png/birth_death';
if (!file_exists($pngPath)) {
    mkdir($pngPath, 0777, true);
}

$fh = fopen($docsPath . '/birth.csv', 'r');
fgetcsv($fh, 2048);
$chart = [
    'labels' => [],
    'datasets' => [],
];
$pool = [];
while ($line = fgetcsv($fh, 2048)) {
    foreach ($line as $k => $v) {
        if (empty($v)) {
            continue;
        }
        if ($k > 0) {
            $pool[$line[0] . '/' . str_pad($k, 2, '0', STR_PAD_LEFT)] = $v;
        }
    }
}

$chart['labels'] = array_keys($pool);
$chart['datasets'][] = [
    'label' => '出生',
    'borderColor' => 'rgb(75, 192, 192)',
    'backgroundColor' => 'rgb(75, 192, 192)',
    'borderWidth' => 5,
    'data' => array_values($pool),
];

$fh = fopen($docsPath . '/death.csv', 'r');
$pool = [];
fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    foreach ($line as $k => $v) {
        if (empty($v)) {
            continue;
        }
        if ($k > 0) {
            $pool[$line[0] . '/' . str_pad($k, 2, '0', STR_PAD_LEFT)] = $v;
        }
    }
}
$chart['datasets'][] = [
    'label' => '死亡',
    'borderColor' => 'rgb(201, 0, 0)',
    'backgroundColor' => 'rgb(201, 0, 0)',
    'borderWidth' => 5,
    'data' => array_values($pool),
];

file_put_contents($pngPath . '/chart.json', json_encode($chart, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

exec("/usr/bin/node {$basePath}/scripts/rawCharts.js");
