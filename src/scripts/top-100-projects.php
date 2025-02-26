<?php

use League\Csv\Writer;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Filesystem\Filesystem;

require_once __DIR__ . '/../init.php';

$client = HttpClient::create();
$filesystem = new Filesystem();

$languages = [
  'html',
  'css',
  'scss',
  'sass',
  'js',
  'typescript',
  'php',
  'blade',
  'sql',
  'markdown',
  'python',
  'java',
  'c++',
  'c',
  'c#',
  'go',
  'rust',
  'r',
  'ruby'
];

foreach ($languages as $language) {
  $projects = [];

  $file_path = sprintf('%s/top-100-projects/%s.csv', TH_DOCS_PATH, $language);
  if ($filesystem->exists($file_path)) {
    dump(sprintf('skip:%s', $language));
    continue;
  }

  $response = $client->request(
    'GET',
    'https://api.github.com/search/repositories',
    [
      'query' => [
        'q' => sprintf('language:%s', $language),
        'sort' => 'stars',
        'order' => 'desc',
        'per_page' => 100,
      ]
    ]
  );

  $data = $response->toArray();

  $repos = $data['items'];

  foreach ($repos as $repo) {
    $projects[] = [
      'id' => $repo['id'],
      'name' => $repo['name'],
      'full_name' => $repo['full_name'],
      'stars' => $repo['watchers'],
      'forks' => $repo['forks'],
      'size' => $repo['size'],
      'language' => $repo['language'],
      'branch:default' => $repo['default_branch'],
      'topics' => implode(', ', $repo['topics']),
      'issues:open' => $repo['open_issues'],
      'license' => $repo['license']['name'] ?? '',
      'url' => $repo['html_url'],
      'owner:url' => $repo['owner']['html_url'],
      'home' => $repo['homepage'],
      'created_at' => $repo['created_at'],
      'updated_at' => $repo['updated_at'],
    ];
  }

  $csv = Writer::createFromPath($file_path, 'w+');

  $csv->insertOne([
    'ID',
    'Name',
    'Full Name',
    'Stars',
    'Forks',
    'Size',
    'Programming Language',
    'Default Branch',
    'Topics',
    'Open Issues',
    'License',
    'Repo URL',
    'Owner',
    'Website',
    'Created At',
    'Updated At'
  ]);

  $csv->insertAll($projects);

  dump(sprintf('done:%s', $language));
}
