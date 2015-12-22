<?php
/*
 * Sample script for searching google places by given keywords
 */

require 'CurlHttpClient.php';

print_r(hit("gyms in bangalore"));

function hit($keywords,$attempt=0,$page_token=null)
{
    $geocode_api_key = ''; //put your api key here

    $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json';
    $params = array("query" => $keywords, "key" => $geocode_api_key);
    if ($page_token) {
        $params['pagetoken'] = $page_token;
    }
    $url = $url . "?";
    foreach ($params as $key => $value) {
        $url = $url . $key . '=' . urlencode($value) . '&';
    }
    rtrim($url, '&');

    if ($attempt > 0) {
        echo "\nattempt = $attempt\n";
    }

    if ($attempt >= 5) {
        echo "\n\ngiving up on $url\n\n";
        exit;
    }

    $ch = new CurlHttpClient();
    $ch->fetch_url($url);
    $resp_raw = $ch->get_response();
    echo "$url attempt = $attempt, resp_raw = $resp_raw\n";
    $resp = json_decode($resp_raw, true);

    $status = $resp['status'];

    if ($status == 'OVER_QUERY_LIMIT') {//api query limit exceeded
        echo "over limit for $keywords";
        exit;
    }
    if ($status == 'ZERO_RESULTS') {
        echo 'zero results';
        exit;
    }
    if ($status != 'OK') {
        usleep(500000);
        return hit($keywords,$attempt+1);
    }

    $page_token = isset($resp['next_page_token']) ? $resp['next_page_token'] : null;
    return array($resp['results'], $page_token);
}
