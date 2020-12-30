<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;

$ibonPrintAdminUrl = 'https://printadmin.ibon.com.tw/IbonUpload/IbonUpload/LocalFileUpload';

$client = new Client(['cookie' => true]);
$fileUuid = (string)Uuid::uuid4();
$formParams = [
    'multipart' => [
        [
            'name' => 'file',
            'contents' => fopen('./OOPPHP.pdf', 'r'),
            'headers' => [
                'Content-Type' => 'application/pdf',
            ],
        ],
        [
            'name' => 'fileName',
            'contents' => 'OOPPHP.pdf',
        ],
        [
            'name' => 'hash',
            'contents' => $fileUuid,
        ],
    ],
];
$response = $client->request('POST', $ibonPrintAdminUrl, $formParams);

$responseStr = (string)$response->getBody();
var_dump($responseStr);

$responseJson = json_decode((string)$response->getBody(), true);
var_dump($responseJson);

echo 'Letting uploaded file generate the QRCode and send information to the user email...', "\n";

$userName = 'Peter';
$email = 'peter279k@gmail.com';
$ibonFileUploadUrl = 'https://printadmin.ibon.com.tw/IbonUpload/IbonUpload/IbonFileUpload';
$formParams = [
    'form_params' => [
        'hash' => $fileUuid,
        'user' => $userName,
        'email' => $email,
    ],
];
$response = $client->request('POST', $ibonFileUploadUrl, $formParams);

$responseStr = (string)$response->getBody();
var_dump($responseStr);

$responseJson = json_decode((string)$response->getBody(), true);
var_dump($responseJson);
