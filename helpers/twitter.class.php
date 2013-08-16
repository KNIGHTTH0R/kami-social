<?php
/*
 * The art of Web
 * 
 * @author TAOW http://www.the-art-of-web.com/php/twitter/
 * @ref http://www.richnetapps.com/php-twitter-search-parser/
 * @ref https://github.com/ryanfaerman/php-twitter-search-api
 */
class twitter_class
{	
	function twitter_class()
	{
		$this->realNamePattern = '/\((.*?)\)/';
		$this->searchURL = 'http://search.twitter.com/search.atom?lang=en&page=1&rpp=15&q=';
		
		$this->intervalNames   = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year');
		$this->intervalSeconds = array( 1,        60,       3600,   86400, 604800, 2630880, 31570560);
		
		$this->badWords = array('somebadword', 'anotherbadword');
	}
    
    function updateUrl($p = 1, $l = 15) {
        if($p == "") $p = 1;
        $this->searchURL = 'http://search.twitter.com/search.atom?lang=en&page='. $p .'&rpp='. $l .'&q=';
    }

	function getTweets($q, $limit=10, $since_id='0', $max_id='0', $dataPath="/tmp/", $page = '')
	{
	    try{
            $count = intval($page);
            $this->updateUrl($count);
            $count = $count>1?$count-1:0;
            
    		$output = '';
    
    		// get the seach result
    		$max_id = $max_id=='0'?'':'&max_id=' . $max_id;
    		$since_id = $since_id=='0'?'':'&since_id=' . $since_id;
    		$ch= curl_init($this->searchURL . urlencode($q) . $since_id . $max_id);
    
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    		$response = curl_exec($ch);
        } catch (exception $e) {var_dump($e->getMessage());} 

        try{
    		if ($response !== FALSE)
    		{
    			$xml = simplexml_load_string($response);
                $fp = fopen($dataPath. urlencode($q) . "_tw_page" . ($count+1), 'w');
                fwrite($fp, serialize($response));
                fclose($fp);
                
                //$fp = fopen("sample_tw_data.xls", 'w');
                //fputcsv($fp, (array)"\xEF\xBB\xBF");
                //foreach ($xml as $line) {
                    //fputcsv($fp, $line);
                //}
                //fclose($fp);die();
    	
    			$tweets = 0;
    			$total = count($xml->entry);
                if ($total>0) {
                    $output = '<div class="lbl" align="center">Twitter posts';
                    $output .= ($page==""?"":" | PAGE ".$page);
                    $output .= '</div>';    
                }
                else $output = '';
    			for($i=0; $i<$total; $i++)
    			{
    				$crtEntry = $xml->entry[$i];
    				$account  = $crtEntry->author->uri;
                    $uid      = substr($crtEntry->id, strpos($crtEntry->id, ",") + 6);
    				$image    = $crtEntry->link[1]->attributes()->href;
    				$tweet    = $crtEntry->content;
    	
    				// skip tweets containing banned words
    				/*
    				$foundBadWord = false;
    				foreach ($this->badWords as $badWord)
    				{
    					if(stristr($tweet, $badWord) !== FALSE)
    					{
    						$foundBadWord = true;
    						break;
    					}
    				}*/
    				
    				$tweet = str_replace('<a href=', '<a target="_blank" href=', $tweet);
    				
    				// skip this tweet containing a banned word
    				//if ($foundBadWord)
    				//	continue;
    
    				// don't process any more tweets if at the limit
    				if ($tweets==$limit)
    					break;
    				$tweets++;
    	
    				// name is in this format "acountname (Real Name)"
    				preg_match($this->realNamePattern, $crtEntry->author->name, $matches);
    				$name = $matches[1];
    	
    				// get the time passed between now and the time of tweet, don't allow for negative
    				// (future) values that may have occured if server time is wrong
    				$time = 'just now';
    				$secondsPassed = time() - strtotime($crtEntry->published);
    
    				if ($secondsPassed>0)
    				{
    					// see what interval are we in
    					for($j = count($this->intervalSeconds)-1; ($j >= 0); $j--)
    					{
    						$crtIntervalName = $this->intervalNames[$j];
    						$crtInterval = $this->intervalSeconds[$j];
    							
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
                    $hiddenMaxId = $hiddenSinceId = '';
    				if ($i == $limit-1) {
                        $hiddenMaxId = '<input type="hidden" id="hidMaxId'. $page .'" name="hidMaxId'. $page .'" value="'. $uid .'" >';
                        $max_id = $uid;
                    }
                    if ($i == 0) {
                        $hiddenSinceId = '<input type="hidden" id="hidSinceId" name="hidSinceId'. $page .'" value="'. $uid .'" >';
                        $since_id = $uid;
                    }
    				$output .= '
    				<div class="tweet post clearfix">
    					<div class="avatar">
    						<a href="' . $account . '" target="_blank"><img src="' . $image .'"></a>
    					</div>
    					<div class="post-content">
    						<span class="author"><a href="' . $account . '"  target="_blank">' . ($count*$limit + $i + 1)  . ") " . $name .'</a></span>: ' . 
    						'<span class="time">' . $time . '</span><br />'.
    						strip_tags( $tweet, '<b><a><ul><li><ol><em>' ) . 
    						$hiddenMaxId. $hiddenSinceId.
    					'</div>
    				</div>';
    			}
                $page = $page==''?'1':$page;
                $output .= '<input type="hidden" id="hidCountTw'. $page .'" name="hidCountTw'. $page .'" value="'. $tweets .'" >';
    		}
    		else
    			$output = '<div class="tweet"><span class="error">' . curl_error($ch) . '</span></div>';
		} catch (exception $e) {var_dump($e->getMessage());}
        
		curl_close($ch);
        //$arr = array('tw' => $output, 'twmaxid' => $max_id, 'fbpaging' => '');
		return $output;
	}
}

?>
