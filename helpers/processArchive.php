<?php
// This provides access to helper functions defined in 'utils.php'
try{
    require_once('utils.php');
    //$str = urlencode("starbuck vn");
    $str = ("starbuck vn");
    processArchive($str);
    //$data = file_get_contents("sampleData_".$str.".json");
    //var_dump(json_decode($data));die();
    //$arr = json_decode($data);
    
    //$fp = fopen("exportData_".$str.".csv", 'w');
    //fputcsv($fp, array("ID", "Profile", "Fullname", "Avatar link", "Content", "Created on", "Digital Entities"));
    //foreach ($arr as $i => $a) {
    //    fputcsv($fp, (array)$a);
    //}
    //fclose($fp);
} catch (Exception $e) {var_dump($e->getMessage());}   
?>