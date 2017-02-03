<?php

error_reporting(-1);
ini_set('display_errors', 'on');

use Adael\ProgressReporter\ProgressReporter;

require dirname(__DIR__) . "/src/ProgressReporter.php";

echo "Starting tasks..." . PHP_EOL;

for ($i = 1; $i <= 5; $i++) {

    $items = range(1, rand(1000, 3000));

    // Setups the reporter
    $report = new ProgressReporter(count($items), "Doing task $i");

    // reports each 100 iterations
    $report->timeout(250);

    foreach ($items as $item) {
        // Report!, that's it
        $report->report();
        usleep(rand(100, 2000));
    }

    // don't forget to call finish at the end, altough is not very important
    $report->finish();
}

echo "Process finished" . PHP_EOL;
