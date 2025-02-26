<?php

use League\Csv\Reader;
use Symfony\Component\Process\Process;

require_once __DIR__ . '/../init.php';

$csv = Reader::createFromPath(__DIR__ . '/../../docs/top-100-projects/php.csv', 'r');
$csv->setHeaderOffset(0);

$records = $csv->getRecords();

foreach ($records as $record) {
  $name = $record['Name'];
  $url = $record['Repo URL'];

  try {
    $process = new Process([
      'git',
      'clone',
      $url,
      sprintf('%s/../../repos/%s', __DIR__, $name)
    ]);

    $process->setTimeout(0);
    $process->run();

    dump($name);
  } catch (Exception $e) {
    dump($e->getMessage());
  }
}
