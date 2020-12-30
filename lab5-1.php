<?php

require_once './vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

$loginUrl = 'https://www.leezen.com.tw/login.php';
$captchaUrl = 'https://www.leezen.com.tw/captcha/code.php';

$client = new Client(['cookies' => true]);
$response = $client->request('GET', $loginUrl);
$loginPageResponse = (string)$response->getBody();

$codeResponse = $client->request('GET', $captchaUrl);
file_put_contents('./code.png', (string)$codeResponse->getBody());

exec('tesseract ./code.png code');

$code = file_get_contents('./code.txt');
preg_match('/(\d+)/', $code, $matched);
$code = $matched[0];

$crawler = new Crawler($loginPageResponse);
$token = '';
$crawler
   ->filter('input[type="hidden"]')
   ->reduce(function (Crawler $node, $i) {
       global $token;
       if ($node->attr('name') === 'token') {
           $token = $node->attr('value');
       }
   });

$formParams = [
    'form_params' => [
        'member' => 'email_or_phone',
        'member_m' => 'email_or_phone',
        'member_password' => 'password',
        'Mode' => 'login',
        'token' => $token,
        'Turing2' => $code,
        'login' => '登入',
    ],
];

$postLoginUrl = 'https://www.leezen.com.tw/member_process.php';
$response = $client->request('POST', $postLoginUrl, $formParams);

$loginResponseString = (string)$response->getBody();

var_dump($loginResponseString);

if (strstr($loginResponseString, '登入成功') === false) {
    exit(1);
}

$todayMonth = Carbon\Carbon::now();
$preThreeMonths = clone $todayMonth;
$preThreeMonths->subMonths(3);

$storeBillingUrl = 'https://www.leezen.com.tw/ajax_storebilling.php';
$formParams = [
    'form_params' => [
        's' => $preThreeMonths->format('Y-m-d') . ' 00:00:01',
        'e' => $todayMonth->format('Y-m-d') . ' 23:59:59',
    ],
];
$storeBillingResponse = $client->request('POST', $storeBillingUrl, $formParams);

$crawler = new Crawler((string)$storeBillingResponse->getBody());
$shoppingLists = $crawler
   ->filter('table[class="cart-table responsive-table"] > tr')
   ->each(function (Crawler $node, $i) {
        $first = $node->children()->first()->text();
        $second = $node->children()->nextAll()->text();
        $last = $node->children()->last()->html();
        $last = str_replace(['<a href="', '">點我查詢</a>'], '', $last);
        $last = 'https://www.leezen.com.tw/' . $last;

        return [$first, $second, $last];
   });

print_r($shoppingLists);
