<?php

define("GOSERVER", "http://go.brandwatch.com/");
define("GOSERVERSHORT", "http://go/");
define("GOSERVERDISPLAY", "go");
define("BOTNAME", "go");
define("INCOMINGWEBHOOK", "https://hooks.slack.com/services/xxx"); //Use your own incoming webhook URL here

function get_long_url( $slug ) 
{ 
    $options = array( 
        CURLOPT_RETURNTRANSFER => true,     // return web page 
        CURLOPT_HEADER         => false,    // return headers 
    ); 
 
    $ch = curl_init( GOSERVER . '_api/go-link.json' ); 
    curl_setopt_array( $ch, $options ); 
    $content = curl_exec( $ch ); 
 
    if(curl_errno($ch)) {
        die ("An error occured (is the Go server up?)"); //problem with the go server
    }

    curl_close( $ch ); 

    $links = json_decode($content, true);

    foreach ($links as $link) {
    	if ($link['shortUri'] == $slug) {
            return $link['longUri'];
    	}
    }
    die ("No link found for " . $slug);
}

$long_url = get_long_url( $_POST["text"] );

// do the incoming webhook
$payload = array(
    text => "@" . $_POST["user_name"] . " shared *<" . GOSERVERSHORT . $_POST["text"] . "|" . GOSERVERDISPLAY . "/" . $_POST["text"] . ">*\n" . "_ <" . GOSERVERSHORT . $_POST["text"] . "|" . urlencode($long_url) . ">_",
    username => BOTNAME,
    channel => $_POST["channel_id"],
    link_names => 1,
    mrkdwn => "true"
);

$json_payload = json_encode($payload);

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, INCOMINGWEBHOOK);
curl_setopt($ch,CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "payload=" . $json_payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);