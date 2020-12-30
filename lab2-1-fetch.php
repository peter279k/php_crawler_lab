<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

$publicCourses = 'https://infosys.nttu.edu.tw/n_CourseBase_Select/CourseListPublic.aspx';

$headers = [
    'Host' => 'infosys.nttu.edu.tw',
    'Connection' => 'keep-alive',
    'Cache-Control' => 'max-age=0',
    'Upgrade-Insecure-Requests' => '1',
    'Sec-Fetch-Mode' => 'navigate',
    'Sec-Fetch-User' => '?1',
    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8 application/signed-exchange;v=b3',
    'Sec-Fetch-Site' => 'none',
    'Referer' => 'https://infosys.nttu.edu.tw/',
    'Accept-Encoding' => 'gzip, deflate, br',
    'Accept-Language' => 'zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7',
    'User-Agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
];

$client = new Client(['cookies' => true]);
$response = $client->request('GET', $publicCourses, [
    'headers' => $headers,
]);

$publicCourseString = (string)$response->getBody();
$viewState = '__VIEWSTATE';
$eventValidation = '__EVENTVALIDATION';
$viewStateGenerator = '5D156DDA';

$crawler = new Crawler($publicCourseString);

$crawler
   ->filter('input[type="hidden"]')
   ->reduce(function (Crawler $node, $i) {
       global $viewState;
       global $eventValidation;
       if ($node->attr('name') === $viewState) {
           $viewState = $node->attr('value');
       }
       if ($node->attr('name') === $eventValidation) {
           $eventValidation = $node->attr('value');
       }
   });

$formParams = [
    'form_params' => [
        'ToolkitScriptManager1' => 'UpdatePanel1|Button3',
        'ToolkitScriptManager1_HiddenField' => '',
        'DropDownList1' => '1081',
        'DropDownList6' => '1',
        'DropDownList2' => '%',
        'DropDownList3' => '%',
        'DropDownList4' => '%',
        'TextBox9' => '',
        'DropDownList5' => '%',
        'DropDownList7' => '%',
        'TextBox1' => '',
        'DropDownList8' => '%',
        'TextBox6' => '0',
        'TextBox7' => '14',
        '__EVENTTARGET' => '',
        '__EVENTARGUMENT' => '',
        '__LASTFOCUS' => '',
        '__VIEWSTATE' => $viewState,
        '__VIEWSTATEGENERATOR' => $viewStateGenerator,
        '__SCROLLPOSITIONX' => '0',
        '__SCROLLPOSITIONY' => '0',
        '__EVENTVALIDATION' => $eventValidation,
        '__VIEWSTATEENCRYPTED' => '',
        '__ASYNCPOST' => 'false',
        'Button3' => '查詢',
    ],
    'headers' => [
        'Sec-Fetch-Mode: cors',
        'Origin: https://infosys.nttu.edu.tw',
        'Accept-Encoding: gzip, deflate, br',
        'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7',
        'X-Requested-With: XMLHttpRequest',
        'Connection: keep-alive',
        'X-MicrosoftAjax: Delta=true',
        'Accept: */*',
        'Cache-Control: no-cache',
        'Referer: https://infosys.nttu.edu.tw/n_CourseBase_Select/CourseListPublic.aspx',
        'Sec-Fetch-Site: same-origin',
        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
    ],
];

$response = $client->request('POST', $publicCourses, $formParams);

$coursesString = (string)$response->getBody();

$crawler = new Crawler($coursesString);

$orders = [
    'mandatory',
    'class',
    'category',
    'number',
    'name',
    'outline',
    'credit',
    'people_limit',
    'people_least',
    'people_select_course',
    'people_course',
    'teacher',
    'teach_time',
    'teach_place',
    'advanced_course',
    'merged_class',
    'p.s',
    'course_limit_info',
    'special_course',
];

$courses = [
    'mandatory' => [],
    'class' => [],
    'category' => [],
    'number' => [],
    'name' => [],
    'outline' => [],
    'credit' => [],
    'people_limit' => [],
    'people_least' => [],
    'people_select_course' => [],
    'people_course' => [],
    'teacher' => [],
    'teach_time' => [],
    'teach_place' => [],
    'advanced_course' => [],
    'merged_class' => [],
    'p.s' => [],
    'course_limit_info' => [],
    'special_course' => [],
];

$outlineHost = 'https://infosys.nttu.edu.tw/n_CourseBase_Select/';

$crawler
   ->filter('tr[class="NTTU_GridView_Row"] td')
   ->reduce(function (Crawler $node, $i) {
        global $courses;
        global $orders;
        global $outlineHost;

        $index = $i % 19;
        $text = str_replace([' ', "\n", "\r"], '', $node->text());

        if ($index === 5) {
            $outlineText = str_replace('amp;', '', $node->html());
            preg_match("/CourseInfo1.aspx\?id=\d+&yrsem=\d+/", $outlineText, $matched);
            if (count($matched) === 1) {
                $courses[$orders[$index]][] = $outlineHost . $matched[0];
            }
        } else {
            $courses[$orders[$index]][] = $text;
        }
   });

var_dump($courses);
