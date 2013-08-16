<?php

/**
 * @return the value at $index in $array or $default if $index is not set.
 */
function idx(array $array, $key, $default = null) {
  return array_key_exists($key, $array) ? $array[$key] : $default;
}

function timeToInterval(DateTime $t) {
    $intervalNames   = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year');
    $intervalSeconds = array( 1,        60,       3600,   86400, 604800, 2630880, 31570560);
    $strTime = $t;
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
    return $time;
}

function he($str) {
  return htmlentities($str, ENT_QUOTES, "UTF-8");
}

function processArchive($str) {
    try{
    $listFiles = getFileNameList($str);
    $listFbFiles = $listFiles[0];
    $listTwFiles = $listFiles[1];
    $fbdata = array();
        
    // FB
    foreach ($listFbFiles as $file) {
        $data = file_get_contents($file);
        $fbtemp = unserialize($data);
        //var_dump($fbdata);die();
        $total = count($fbtemp);
        foreach ($fbtemp as $key => $value) {
            $arrPost = array(
                        'uid'       => $value['from']['id'],
                        'src'       => "facebook",
                        'profile'   => "http://fb.com/".$value['id'], 
                        'name'      => $value['from']['name'], 
                        'avatar'    => "https://graph.facebook.com/". $value['id'] ."/picture", 
                        'content'   => strip_tags( $value['message'], '<b><a><ul><li><ol><em>' ),
                        'date'      => $value['created_time'],
                        'entities'  => $value['link'],
                        );
            $fbdata[] = $arrPost;
        }
    }
    //var_dump($fbdata);die();
    // TW
    try{
    foreach ($listTwFiles as $file) {
        try{
        $data = file_get_contents($file);
        $data = unserialize($data);
        $xml = simplexml_load_string($data);
        } catch (Exception $e) {var_dump($e->getMessage());}
        $fp = fopen("asd.xml", 'w');
        fwrite($fp, $data);
        fclose($fp);
        
        $tweets = 0;
        $realNamePattern = '/\((.*?)\)/';
        //$twdata = array();
        //$fbdata = array();
        $total = count($xml->entry);
        for($i=0; $i<$total; $i++)
        {
            $crtEntry = $xml->entry[$i];
            $account  = $crtEntry->author->uri;
            $uid      = substr($crtEntry->id, strpos($crtEntry->id, ",") + 6);
            $image    = ($crtEntry->link[1]->attributes()->href);
            $tweet    = $crtEntry->content;
            
            $tweet = str_replace('<a href=', '<a target="_blank" href=', $tweet);
            
            // don't process any more tweets if at the limit
            //if ($tweets==$limit)
            //    break;
            $tweets++;
        
            // name is in this format "acountname (Real Name)"
            preg_match($realNamePattern, $crtEntry->author->name, $matches);
            $name = $matches[1];
            
            $arrTweet = array(
            'uid'       => $uid,
            'src'       => "twitter",
            'profile'   => (string)$account, 
            'name'      => $name, 
            'avatar'    => (string)$image, 
            'content'   => strip_tags( $tweet, '<b><a><ul><li><ol><em>' ),
            'date'      => (string)$crtEntry->published,
            'entities'  => NULL,
            );
            //$twdata[] = $arrTweet;
            $fbdata[] = $arrTweet;
        }
    }
    } catch (Exception $e) {var_dump($e->getMessage());}

    // WRITE NEW FILE
    $fp = fopen("sampleData_".urlencode($str).".json", 'w');
    $rslt = fwrite($fp, json_encode($fbdata));
    $items = count($fbdata);
    $stt = $items>0?"SUCCESS":"FAILED";
    fclose($fp);
    
    echo "Processed archived data for search results of keyword <b>".$str."</b>.<br />";
    echo "<br />STATUS: ".$stt. " (return code: ".$rslt.")";
    echo "<br />ITEMS : ".$items;
    } catch (Exception $e) {var_dump($e->getMessage());}
}

function getFileNameList($str) {
    if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
        $PATH = "/tmp/";
    } else {$PATH = "c:\\folder\\";}
    $str = urlencode($str);
    //$PATH = "c:\\folder\\";
    $FBTAG = "_fb_page";
    $TWTAG = "_tw_page";
    $arrFb = array();
    $arrTw = array();
    $arr = array();
    $i = 1;
    $flag = true;
    while($flag) {
        $filename = $PATH . $str . $FBTAG . $i;
        if (file_exists($filename)) {
            $arrFb[] = $filename;
            $i++;
        } else $flag = false;
    }
    $arr[] = $arrFb;
    $i = 1;
    $flag = true;
    while($flag) {
        $filename = $PATH . $str . $TWTAG . $i;
        if (file_exists($filename)) {
            $arrTw[] = $filename;
            $i++;
        } else $flag = false;
    }
    $arr[] = $arrTw;
    return $arr;
}
/*
 * Analytics
 */
function array_icount_values($array) {
    $ret_array = array();
    foreach($array as $value) $ret_array[strtolower($value)]++;
    return $ret_array;
}
function strip_junk($str) {
    //$str = "Tôi là một con mèo già khằng khú đế hế hế hễ hề hê hệ";
    $pattern = array(
        "/[^a-zA-Z ﻿ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ]+/",
        "/ my /i", "/ has /i", "/ it /i", "/ a /i", "/ that /i", "/ at /i", "/The /", "/ the /i", "/An /", "/I /", "/ an /i", "/ i /i", "/ about /", "/ of /i", "/ with /i", "/ but /i", "/ ve /i", "/ ll /i", "/ d /i",
        "/ such /i", "/ s /i", "/ just /i", "/ nt /i", "/ and /i", "/ to /i", "/ in /i", "/ from /i", "/ th /i", "/ le /i", "/ we /i", "/ few /i", "/ your /i", "/ yours /i", "/ however /i", "/However /",
        "/ be /i", "/ is /i", "/ are /i", "/ was /i", "/ were /i", "/ will /i", "/ would /i", "/ should /i", "/ could /i", "/ can /i", "/ do /i", "/ does /i", "/ did /i",
        "/https*/", "/hrefs*/", "/ com /",
        "@\\b[a-z]\\b ?@i", "@\\b[a-z][a-z]\\b ?@i",
    );
    //$pattern = array("/[^a-zA-Z ]+/");
    $replace = " ";
    $subject = $str;
    
    return (preg_replace($pattern, $replace, $subject));
}
function merge_word_count_array($array1, $array2) {
    //$array1 = array_icount_values(str_word_count($str, 1));
    //$array2 = array_icount_values(str_word_count($str2, 1));
    $arr = array_merge_recursive($array2, $array1);
    foreach ($arr as $key => $item) {
        if(gettype($item) == "array") {
            $arr[$key] = array_sum($item);
        }
    }
    arsort($arr);
    return $arr;
}
 /*
 * Facebook data processing
 */