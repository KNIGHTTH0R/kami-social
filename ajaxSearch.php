<?php
/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('./helpers/AppInfo.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  //header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
    $dataPath = "/tmp/";
} else {$dataPath = "c:\\folder\\";}

// This provides access to helper functions defined in 'utils.php'
require_once('./helpers/utils.php');


/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

require_once('sdk/src/facebook.php');
$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));
if(isset($_POST['txtSearch'])) $str = strip_tags( ($_POST['txtSearch']), '<b><a><ul><li><ol><em>' );
// Facebook pagination
if(isset($_POST['hidLink'])) {
    $link = ($_POST['hidLink']);
    $link = substr($link, strpos($link, "/search"));
}
// Twitter pagination
/* @deprecated
if(isset($_POST['hidSince'])) { //prev
    $since_id = ($_POST['hidSince']);
} else $since_id = '0';
if(isset($_POST['hidMax'])) { //next
    $max_id = ($_POST['hidMax']);
} else $max_id = '0';
*/
//var_dump($since_id);var_dump($max_id);
// Fetching FB posts
if (isset($str) && $str!="") {
    try{
        if($link) {
            $data = ($facebook->api($link));
            $pubPosts = idx($data, 'data', array());
            $paging = idx($data, 'paging', array());
            $fp = fopen($dataPath. urlencode($str) . "_fb_page" . $_POST['page'], 'w');
            fwrite($fp, serialize($pubPosts));
            fclose($fp);
            $item_count=0;
            $result_count = count($pubPosts);
        } else {
            $result_count = 0;
        }
    } catch (Exception $e) {var_dump($e->getMessage());}
} else {
    $str = "";
}

// Fetch the basic info of the app that they are using
?>
<?php
    $output = "";
    $output .= '<div class="divPosts">';
    $output .= '<label>More results: PAGE'.$_POST['page'].'</label>';
    $output .= '<br/><br/>';
    $output .= '<div class="lbl" align="center" style="display: '.($result_count>0?'':'none').'">Facebook posts | PAGE '.$_POST['page'].'</div>';
    $output .= '<div class="posts">';
    
    $intervalNames   = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year');
    $intervalSeconds = array( 1,        60,       3600,   86400, 604800, 2630880, 31570560);
          if($str!= "" && $result_count>0) {
            foreach ($pubPosts as $post) {
              // Extract the pieces of info we need from the requests above
              $item_count++;
              $id = idx($post, 'id');
              $msg = idx($post, 'message');
              $from = idx($post, 'from');
              $uid = $from['id'];
              $ctime = idx($post, 'created_time');
              $link = idx($post, 'link');
              $time = timeToInterval(new DateTime($ctime));
              // User info
              $uinfo = $facebook->api('/'. $uid );
              $username = idx($uinfo, 'username');
              $gender = idx($uinfo, 'gender'); $gender = $gender?$gender:"Private";
              $location = idx($uinfo, 'location');
              $add = $location['name'] . " " . $location['street'] . " ". $location['city'] . " ". $location['country'];
              $add = str_replace("   ", "", $add);
              $add = $add?$add:"Private";
              $locale = idx($uinfo, 'locale'); $locale = $locale?$locale:"Private";
              // append *More* hyperlink
              //$msg .= '&nbsp;<br/><a class="see-more" onclick="seeMore(\''. $id .'\', this)">See Comment(s)</a><br/>';
              $msg_more = '<br/><a href="javascript:;" class="see-more" onclick="seeMore(\''. $id .'\', null, \'0\', this)">See Comment(s)</a>';
            // get the time passed between now and the time of tweet, don't allow for negative
            // (future) values that may have occured if server time is wrong
          $output.='<div class="post">';
          $output.='<div class="uinfo">';
          $output.='<div class="post-pic"><img src="https://graph.facebook.com/'.$uid.'/picture"></div>';
          $output.='<a  target="_blank" href="https://www.facebook.com/'.$id.'">'.(($_POST['page']-1)* 10 + $item_count) . ') ' .$from['name'].':</a><br/>';
          $output.='<label class="lblhdr">Username</label>: '.$username.'<br/>';
          $output.='<label class="lblhdr">Gender</label>: '. $gender .'<br/>';
          $output.='<label class="lblhdr">Location</label>: '.$add.'<br/>';
          $output.='<label class="lblhdr">Language</label>: '. $locale.'<br/>';
          $output.='</div>';
          $output.='<span><i>Published '.$time.'</i></span><br/>';
          $output.='<a class="post-link" href="'.$link.'">'.$link.'</a><br/>'.strip_tags( $msg, '<b><a><ul><li><ol><em>' ) . $msg_more;
          $output.='<br /><div id="'. $id .'"></div>';
          $output.='</div>';
            }
          }
        $output.='</div>';
        $output.='<input type="hidden" id="hidLinkAjax'.$_POST['page'].'" name="hidLinkAjax'.$_POST['page'].'" value="'.$paging['next'].'">';
        $output.='<input type="hidden" id="hidCountFb'.$_POST['page'].'" name="hidCountFb'.$_POST['page'].'" value="'.$item_count.'">';
    $output.='</div>';
    
    if ($str != "") {
        try{
            require('./helpers/twitter.class.php');
            $twitter = new twitter_class();
            $output.= $twitter->getTweets(($str), 10, $since_id, $max_id, $dataPath, $_POST['page']);
        } catch (exception $e) {var_dump($e->getMessage());}
    }
    $arrOutput = array('output' => $output);
    //echo $arrOutput['output'];
    //$arrOutput[] = $output;
    echo json_encode($arrOutput);
    ?>