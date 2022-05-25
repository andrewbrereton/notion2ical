<?php

declare(strict_types=1);

namespace App;

require_once('vendor/autoload.php');


use App\Models\PageCollection;
use App\Normalizers\PageNormalizer;
use Dotenv\Dotenv;
use GuzzleHttp\Client;


# Get env vars

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

define('GITHUB_PERSONAL_ACCESS_TOKEN', $_ENV['GITHUB_PERSONAL_ACCESS_TOKEN']);
define('GITHUB_GIST_ID', $_ENV['GITHUB_GIST_ID']);
define('NOTION_DATABASE_ID', $_ENV['NOTION_DATABASE_ID']);
define('NOTION_KEY', $_ENV['NOTION_KEY']);


# Get data from Notion

function getNotionPages(): array
{
    $client = new Client();

    $body = [
        # Get this many items at a time
        'page_size' => 100,
        # We only care about items that have a scheduled datetime
        'filter' => [
            'property' => 'When',
            'date' => [
                'is_not_empty' => true,
            ],
        ],
        # Sort by the most recent first so we always get the most recent 100 events
        # FIXME: If you have a different name for the column you want to sort by, update this
        'sorts' => [[
            'property' => 'When',
            'direction' => 'descending',
        ]],
    ];

    $response = $client->request(
        'POST',
        'https://api.notion.com/v1/databases/' . NOTION_DATABASE_ID . '/query',
        [
            'body' => json_encode($body),
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . NOTION_KEY,
                'Content-Type' => 'application/json',
                'Notion-Version' => '2022-02-22',
            ],
        ]
    );

    $json = json_decode($response->getBody()->getContents(), true);
    $results = $json['results'];

    return $results;
}

$notionPages = getNotionPages();


# Parse Notion data into PHP objects

$pageCollection = new PageCollection();

foreach ($notionPages as $notionPage) {
    $pageCollection->add(PageNormalizer::JsonToObject($notionPage));
}


# Upload iCal to Gist

$github = new \Github\Client();
$github->authenticate(GITHUB_PERSONAL_ACCESS_TOKEN, null, \Github\AuthMethod::CLIENT_ID);

$gist = [
    'files' => [
        'notion2ical.ics' => [
            'content' => (string) $pageCollection,
        ],
    ],
];

$github->api('gists')->update(GITHUB_GIST_ID, $gist);


# Raw URL to latest gist without commit hash:
# https://gist.githubusercontent.com/YOUR_GITHUB_USERNAME/GITHUB_GIST_ID/raw/notion2ical.ical
