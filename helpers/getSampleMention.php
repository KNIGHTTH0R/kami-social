<?php
require_once('utils.php');
$string = file_get_contents("sampleData_".$_GET["str"].".json");
//var_dump($string);die();
$iFbCount = substr_count($string, 'src":"facebook');
$data = json_decode($string);
//var_dump($data);die();
$arrSample = array_slice($data, 0, 5);
$arrSample = array_merge($arrSample, array_slice($data, $iFbCount, 5));
var_dump($arrSample);die();

// Prepare the json data

/*
$volume = array();
$words = array();
$c = 1;
foreach ($data as $key => $item) {
    if ($item->src == "facebook") {
        $date = new DateTime($item->date);
        $date = $date->format('Y,m,d,H');
        if (!isset($volume[(string)$date])) $volume[$date] = array("fb" => 0, "tw" => 0);
        $volume[$date]['fb'] += 1;
        // word count (for TermCloud)
        $str = strip_junk($item->content);
        $array2 = array_icount_values(str_word_count($str, 1));
        $words = merge_word_count_array($words, $array2);
    } else {
        $date = new DateTime($item->date);
        $date = $date->format('Y,m,d,H');
        if (!isset($volume[(string)$date])) $volume[$date] = array("fb" => 0, "tw" => 0);
        $volume[$date]['tw'] += 1;
        // word count (for TermCloud)
        $str = strip_junk($item->content);
        $array2 = array_icount_values(str_word_count($str, 1));
        $words = merge_word_count_array($words, $array2);
    }
}

$temp = array();
ksort($volume);
foreach ($volume as $key => $value) {
    $a = array($key, $value['fb'], $value['tw']);
    $temp[] = $a;
}
//$return = array($temp, $words);
//$jsn = json_encode($return);
$tmpWords = array_slice($words, 0, 20);
$temp[] = $tmpWords;
$jsn = json_encode($temp);
echo ($jsn);
*/
 */