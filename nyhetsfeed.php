<?php
header("Content-type: text/xml");

#$xml=join('', file('https://www.google.no/alerts/feeds/03561379419363822400/14770639297668427748'));

$rss='https://news.google.com/news/rss/search/section/q/(leteaksjon%20AND%20%22R%C3%B8de%20Kors%22%20)OR%20%22R%C3%B8de%20Kors%20hjelpekorps%22%20OR%20hjelpekorps%20OR%20redningshunder%20OR%20%22savnet%20siden%22/(leteaksjon%20AND%20%22R%C3%B8de%20Kors%22%20)OR%20%22R%C3%B8de%20Kors%20hjelpekorps%22%20OR%20hjelpekorps%20OR%20redningshunder%20OR%20%22savnet%20siden%22?hl=no&ned=no_no';

$xml=join('', file($rss)); 

$xml=simplexml_load_string($xml);


print "<feed xmlns='http://www.w3.org/2005/Atom' xmlns:idx='urn:atom-extension:indexing'>
<id>".$xml->id."</id>
<title>
".$xml->title."
</title>
<link href='https://www.google.com/alerts/feeds/03561379419363822400/14770639297668427748' rel='self'/>
<updated>".$xml->updated."</updated>";

foreach($xml->channel->item as $k) {
	
	$link=$k->link; 
	$tags = getUrlData($link);

	$tags=array_merge($tags['metaProperties'], $tags['metaTags']);
	
#	print_r($k);
	
#	print_r($tags);
#	exit;
	$img=htmlentities($tags['og:image']['value']);
	$title=htmlentities(htmlentities(html_entity_decode($tags['og:title']['value'])));
	$content=htmlentities(htmlentities(html_entity_decode($tags['og:description']['value'])));
	
	$published=$k->pubDate;
	
	# old news is old news: hide
	if(time()-strtotime($published)>60*60*24){
		continue;
	}
	
	$updated=$k->updated;
  #$id=$k->guid;
	$id=preg_replace('/[^0-9]/', '', $k->link);
	if($id<1000){
		$id=preg_replace('/[^0-9]/', '', md5($k->link));
	}

	if(strpos($img, 'default.jpg') or strpos($img, 'square_logo.jpg')) {
		$img='';
	}else{
		$img="&lt;img src=&quot;$img&quot;&gt;";
	}
	
		
	print "
<entry>
<id>$id</id>
<title type='html'>$img $title</title>
<link href='$link'/>
<published>$published</published>
<updated>$updated</updated>
<content type='html'>$content</content>
<author>
</author>
</entry>
";
	
	
	
 }




function getUrlData($url, $raw=false) // $raw - enable for raw display
{
    $result = false;
   
    $contents = getUrlContents($url);

    if (isset($contents) && is_string($contents))
    {
        $title = null;
        $metaTags = null;
        $metaProperties = null;
       
        preg_match('/<title>([^>]*)<\/title>/si', $contents, $match );

        if (isset($match) && is_array($match) && count($match) > 0)
        {
            $title = strip_tags($match[1]);
        }
       
        preg_match_all('/<[\s]*meta[\s]*(name|property)="?' . '([^>"]*)"?[\s]*' . 'content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $contents, $match);
       
        if (isset($match) && is_array($match) && count($match) == 4)
        {
            $originals = $match[0];
            $names = $match[2];
            $values = $match[3];
           
            if (count($originals) == count($names) && count($names) == count($values))
            {
                $metaTags = array();
                $metaProperties = $metaTags;
                if ($raw) {
                    if (version_compare(PHP_VERSION, '5.4.0') == -1)
                         $flags = ENT_COMPAT;
                    else
                         $flags = ENT_COMPAT | ENT_HTML401;
                }
               
                for ($i=0, $limiti=count($names); $i < $limiti; $i++)
                {
                    if ($match[1][$i] == 'name')
                         $meta_type = 'metaTags';
                    else
                         $meta_type = 'metaProperties';
                    if ($raw)
											${$meta_type}[trim($names[$i])] = array (
                            'html' => htmlentities($originals[$i], $flags, 'UTF-8'),
                            'value' => $values[$i]
                        );
                    else
											${$meta_type}[trim($names[$i])] = array (
                            'html' => $originals[$i],
                            'value' => $values[$i]
                        );
                }
            }
        }
       
        $result = array (
            'title' => $title,
            'metaTags' => $metaTags,
            'metaProperties' => $metaProperties,
        );
    }
   
    return $result;
}

function getUrlContents($url, $maximumRedirections = null, $currentRedirection = 0)
{
    $result = false;
   
    $contents = @file_get_contents($url);
   
    // Check if we need to go somewhere else
   
    if (isset($contents) && is_string($contents))
    {
        preg_match_all('/<[\s]*meta[\s]*http-equiv="?REFRESH"?' . '[\s]*content="?[0-9]*;[\s]*URL[\s]*=[\s]*([^>"]*)"?' . '[\s]*[\/]?[\s]*>/si', $contents, $match);
       
        if (isset($match) && is_array($match) && count($match) == 2 && count($match[1]) == 1)
        {
            if (!isset($maximumRedirections) || $currentRedirection < $maximumRedirections)
            {
                return getUrlContents($match[1][0], $maximumRedirections, ++$currentRedirection);
            }
           
            $result = false;
        }
        else
        {
            $result = $contents;
        }
    }
   
    return $contents;
}




?>
</feed>
