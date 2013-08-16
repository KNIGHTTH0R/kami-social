<?php
  if(!isset($_GET['q']) || !$q = $_GET['q']) $q = 'vinaresearch';

  $NUMITEMS   = 20;
  $BLOGURL    = "http://search.twitter.com/search.rss?q=" . urlencode($q) . "&rpp=$NUMITEMS";
  $TIMEFORMAT = "j F Y, g:ia";
  //$CACHEFILE  = "/tmp/" . md5($BLOGURL);
  $CACHEFILE  = "c:\\folder\\resource";
  $CACHETIME  = 0.5; // hours

  # Original PHP code by Chirp Internet: www.chirp.com.au
  # Please acknowledge use of this code by including this header.

  function expandLinks(&$input)
  {
    /*
    // links matching the following regular expressions will be checked for redirects and expanded
        $domains = array(
          '[a-z0-9]{2,3}\.[a-z]{2}', '[a-z]{3,4}(url|ly)\.com'      
        );
        if(preg_match_all("@http://((" . implode("|", $domains) . ")/[-a-z0-9]+)@i", $input, $matches)) {
          $matches = array_unique($matches[1]);
          foreach($matches as $shorturl) {
            $command = "curl --head " . escapeshellarg($shorturl) . " | awk '($1 ~ /^Location/){print $2}'";
            if($expandedurl = exec($command)) {
              $input = str_replace("http://$shorturl", htmlspecialchars($expandedurl), $input);
            }
          }
        }*/
  }

  function updateFeed()
  {
    global $BLOGURL, $CACHEFILE;

    ini_set('user_agent', "TheArtOfWeb (http://{$_SERVER['HTTP_HOST']})");
    if($feed_contents = file_get_contents($BLOGURL)) {
      # expand shortened urls
      expandLinks($feed_contents);

      # write feed contents to cache file
      try{
      $fp = fopen($CACHEFILE, 'w');
      fwrite($fp, $feed_contents);
      fclose($fp);
      } catch (Exception $e) { var_dump($e->getMessage() );}
    }
  }

  echo "<p>Read the latest tweets mentioning <b>$q</b> as of ";
  if(file_exists($CACHEFILE)) {
    echo date('g:ia', filemtime($CACHEFILE)) . ' local time';
  } else {
    echo 'right now';
  }
  echo ":</p>\n\n";

  # download the feed iff cached version is missing
  if(!file_exists($CACHEFILE)) updateFeed();

  include "class.myrssparser.php";
  $rss_parser = new myRSSParser($CACHEFILE);

  # read feed data from cache file
  $feeddata = $rss_parser->getRawOutput();
  extract($feeddata['RSS']['CHANNEL'][0], EXTR_PREFIX_ALL, 'rss');

  # display feed items
  if($rss_ITEM) {
    echo "<table border=\"0\" cellpadding=\"5\" cellspacing=\"0\">\n";
    foreach($rss_ITEM as $itemdata) {
      preg_match("/^(.*)@twitter\.com \((.*)\)$/", $itemdata['AUTHOR'], $regs);
      list($foo, $author, $name) = $regs;
      echo "<tr>\n";
      echo "<td><a title=\"$name\" href=\"http://twitter.com/$author\" target=\"_blank\"><img src=\"{$itemdata['GOOGLE:IMAGE_LINK']}\" width=\"48\" height=\"48\" border=\"0\" alt=\"$author\"></a></td>\n";
      echo "<td><p style=\"width: 600px; overflow: auto;\"><a href=\"http://twitter.com/$author\" target=\"_blank\">$author</a>: ";
      echo str_replace('<a ', '<a rel="nofollow" target="_blank" ', stripslashes($itemdata['DESCRIPTION']));
      echo "<br>\n";
      echo "<small><a style=\"text-decoration: none; color: inherit;\" href=\"{$itemdata['GUID']}\" target=\"_blank\">";
      echo date($TIMEFORMAT, strtotime($itemdata['PUBDATE']));
      echo "</a></small>";
      echo "</p></td>\n";
      echo "</tr>\n";
    }
    echo "</table>\n\n";
  }
?>

<?php
  // download the feed iff cached version is too old
  if((time() - filemtime($CACHEFILE)) > 3600 * $CACHETIME) {
    flush();
    updateFeed();
  }
?>