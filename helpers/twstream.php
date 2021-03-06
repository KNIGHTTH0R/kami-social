<?php

/**
 * Very basic streaming API example. In production you would store the
 * received tweets in a queue or database for later processing.
 *
 * Additional comments by artumi-richard
 *
 * Instructions:
 * 1) If you don't have one already, create a Twitter application on
 *      https://dev.twitter.com/apps
 * 2) From the application details page copy the consumer key and consumer
 *      secret into the place in this code marked with (YOUR_CONSUMER_KEY
 *      and YOUR_CONSUMER_SECRET)
 * 3) From the application details page copy the access token and access token
 *      secret into the place in this code marked with (A_USER_TOKEN
 *      and A_USER_SECRET)
 * 4) In a terminal or server type:
 *      php /path/to/here/streaming.php
 * 5) To stop the Streaming API either press CTRL-C or, in the folder the
 *      script is running from type:
 *      touch STOP
 *
 * @author themattharris
 */

function my_streaming_callback($data, $length, $metrics) {
  // Twitter sends keep alive's in their streaming API.
  // when this happens $data will appear empty. 
  // ref: https://dev.twitter.com/docs/streaming-apis/messages#Blank_lines
  
  echo $data .PHP_EOL;
  $data = explode(",", $data);
  var_dump((array)$data);
  die();
  //$fp = fopen("asd.xml", 'w');
  //fwrite($fp, $data);
  //fclose($fp);
  return file_exists(dirname(__FILE__) . '/STOP');
}

require '../sdk/tmhOAuth.php';
require '../sdk/tmhUtilities.php';
$tmhOAuth = new tmhOAuth(array(
  'consumer_key'    => 'TMUU1NOL7HTy1Qa0l4SF7Q',
  'consumer_secret' => 'Npa3CMxETIwsB0KuAFD0beNRKZwRwVyMcEqm3lk',
  'user_token'      => '816550262-YSOeNRPK4bTbAYI0gpKG9PWOlB5l3cLDUmMDCfoQ',
  'user_secret'     => 'ziAGGUz3T3fgnEJIztX2H9V8F1lVGJDM53nyjeQrEk',
));

$method = 'https://stream.twitter.com/1/statuses/filter.json';

// show Tweets which contan the word twitter OR have been geo-tagged within
// the bounding box -122.41,37.77,-122.40,37.78 OR are by themattharris

$params = array(
  // matches tweets containing 'twitter' 'Twitter' '#Twitter'
  'track'     => 'puzzle',
  // matches tweets containing 'twitter' or 'love' (no spaces!)
  //'track'   => 'twitter,love'
  // matches tweets containing 'twitter' and 'love'
  //'track'   =>'twitter love'
  // Warning on extra spaces - below matches 'twitter' but not 'love'!
  //'track'   =>'twitter, love'
  // Around Twitter HQ. First param is the SW corner of the bounding box
  //'locations' => '-122.41,37.77,-122.40,37.78',
  //'follow'    => '777925' // themattharris
);
$tmhOAuth->streaming_request('POST', $method, $params, 'my_streaming_callback');
// output any response we get back AFTER the Stream has stopped -- or it errors
tmhUtilities::pr($tmhOAuth);
?>