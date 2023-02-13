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

    $baseFile = '';
    switch ($p['filename']) {
        case '臺南市':
            $baseFile = $basePath . '/tmp/臺南縣.json';
            break;
        case '高雄市':
            $baseFile = $basePath . '/tmp/高雄縣.json';
            break;
        case '臺中市':
            $baseFile = $basePath . '/tmp/臺中縣.json';
            break;
        case '桃園市':
            $baseFile = $basePath . '/tmp/桃園縣.json';
            break;
        case '新北市':
            $baseFile = $basePath . '/tmp/臺北縣.json';
            break;
    }
    if (!empty($baseFile) && file_exists($baseFile)) {
        $base = json_decode(file_get_contents($baseFile), true);
        if (isset($base[1])) {
            $poolBirth = $base[0];
            $poolDeath = $base[1];
        }
    }

    $fh = fopen($csvFile, 'r');
    $head = fgetcsv($fh, 2048);
    while ($line = fgetcsv($fh, 2048)) {
        $data = array_combine($head, $line);
        $parts = explode('/', $data['y/m']);
        $key = $parts[0] . '/' . str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        if (isset($poolBirth[$key])) {
            $poolBirth[$key] += $data['出生數'];
            $poolDeath[$key] += $data['死亡數'];
        } else {
            $poolBirth[$key] = $data['出生數'];
            $poolDeath[$key] = $data['死亡數'];
        }
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

    file_put_contents($basePath . '/tmp/' . $p['filename'] . '.json', json_encode([$poolBirth, $poolDeath]));

    file_put_contents($basePath . '/tmp/chart.json', json_encode([
        'title' => $p['filename'] . '出生死亡曲線圖',
        'data' => $chart,
        'pngFilePath' => $pngPath . '/' . $p['filename'] . '.png',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    exec("/usr/bin/node {$basePath}/scripts/rawCharts.js");
}
