<?php
require_once __DIR__ . '/vendor/autoload.php';

$basePath = dirname(__DIR__);

$docsPath = $basePath . '/docs/csv/birth_death_city';

$pngPath = $basePath . '/docs/png/birth_death_city';
if (!file_exists($pngPath)) {
    mkdir($pngPath, 0777, true);
}

foreach (glob($docsPath . '/*.csv') as $csvFile) {
    $p = pathinfo($csvFile);
    if (false !== strpos($p['filename'], '區')) {
        continue;
    }
    $chart = [
        'labels' => [],
        'datasets' => [],
    ];
    $poolBirth = $poolDeath = [];
    $fh = fopen($csvFile, 'r');
    $head = fgetcsv($fh, 2048);
    while ($line = fgetcsv($fh, 2048)) {
        $data = array_combine($head, $line);
        $parts = explode('/', $data['y/m']);
        $key = $parts[0] . '/' . str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        $poolBirth[$key] = $data['出生數'];
        $poolDeath[$key] = $data['死亡數'];
    }
    $chart['labels'] = array_keys($poolBirth);
    $chart['datasets'][] = [
        'label' => '出生',
        'borderColor' => 'rgb(75, 192, 192)',
        'backgroundColor' => 'rgb(75, 192, 192)',
        'borderWidth' => 5,
        'data' => array_values($poolBirth),
    ];
    $chart['datasets'][] = [
        'label' => '死亡',
        'borderColor' => 'rgb(201, 0, 0)',
        'backgroundColor' => 'rgb(201, 0, 0)',
        'borderWidth' => 5,
        'data' => array_values($poolDeath),
    ];

    file_put_contents($basePath . '/tmp/chart.json', json_encode([
        'title' => $p['filename'] . '出生死亡曲線圖',
        'data' => $chart,
        'pngFilePath' => $pngPath . '/' . $p['filename'] . '.png',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    exec("/usr/bin/node {$basePath}/scripts/rawCharts.js");
}
