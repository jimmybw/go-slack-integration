<?php

define("GO_SERVER", "http://go.brandwatch.com/");
define("GO_SERVER_SHORT", "http://go/");
define("GO_SERVER_DISPLAY", "go");
define("GO_LINKS_API_PATH", "_api/go-link.json");
define("BOT_NAME", "Go/");
define("INCOMING_WEBHOOK", "https://hooks.slack.com/services/xxx"); //Use your own incoming webhook URL here

function getGoLinks ( $server ) //returns JSON array of linkd from Go/ server
{
    $options = array( 
        CURLOPT_RETURNTRANSFER => true,     // return web page 
        CURLOPT_HEADER         => false,    // return headers 
    ); 
 
    $curlHandle = curl_init( $server ); 
    curl_setopt_array( $curlHandle, $options ); 
    $content = curl_exec( $curlHandle ); 
 

    if (curl_errno($curlHandle)) {
        die ("An error occured (is the Go server up?)"); //problem with the go server
    }

    curl_close( $curlHandle ); 

    return json_decode($content, true);

}

function getLongUrl( $slug )
{ 
    $links = getGoLinks( GO_SERVER . GO_LINKS_API_PATH );

    foreach ($links as $link) {
    	if ($link['shortUri'] == $slug) {
            return $link['longUri'];
    	}
    }
    die ("No link found for " . $slug);
}

function postToSlack( $message )
{
    $payload = array(
        text => $message,
        username => BOT_NAME,
        channel => $_POST["channel_id"],
        link_names => 1,
        mrkdwn => "true"
    );

    $json_payload = json_encode($payload);

    $curlHandle = curl_init();
    curl_setopt($curlHandle, CURLOPT_URL, INCOMING_WEBHOOK);
    curl_setopt($curlHandle, CURLOPT_POST, 1);
    curl_setopt($curlHandle, CURLOPT_POSTFIELDS, "payload=" . $json_payload);
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curlHandle);

}

$longUrl = getLongUrl( $_POST["text"] );

$returnText = "@" . $_POST["user_name"] . " shared *<" . 
              GO_SERVER_SHORT . $_POST["text"] . "|" . GO_SERVER_DISPLAY . "/" . $_POST["text"] . ">*\n" . 
              "_ <" . GO_SERVER_SHORT . $_POST["text"] . "|" . urlencode($longUrl) . ">_";

postToSlack($returnText);