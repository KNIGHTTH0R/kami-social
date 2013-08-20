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
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
} elseif ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
	$dataPath = "./data/";
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
if(($_GET['txtSearch'])) $str = strip_tags( ($_GET['txtSearch']), '<b><a><ul><li><ol><em>' );
$unix_from = $unix_to = '';
if(($_REQUEST['dateFrom'])) {
    $date_from = $_REQUEST['dateFrom'];
    $unix_from = '&since='.strtotime($date_from);
}
if(($_REQUEST['dateTo'])) {
    $date_to = $_REQUEST['dateTo'];
    //$date_to = time() - 4*60*60;$unix_to = '&until='.($date_to);
    $unix_to = '&until='.strtotime($date_to);
}
// Facebook pagination
if(isset($_POST['hidLink'])) {
    $link = ($_POST['hidLink']);
    $link = substr($link, strpos($link, "/search"));
}
// Twitter pagination
if(isset($_POST['hidSince'])) { // Previous
    $since_id = ($_POST['hidSince']);
} else $since_id = '0';
if(isset($_POST['hidMax'])) { // Next
    $max_id = ($_POST['hidMax']);
} else $max_id = '0';
//var_dump($since_id);var_dump($max_id);
// Fetching FB posts
if (isset($str) && $str!="") {
try{
    if(!isset($link)) {
        // Until: Date_of_results < Until_date
        // Since: Date_of_results > since_date (latest till since_date)
        $link = '/search?limit=10&type=post&fields=id,message,from,link,picture,type&q=' . urlencode($str) . $unix_from . $unix_to;
    } else {
    }
    $data = ($facebook->api($link));
    $pubPosts = idx($data, 'data', array());
    $paging = idx($data, 'paging', array());
    $fp = fopen($dataPath. urlencode($str) . "_fb_page1", 'w');
    fwrite($fp, serialize($pubPosts));
    fclose($fp);
} catch (Exception $e) {var_dump($e->getMessage());}
} else {
    $str = "";
    //echo "No query";
    //die();
}

//$user_id = $facebook->getUser();

if ($user_id) {
  try {
    // Fetch the viewer's basic information
    //$basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    //if (!$facebook->getUser()) {
    //  header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
    //  exit();
    //}
  }
  /*$app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));*/
}
// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());
$app_name = idx($app_info, 'name', '');
$item_count=0;
?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

    <title><?php echo he($app_name); ?></title>
    <link rel="stylesheet" href="stylesheets/base.css" type="text/css" />
    <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
    <link rel="stylesheet" href="stylesheets/jdpicker.css" media="Screen" type="text/css" />
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
    <script type="text/javascript" src="/javascript/jquery.scrollTo-1.4.2.js"></script>
    <script type="text/javascript" src="/javascript/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="/javascript/jquery.highlight-4.js"></script>
    <script type="text/javascript" src="/javascript/jquery.jdpicker.js"></script>

    <script type="text/javascript">
      var PAGE = 1;
      var AJAX = false;
      
      function logResponse(response) {
        if (console && console.log) {
          console.log('The response was', response);
        }
      }
      function prepareAjax(){
      var loadMore = $('#load-more');
            //load event / ajax
            loadMore.click(function(){
                //add the activate class and change the message
                if (AJAX) {
                    return;
                }
                loadMore.addClass('activate').text('Loading...');
                AJAX = true;
                //begin the ajax attempt
                $.ajax({
                    url: 'ajaxSearch.php',
                    data: {
                        'txtSearch': '<?php echo $str; ?>',
                        //'hidMax': document.getElementById('hidMaxId').value,
                        'hidLink': document.getElementById('hidLinkNext').value,
                        'page': ++PAGE,
                    },
                    type: 'post',
                    datatype : 'json',
                    success: function(responseJSON) {
                        var arrJson = $.parseJSON(responseJSON);
                        loadMore.text('Load More');
                        document.getElementById('ajax-posts').innerHTML += arrJson.output;
                        document.getElementById('hidLinkNext').value = document.getElementById('hidLinkAjax'+ PAGE).value;
                        highlighter('<?php echo $str;?>');
                        prepareAjax();
                        countItems();
                    },
                    error: function() {
                        loadMore.text('Oops! Try Again.');
                    },
                    complete: function() {
                        AJAX = false;
                        loadMore.removeClass('activate');
                    }
                });
            });
       };
       
       function countItems() {
           if (document.getElementById("txtSearch").value != "") {
               try{
                   var cfb = document.getElementById('hidCountFb'+ PAGE).value;
                   var ctw = document.getElementById('hidCountTw'+ PAGE).value;
                   if ((cfb < 10 && ctw < 10) || (cfb == 0 && ctw == 0)) {
                       //document.getElementById('load-more').style.display = "none";
                       $('#load-more').text("No matching result");
                       $('#load-more').unbind("click");
                   } else {
                       document.getElementById('load-more').style.display = "block";
                   }
               } catch (err) {
                   console.log(err);
                   document.getElementById('load-more').style.display = "none";
               }
           }
       }
       
       function highlighter(input) {
           var words = input.split(" ");
           for(var i = 0; i<words.length; i++){
               $('div').highlight(words[i]);
           }
       }
       
       function seeMore(id, np, pn, ahref) {
           var obj = document.getElementById(id);
           if ((obj.innerHTML == "" && np == null) || (obj.innerHTML != "" && np != null)) {
               ahref.innerHTML = "<img src='spinnerz.gif'/>";
                $.ajax({
                    url: 'fetchCmt.php',
                    data: {
                        'id': id,
                        'paging-next': np,
                        'paging-no': pn,
                    },
                    type: 'get',
                    success: function(rspData) {
                        if (pn > 1) hideObj(ahref);
                        obj.innerHTML += rspData;
                        highlighter('<?php echo $str;?>');
                    },
                    error: function(rsp) {
                        if (pn > 1) hideObj(ahref);
                        obj.innerHTML = "<b>No comment is available</b>";
                    },
                    complete: function() {
                        ahref.innerHTML = "Hide Comment(s)";
                        obj.style.display = "block";
                    }
                });
           } else {
               if (ahref.innerHTML == "See Comment(s)") {
                ahref.innerHTML = "Hide Comment(s)";
                obj.style.display = "block";
               } else {
                ahref.innerHTML = "See Comment(s)";
                obj.style.display = "none";
                }
           }
       }
       function hideObj(obj) {
           obj.style.display = "none";
       }
       
       $(document).ready(function()
{

$(window).scroll(function(){
if ($(window).scrollTop() >= ($(document).height() - $(window).height())*0.75){
    $('#load-more').click();
}
}); 
});
    </script>

    <!--[if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->
  </head>
  <body onload="highlighter('<?php echo $str;?>'); prepareAjax(); countItems();">
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
    
    <div id="divSearch" class="clearfix" align="center">
        <div style="float: left; ">
            <form id="frmSearch">
            <h1>Your search here:</h1>
                <input type="text" id="txtSearch" name="txtSearch" value="<?php echo $str; ?>"/><br/>
                <!--From: <input type="text" id="dateFrom" name="dateFrom" class="jdpicker"/>
                To: <input type="text" id="dateTo" name="dateTo" class="jdpicker"/> -->
                <input type="submit" value="Search"/>
            </form>
        </div>
        <div style="float: right; display: none;">
            <a id="ana" class="button-pretty" href="helpers/analytic.php">Analytics</a>
        </div>
    </div>

    <section id="get-started">
      <p>Kami's Social Search</p>
    </section>
    <br/>
    <div class="divPosts">
        <div class="lbl" align="center">Facebook posts</div>
        <br/>
        <div class="posts">
          <?php
          $arrCsv = array();
          if(isset($str) && $str!= "") {
            foreach ($pubPosts as $post) {
              // Extract the pieces of info we need from the requests above
              $item_count++;
              $id = idx($post, 'id');
              $msg = idx($post, 'message');
              $from = idx($post, 'from');
              $name = $from['name'];
              $uid = $from['id'];
              $ctime = idx($post, 'created_time');
              $link = idx($post, 'link');
              $type = idx($post, 'type');
              $time = timeToInterval(new DateTime($ctime));
              // extract User info
              $uinfo = $facebook->api('/'. $uid) . '?format=json';
              $username = idx($uinfo, 'username');
              $gender = idx($uinfo, 'gender'); $gender = $gender?$gender:"Private";
              $location = idx($uinfo, 'location');
              $add = $location['name'] . " " . $location['street'] . " ". $location['city'] . " ". $location['country'];
              $add = str_replace("   ", "", $add);
              $add = $add?$add:"Private";
              $locale = idx($uinfo, 'locale'); $locale = $locale?$locale:"Private";
              // append *More* hyperlink
              //$msg .= '<br/><a class="see-more" onclick="seeMore(\''. $id .'\', this)">See Comment(s)</a><br/>';
              $msg_more = '<br/><a href="javascript:;" class="see-more" onclick="seeMore(\''. $id .'\', null, \'1\', this)">See Comment(s)</a>';
            
          //$a = array (  'id' => $id, 'msg' => $msg, 'type' => $type, 'created_at' => $ctime, 'uid' => $uid, 'name' => $name, 'username' => $username, 'link' => 'https://fb.com/'.$uid, 'gender' => $gender, 'location' => $add, 'locale' => $locale);
          //$arrCsv[] = $a;
          //$access_token = AppInfo::getToken();
          //$request_url ="https://graph.facebook.com/".$id."/comments?" . $access_token;
          ?>
          <div class="post">
            <div class="uinfo">
                <div class="post-pic"><img src="https://graph.facebook.com/<?php echo $uid; ?>/picture"></div>
                <a href="https://www.facebook.com/<?php echo $id; ?>"><?php echo $item_count . ") " . $name; ?>:</a><br/>
                <label class="lblhdr">Username</label>: <?php echo $username;?><br/>
                <label class="lblhdr">Gender</label>: <?php echo $gender;?><br/>
                <label class="lblhdr">Location</label>: <?php echo $add;?><br/>
                <label class="lblhdr">Language</label>: <?php echo $locale;?><br/>
            </div>
            <span class="time">Published <?php echo $time; ?></span><br />
            <a class="post-link" href="<?php echo $link;?>"><?php echo $link;?></a><br /><?php echo strip_tags( $msg, '<b><a><ul><li><ol><em>' ); echo $msg_more;?>
            <div id="<?php echo $id;?>"></div>
          </div>
          <?php
            }
          }
          ?>
          <input type="hidden" id="hidCountFb1" name="hidCountFb1" value="<?php echo $item_count;?>">
        </div>
    </div>
    
    <?php
    if ($str != "") {
        require('./helpers/twitter.class.php');
        $twitter = new twitter_class();
        $arrOutput = $twitter->getTweets(($str), 10, $since_id, $max_id, $dataPath);
        echo $arrOutput;
    }
    ?>
    
    <div style='display: <?php echo ($str!="" && 1!=1)?"":"none;"; ?>'>
        <form id="frmPaging" name="frmPaging" method="post">
            <input type="hidden" id="hidSince" name="hidSince" value="" />
            <input type="hidden" id="hidMax" name="hidMax" value="" />
            <input type="hidden" id="hidLink" name="hidLink" value="" />
            <input type="hidden" id="hidLinkNext" name="hidLinkNext" value="<?php echo $paging['next'];?>" />
            <input type="submit" onclick="document.getElementById('hidLink').value = ('<?php echo $paging['previous'];?>');document.getElementById('hidSince').value = document.getElementById('hidSinceId').value;" value="Newer" />
            <input type="submit" onclick="document.getElementById('hidLink').value = ('<?php echo $paging['next'];?>');document.getElementById('hidMax').value = document.getElementById('hidMaxId').value;" value="Older" />
        </form>
    </div>
    
    <!-- Posts go inside this DIV -->
    <div id="ajax-posts" class="div-fb-post">
    </div>
    
    <!-- Widget XHTML Starts Here -->
    <div id="posts-container" style='display: <?php echo $str!=""?"":"none;"; ?>'>
        <!-- Load More "Link" -->
        <div id="load-more">Load More</div>
    </div>
    <!-- Widget XHTML Ends Here -->
  </body>
</html>