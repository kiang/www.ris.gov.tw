<?php
require_once __DIR__ . '/vendor/autoload.php';
$bdCityPath = dirname(__DIR__) . '/docs/csv/birth_death_city';


foreach (glob($bdCityPath . '/*.csv') as $csvFile) {
    $p = pathinfo($csvFile);
    if (mb_substr($p['filename'], -1, 1, 'utf-8') !== '區') {
        $pool = [];
        $fh = fopen($csvFile, 'r');
        $head = fgetcsv($fh, 2048);
        $toBegin = false;
        $count = 0;
        while ($line = fgetcsv($fh, 2048)) {
            $data = array_combine($head, $line);
            if ($data['y/m'] === '2014/12') {
                $toBegin = true;
            }
            if ($toBegin) {
                $pool[$data['y/m']] = $data['自然增加人口數'];
                if ($data['自然增加人口數'] < 0) {
                    ++$count;
                }
            }
        }
        if (!isset($pool['2022/9'])) {
            continue;
        }
        echo $p['filename'] . ': ' . "{$count} \n";
    }
}
