<?php
header("Content-type: text/xml");

$xml=join('', file('https://news.google.com/news/rss/search/section/q/leteaksjon%20OR%20%22R%C3%B8de%20Kors%20hjelpekorps%22%20OR%20hjelpekorps%20OR%20redningshunder%20OR%20%22savnet%20siden%22/leteaksjon%20OR%20%22R%C3%B8de%20Kors%20hjelpekorps%22%20OR%20hjelpekorps%20OR%20redningshunder%20OR%20%22savnet%20siden%22?hl=en&ned=us'));
$xml=simplexml_load_string($xml);

print "<feed xmlns='http://www.w3.org/2005/Atom' xmlns:idx='urn:atom-extension:indexing'>
<id>".$xml->id."</id>
<title>
".$xml->title."
</title>
<link href='https://www.google.com/alerts/feeds/03561379419363822400/14770639297668427748' rel='self'/>
<updated>".$xml->updated."</updated>";

foreach($xml->entry as $k) {

    $link=preg_replace('/.+?url=(.+?)\&.*/', '\\1', $k->link->Attributes());
    $title=html_entity_decode(strip_tags($k->title));
    $content=html_entity_decode(strip_tags($k->content));
    $published=$k->published;
    $updated=$k->updated;
  $id=$k->id;

    $html=join('', file($link));
    list($html)=explode('</head>', $html); 
    $img=preg_replace('/.+?<meta property="og:image" content="(.+?)".*/s', '\\1', $html);
    $img=htmlentities(preg_replace('/.+?<meta property=og:image content="(.+?)".*/s', '\\1', $img));
    $img=str_replace('https://presizely.abcmedia.no/972x,q75,prog,adsh/', '', $img);

        
    print "
<entry>
<id>$id</id>
<title type='html'>&lt;img src=&quot;$img&quot;&gt;$title</title>
<link href='$link'/>
<published>$published</published>
<updated>$updated</updated>
<content type='html'>$content</content>
<author>
</author>
</entry>
";
    

    
 }

?>
</feed>

