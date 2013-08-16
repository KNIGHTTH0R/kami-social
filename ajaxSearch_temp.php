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
if(isset($_POST['txtSearch'])) $str = ($_POST['txtSearch']);
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
$app_info = $facebook->api('/'. AppInfo::appID());
$app_name = idx($app_info, 'name', '');
?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

    <title><?php echo he($app_name); ?></title>
    <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
    <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" />

    <!--[if IEMobile]>
    <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"  />
    <![endif]-->

    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
    <meta property="og:title" content="<?php echo he($app_name); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
    <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>" />
    <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
    <meta property="og:description" content="My first app" />
    <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

    <script type="text/javascript">
      function logResponse(response) {
        if (console && console.log) {
          console.log('The response was', response);
        }
      }
    </script>

    <!--[if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->
  </head>
  <body onload="">
    <div id="fb-root"></div>
    <script type="text/javascript">
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
        });

        // Listen to the auth.login which will be called when the user logs in
        // using the Login button
        FB.Event.subscribe('auth.login', function(response) {
          // We want to reload the page now so PHP can read the cookie that the
          // Javascript SDK sat. But we don't want to use
          // window.location.reload() because if this is in a canvas there was a
          // post made to this page and a reload will trigger a message to the
          // user asking if they want to send data again.
          window.location = window.location;
        });

        FB.Canvas.setAutoGrow();
      };

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>
    
    <div class="divPosts">
        <label>More results: PAGE <?php echo $_POST['page'];?></label>
        <br/><br/>
        <div class="lbl" align="center" style="display: <?php echo $result_count>0?'':'none';?>;">Facebook posts | PAGE <?php echo $_POST['page'];?></div>
    <div class="posts">
          <?php
          $intervalNames   = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year');
          $intervalSeconds = array( 1,        60,       3600,   86400, 604800, 2630880, 31570560);
          if($str!= "" && $result_count>0) {
            foreach ($pubPosts as $post) {
              // Extract the pieces of info we need from the requests above
              $item_count++;
              $id = idx($post, 'id');
              $msg = idx($post, 'message');
              $from = idx($post, 'from');
              $ctime = idx($post, 'created_time');
              //$pic = idx($post, 'picture');
              $link = idx($post, 'link');
              $strTime = new DateTime($ctime);
              
            // get the time passed between now and the time of tweet, don't allow for negative
            // (future) values that may have occured if server time is wrong
            $time = 'just now';
            $secondsPassed = time() - strtotime($strTime->format('Y-m-d H:i:s'));//strtotime($crtEntry->published);

            if ($secondsPassed>0)
            {
                // see what interval are we in
                for($j = count($intervalSeconds)-1; ($j >= 0); $j--)
                {
                    $crtIntervalName = $intervalNames[$j];
                    $crtInterval = $intervalSeconds[$j];
                        
                    if ($secondsPassed >= $crtInterval)
                    {
                        $value = floor($secondsPassed / $crtInterval);
                        if ($value > 1)
                            $crtIntervalName .= 's';
                            
                        $time = $value . ' ' . $crtIntervalName . ' ago';
                        
                        break;
                    }
                }
            }
          ?>
          <div class="post">
            <div class="post-pic"><img src="https://graph.facebook.com/<?php echo $from['id']; ?>/picture"></div>
            <a href="http://fb.com/<?php echo $from['id']; ?>/"><?php echo (($_POST['page']-1)* 10 + $item_count) . ") " .$from['name']; ?>:</a>
            <span><i><?php echo $time; ?></i></span>
            <a class="post-link" href="<?php echo $link;?>"><?php echo $link;?></a>&nbsp;<?php echo strip_tags( $msg, '<b><a><ul><li><ol><em>' ); ?>
            <br />
            <br />
          </div>
          <?php
            }
          }
          ?>
        </div>
        <input type="hidden" id="hidLinkAjax<?php echo $_POST['page']; ?>" name="hidLinkAjax<?php echo $_POST['page']; ?>" value="<?php echo $paging['next'];?>">
        <input type="hidden" id="hidCountFb<?php echo $_POST['page']; ?>" name="hidCountFb<?php echo $_POST['page']; ?>" value="<?php echo $item_count;?>">
    </div>
    
    <?php
    if ($str != "") {
        try{
            require('./helpers/twitter.class.php');
            $twitter = new twitter_class();
            echo $twitter->getTweets(($str), 10, $since_id, $max_id, $dataPath, $_POST['page']);
        } catch (exception $e) {var_dump($e->getMessage());}
    }
    ?>
  </body>
</html>