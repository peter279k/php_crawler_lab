<?php  
  
define('MAILGUN_URL', 'https://api.mailgun.net/v3/sandbox5099c0f44ddb4ce0883b7ed9d2a87499.mailgun.org');  
define('MAILGUN_KEY', 'key-mailgun-key');
  
require_once __DIR__ . '/vendor/autoload.php';  
  
use GuzzleHttp\Client;  
use Symfony\Component\DomCrawler\Crawler;  
  
$latestNews = 'https://www.nttu.edu.tw/p/503-1000-1009.php';  
$client = new Client();  
$response = $client->request('GET', $latestNews);  
  
$latestNewsString = (string)$response->getBody();  
  
$titles = [];  
$descriptions = [];  
$pubDates = [];  
$links = [];  
$authors = [];  
  
$crawler = new Crawler($latestNewsString);  
  
$crawler  
    ->filter('title')  
    ->reduce(function (Crawler $node, $i) {  
        global $titles;  
        $titles[] = $node->text();  
    });  
  
$crawler  
    ->filter('description')  
    ->reduce(function (Crawler $node, $i) {  
        global $descriptions;  
        $descriptions[] = str_replace([" ", "\n", "\r", "\t"], "", strip_tags($node->text()));  
    });  
  
$crawler  
    ->filter('pubDate')  
    ->reduce(function (Crawler $node, $i) {  
        global $pubDates;  
        $pubDates[] = $node->text();  
    });  
  
$crawler  
    ->filter('link')  
    ->reduce(function (Crawler $node, $i) {  
        global $links;  
        $links[] = $node->text();  
    });  
  
$crawler  
    ->filter('author')  
    ->reduce(function (Crawler $node, $i) {  
        global $authors;  
        $authors[] = $node->text();  
    });  
  
// var_dump($descriptions);  
// var_dump($pubDates);  
// var_dump($links);  
// var_dump($authors);  
// var_dump($titles);  
  
$text = implode(',', $descriptions) . "\n" . implode(',', $pubDates) . "\n" . implode(',', $links) . "\n";  
$text .= implode(',', $authors) . "\n" . implode(',', $titles) . "\n";  
  
//$result = sendMailByMailGun('TO_EMAIL_ADDRESS', 'Peter', 'admin', 'admin@DOMAIN_NAME', 'test', '', $text, '', '');  
$result = sendMailByMailGunGuzzle('peter279k@gmail.com', 'Peter', 'admin', 'admin@example.com', 'test', '', $text, '', '');  
  
var_dump($result); 

function sendMailByMailGunGuzzle($to, $toName, $mailFromName, $mailFrom, $subject, $html, $text, $tag, $replyTo) {
    $client = new Client();
    $arrayData = [
        'from'=> $mailFromName .'<'.$mailFrom.'>',
        'to'=>$toName.'<'.$to.'>',
        'subject'=>$subject,
        'html'=>$html,
        'text'=>$text,
        'o:tracking'=>'yes',
        'o:tracking-clicks'=>'yes',
        'o:tracking-opens'=>'yes',
        'o:tag'=>$tag,
        'h:Reply-To'=>$replyTo
    ];
    $requestArray = [
        'form_params' => $arrayData,
        'auth' => ['api', MAILGUN_KEY],
    ];
    $response = $client->request('POST', MAILGUN_URL . '/messages', $requestArray);

    return (string)$response->getBody();
}
 
function sendMailByMailGun($to, $toName, $mailFromName, $mailFrom, $subject, $html, $text, $tag, $replyTo) {  
    $arrayData = [  
        'from'=> $mailFromName .'<'.$mailFrom.'>',  
        'to'=>$toName.'<'.$to.'>',  
        'subject'=>$subject,  
        'html'=>$html,  
        'text'=>$text,  
        'o:tracking'=>'yes',  
        'o:tracking-clicks'=>'yes',  
        'o:tracking-opens'=>'yes',  
        'o:tag'=>$tag,  
        'h:Reply-To'=>$replyTo  
    ];
  
    $session = curl_init(MAILGUN_URL . '/messages');  
    curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);  
    curl_setopt($session, CURLOPT_USERPWD, 'api:' . MAILGUN_KEY);  
    curl_setopt($session, CURLOPT_POST, true);  
    curl_setopt($session, CURLOPT_POSTFIELDS, $arrayData);  
    curl_setopt($session, CURLOPT_HEADER, false);  
    curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');  
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);  
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);  
    $response = curl_exec($session);  
    curl_close($session);  
  
    $results = json_decode($response, true);  
  
    return $results;  
}
