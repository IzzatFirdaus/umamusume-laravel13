<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$dict = App\Services\SupportCardEffects::dictionary();
foreach($dict as $id => $e) {
    if(stripos($e['name'] ?? '', 'friendship') !== false || stripos($e['name'] ?? '', 'gauge') !== false || stripos($e['name'] ?? '', 'initial') !== false) {
        echo $id.': '.$e['name']."\n";
    }
}