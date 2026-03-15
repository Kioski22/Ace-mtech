<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$targets = [
    ['label' => 'localhost:25', 'host' => 'localhost', 'port' => 25, 'transport' => 'tcp'],
    ['label' => 'GoDaddy SMTP SSL 465', 'host' => 'smtpout.secureserver.net', 'port' => 465, 'transport' => 'ssl'],
    ['label' => 'GoDaddy SMTP STARTTLS 587', 'host' => 'smtpout.secureserver.net', 'port' => 587, 'transport' => 'tcp'],
];

foreach ($targets as $target) {
    $prefix = $target['transport'] === 'ssl' ? 'ssl://' : '';
    $endpoint = $prefix . $target['host'];
    $start = microtime(true);
    $connection = @fsockopen($endpoint, $target['port'], $errno, $errstr, 10);
    $duration = round(microtime(true) - $start, 2);

    echo $target['label'] . PHP_EOL;
    if ($connection === false) {
        echo 'FAILED: ' . $errstr . ' (' . $errno . ')' . PHP_EOL;
        echo 'Elapsed: ' . $duration . 's' . PHP_EOL . PHP_EOL;
        continue;
    }

    echo 'CONNECTED' . PHP_EOL;
    echo 'Elapsed: ' . $duration . 's' . PHP_EOL;

    stream_set_timeout($connection, 5);
    $banner = fgets($connection, 512);
    if (is_string($banner) && trim($banner) !== '') {
        echo 'Banner: ' . trim($banner) . PHP_EOL;
    }

    fclose($connection);
    echo PHP_EOL;
}