<?php

$client_id = 'mcs5885646876.ml.vision.4JULETPZR2j6mjB7dgpEL';
$client_secret = '4oZAh8nBd5wUJ23Rvos9r5ucurpP9vFQZiNSEed8LfZxkHqqWh2rgfKXoG3hpQ';

$url = 'https://mcs.mail.ru/auth/oauth/v1/token';

$post_fields = 'client_id='.$client_id.'&client_secret='.$client_secret.'&grant_type=client_credentials';
// $url = 'https://ferma-test.ofd.ru/api/kkt/cloud/receipt?AuthToken='.$token;

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Content-type: application/x-www-form-urlencoded"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
$response = json_decode($json_response, true);


if (!empty($response['access_token'])) {
    $token = $response['access_token'];
    print_r("Token: $token\n");
    exit();
} else {
    print_r($response);
    exit('Token not recieved');
}


// mcs5885646876.ml.vision.4JULETPZR2j6mjB7dgpEL
// 4oZAh8nBd5wUJ23Rvos9r5ucurpP9vFQZiNSEed8LfZxkHqqWh2rgfKXoG3hpQ
// "https://smarty.mail.ru/api/v1/persons/recognize?oauth_provider=mr&oauth_token=e50b000614a371ce99c01a80a4558d8ed93b313737363830" \

$url = 'https://smarty.mail.ru/api/v1/persons/recognize?oauth_provider=mcs&oauth_token='.$token;
$path = __DIR__ . '/images/p2.jpg';/*
$url = 'https://smarty.mail.ru/api/v1/docs/detect?oauth_provider=mcs&oauth_token='.$token;
$path = __DIR__ . '/images/p1.jpg';*/
$meta = json_encode(['space'=>'1', 'create_new'=>true, 'images'=>[['name'=>$path]]], true);
// $meta = '{"space":"1", "images":[{"name":"i1.jpg"}]}';


$filenames = array($path);
$files = array();
foreach ($filenames as $f)
{
    if (strlen($f) == 0)
    {
        # just to make correct multipart/form-data request
        $files[$f] = 'fake content';
    }
    else
    {
        $files[$f] = file_get_contents($f);
    }
}

$boundary = uniqid();
$delimiter = '-------------' . $boundary;

$fields = array("meta"=> $meta);
$post_data = build_data_files($boundary, $fields, $files);

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  #CURLOPT_VERBOSE => true,
  CURLOPT_RETURNTRANSFER => 1,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_POST => 1,
  CURLOPT_POSTFIELDS => $post_data,
  CURLOPT_HTTPHEADER => array(
    "Content-Type: multipart/form-data; boundary=" . $delimiter,
    "Content-Length: " . strlen($post_data),
  ),
));

$response = curl_exec($curl);

$info = curl_getinfo($curl);
// echo "code: ${info['http_code']}\n";
debug($response);
curl_close($curl);

function build_data_files($boundary, $fields, $files)
{
    $data = '';
    $eol = "\r\n";

    $delimiter = '-------------' . $boundary;

    foreach ($fields as $name => $content)
    {
        $data .= "--" . $delimiter . $eol
            . 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
            . $content . $eol;
    }

    foreach ($files as $name => $content)
    {
        $data .= "--" . $delimiter . $eol
            . 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $name . '"' . $eol
            . 'Content-Type: image/jpeg'.$eol
            . 'Content-Transfer-Encoding: binary'.$eol
            ;

        $data .= $eol;
        $data .= $content . $eol;
    }
    $data .= "--" . $delimiter . "--".$eol;
    return $data;
}

?>
