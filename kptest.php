<?php
        // $str = "evernote";
        // require('../helpers/twitter.class.php');
        // $twitter = new twitter_class();
        // //echo $twitter->getTweets(($str), 10, $since_id, $max_id);
        // $arrOutput = $twitter->getTweets(($str), 15, $since_id, $max_id, $dataPath);
        // echo $arrOutput;
        /*require_once('utils.php');        
$str = "Evernote has changed my life. Such a simple concept! At first tenthe I thought it was just a bit of a gimmick, but once you get a system going it's feking rad. It does the job of the part of my brain that I've always felt was missing.";
$str2 = "The wait is almost over, the agenda is just about final and agenda and registration details will be available in a few days. However, to wet your appitite, we can confirm that the conference (provided minimum numbers are reached) will be at Warwick Le Lagon Resort and Spa in Port Vila, Vanuatu Thursday 6th June to Saturday 8th June 2013 Some agenda items are: - How To Manage and Solve Legal Problems – Sundip Ghedia from WyndhamPrem - Risks operating in Current Market – Robert Moodie from Rodgers Reidy - MYOB Update - Dave Wilson from MYOB - Gadgets to Improve Productivity ie Shoebox and Evernote – Leanne Berry and Maria Landrelli - End of Year Payroll & Reportable Contractors Summaries – Maria Landrelli - All Teched Up - Jason Spence iassist";
$str = strip_junk($str);
echo $str;
$str2 = strip_junk($str2);
echo $str2; die();
//$array1 = array('a' => 100,'b' => 7,'c' => 200);
//$array2 = array('a' => 100,'d' => 7, 'c' => 200, 'e' => 7);
$array1 = array_icount_values(str_word_count($str, 1));
$array2 = array_icount_values(str_word_count($str2, 1)); 
//$arr = array_merge_recursive($array2, $array1);

$arr = merge_word_count_array($array1, $array2);

var_dump($arr);
         
     $str = "mưa"; 
     $str = urlencode($str);
      if ($str != "") {
        try{
            require('./helpers/twitter.class.php');
            $twitter = new twitter_class();
            $output.= $twitter->getTweets(($str), 10, $since_id, $max_id, $dataPath, $_POST['page']);
            var_dump($str);
            echo ($output);
        } catch (exception $e) {var_dump($e->getMessage());}
    }  
         */
      require_once('./helpers/AppInfo.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
} elseif ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
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
echo "神";
$_REQUEST['txtSearch'] = "starbuck vn";
if(isset($_REQUEST['txtSearch'])) $str = strip_tags( ($_REQUEST['txtSearch']), '<b><a><ul><li><ol><em>' );
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
        $unixtime = strtotime("2013-01-30");
        echo "Unix: ";
        var_dump($unixtime);
        // Until: Date_of_results < Until_date
        // Since: Date_of_results > since_date (latest till since_date)
        $link = '/search?limit=10&locale=vi_VN&type=post&fields=id,message,from,link,picture,type&q=' . urlencode($str) . '&since=' . $unixtime;
        //$data = ($facebook->api($link));
    } else {
    }
    $data = ($facebook->api($link));
    $pubPosts = idx($data, 'data', array());
    var_dump($pubPosts);die();
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