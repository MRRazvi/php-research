<?php

use League\Csv\Writer;
use Symfony\Component\HttpClient\HttpClient;

require_once __DIR__ . '/../init.php';

$projects = [];

$language = 'php';

$client = HttpClient::create();

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
    'name' => $repo['full_name'],
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

$csv = Writer::createFromPath(
  sprintf(
    '%s/top-100-projects-%s.csv',
    __DIR__ . '/../../docs',
    $language
  ),
  'w+'
);

$csv->insertOne([
  'ID',
  'Name',
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
