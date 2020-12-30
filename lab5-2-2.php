<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\JavascriptException;

$singleEpisodeUrl = $argv[1] ?? '';
if ($singleEpisodeUrl === '') {
    echo 'Cannot find specific single episode URL. Exited...', "\n";
    exit(1);
}

$matchedCount = preg_match('/(https\:\/\/baabao\.com\/single-episode\/)(\d+)(\?)to=(\d+)&s=(\w+)/', $singleEpisodeUrl);

if ($matchedCount !== 1) {
    echo 'The single episode URL format is invalid...', "\n";
    exit(1);
}

$jsCode = "JSON.parse(JSON.parse(localStorage.getItem('localforage/listen_history/lastListenEpisode')))";
$browserOptions = [
    'headless' => true,
    'noSandbox' => true,
    'ignoreCertificateErrors' => true,
];

try {
    $browserFactory = new BrowserFactory('google-chrome-stable');

    $browser = $browserFactory->createBrowser($browserOptions);

    $page = $browser->createPage();
    $page->navigate($singleEpisodeUrl)->waitForNavigation();

    $episodeInfo = $page->evaluate($jsCode)->getReturnValue();

    echo 'Episode title: ', $episodeInfo['episode_title'], "\n";
    echo 'Episode Data URL: ', $episodeInfo['episode_data_url'], "\n";

    $browser->close();
} catch(JavascriptException $e) {
    echo 'Evaluating JavaScript is failed...Exited.', "\n";
    exit(1);
}

$downloadFileClient = new Client();
$requestOption = [
    'sink' => './files/' . $episodeInfo['episode_title'] . '.mp3',
];

echo 'Download files:', $episodeInfo['episode_title'], '.mp3', "...\n";
try {
    $downloadFileClient->request('GET', $episodeInfo['episode_data_url'], $requestOption);
} catch (RequestException $e) {
    echo 'Failed to download file:', $episodeInfo['episode_title'], '.mp3', "...\n";
    echo 'Failed to download URL:', $episodeInfo['episode_data_url'], "...\n";
    exit(1);
}

echo 'Download ', $episodeInfo['episode_title'], '.mp3 has been done.', "\n";
