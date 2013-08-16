<?php
//require_once('utils.php');

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




if (isset($_GET["str"])) {
    $file = "sampleData_".$_GET["str"].".json";
} elseif (isset($_POST["str"])) {
	$file = "sampleData_".$_POST["str"].".json";
} else $file = "sampleData_wws.json";
if (!file_exists($file)) {
    $tmp = "/tmp/" . $file;
    if (!file_exists($tmp)) {
        $file = "../helpers/" . $file;
    }
}
$string = file_get_contents($file);
$data = json_decode($string);
$volume = array();
$words = array();
$master_str = array();
foreach ($data as $key => $item) {
	if ($item->src == "facebook") {
	    $date = new DateTime($item->date);
        $date = $date->format('Y,m,d,H');
        if (!isset($volume[(string)$date])) $volume[$date] = array("fb" => 0, "tw" => 0);
        $volume[$date]['fb'] += 1;
        // word count (for TermCloud)
        $str = strip_junk($item->content);
        $master_str[] = $str;
        $array2 = array_icount_values(str_word_count($str, 1));
        $words = merge_word_count_array($words, $array2);
	} else {
	    $date = new DateTime($item->date);
        $date = $date->format('Y,m,d,H');
        if (!isset($volume[(string)$date])) $volume[$date] = array("fb" => 0, "tw" => 0);
        $volume[$date]['tw'] += 1;
        // word count (for TermCloud)
        $str = strip_junk($item->content);
        $master_str[] = $str;
        $array2 = array_icount_values(str_word_count($str, 1));
        $words = merge_word_count_array($words, $array2);
	}
}
$returnArr = array();
ksort($volume);
foreach ($volume as $key => $value) {
	$a = array($key, $value['fb'], $value['tw']);
    $returnArr[] = $a;
}
$tmpWords = array_slice($words, 0, 20);
$returnArr[] = $tmpWords;
$tmpWordCloud = array("data" => $master_str);
$returnArr[] = $tmpWordCloud;
$jsn = json_encode($returnArr);
echo ($jsn);