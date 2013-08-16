<?php
require_once('./helpers/AppInfo.php');
require_once('./helpers/utils.php');
$app_id = AppInfo::appID();
$app_secret = AppInfo::appSecret();
$post_id = $_REQUEST['id'];
$next_page = $_REQUEST['paging-next'];
$page_no = $_REQUEST['paging-no'];
$request_url = "";
$BOOL_IsPaging = false;
$access_token = AppInfo::getToken();//"AAAEJQAufhpcBALCAmOAg5Q9opqheOl8IYJVH5YMBevQrpOAin26r3iA85zQ7p5UvCQjYDfvdZA6MG4dUlqj4RUvK6oBeoNfsR8945w77PLnqhVhTN";
if (!strstr($access_token, "access_token")) {
    $access_token = "access_token=" . $access_token;
}
if(!$next_page || $next_page=="null") {
    $request_url = "https://graph.facebook.com/".$post_id."/comments?limit=1000&" . $access_token;
} else {
    $request_url = $next_page;
    $BOOL_IsPaging = true;
}
/* WHEN TOKEN EXPIRES, UNCOMMENT THIS TO GET NEW TOKEN
if (!$access_token || $access_token=="") {
    $access_token = "AAAEJQAufhpcBAGLCF5uKuYO2wkIW16tPrQVCKsEr8h8Grg7xZBDXJodvaEi4eRtjTtWCb7IytIPR9H28WXnlrErKjAVlj6KY4UqvZAnfCvm0X0LqHv";
    // get user access_token
  $token_url = 'https://graph.facebook.com/oauth/access_token?client_id='
    . $app_id . '&grant_type=fb_exchange_token&fb_exchange_token=' . $access_token
    . '&client_secret=' . $app_secret;
  $access_token = file_get_contents($token_url); // Long-lived access token (max 60 days)
  var_dump($access_token);
  putenv("ACCESS_TOKEN=$access_token");
  var_dump(AppInfo::getToken());
} */
$data = file_get_contents($request_url);
$data = (array)json_decode($data, true);
$cmts = idx($data, 'data', array());
$count = count($cmts);
$limit = 15;
$limit_max = ($page_no==1?1000:$limit+1);
$i = 0;
$output = "";
$paging_next_url = "";
foreach ($cmts as $cmt) {
    if ($i < $limit) $i++;
    else break;
    $cmt = (array)$cmt;
    $cmt_id = idx($cmt, 'id');
    $msg = idx($cmt, 'message');
    $from = idx($cmt, 'from');
    $uid = $from['id'];
    $ctime = idx($cmt, 'created_time');
    $time = timeToInterval(new DateTime($ctime));
    if($i == $limit) {
        $paging_next_url = "https://graph.facebook.com/".$post_id."/comments?limit=".$limit_max."&" . $access_token . "&offset=".($page_no * $limit)."&__after_id=".$cmt_id;
    }
    $output.='<div class="post cmt">';
    $output.='<div class="post-pic"><img src="https://graph.facebook.com/'.$uid.'/picture"></div>';
    $output.='<a href="https://www.facebook.com/'.$uid.'/">'.$from['name'].':</a><br/>';
    $output.='<span class="time"><i>'.$time.'</i></span><br/>';
    //$output.='<a class="post-link" href="'.$link.'">'.$link.'</a><br/>'.strip_tags( $msg, '<b><a><ul><li><ol><em>' );
    $output.= strip_tags( $msg, '<b><a><ul><li><ol><em>' );
    $output.='</div>';
}
if ($count > $limit) {
    $output.='<a href="javascript:;" class="see-more" onclick="seeMore(\''.$post_id.'\',\''. $paging_next_url .'\',\''.++$page_no.'\' , this)">More Comment(s)</a>';
}
$outTotalCmts = $BOOL_IsPaging?"":"Total " . $count . " comment(s).<br/>"; 
echo $outTotalCmts . $output;