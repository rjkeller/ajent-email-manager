<pre><?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
function getIcon($website)
{
    $doc = new DOMDocument();
    @$doc->loadHTML(file_get_contents($website));
    $xml = @simplexml_import_dom($doc); // just to make xpath more simple
    if (!$xml)
        return "";
    $links = $xml->xpath('//link');

    $link = "";
    foreach ($links as $link)
    {
        $attrs = $link->attributes();
        if ($attrs['rel'] == "apple-touch-icon")
        {
            $link = $attrs['href'];
            break;
        }
        if ($attrs['rel'] == "shortcut icon")
            $link = $attrs['href'];
    }
    if ($link == "")
        $link = $website ."/favicon.ico";

    $imgs = $xml->xpath('//img');
    foreach ($imgs as $img)
    {
        $attrs = $img->attributes();
        if (strpos($attrs['src'], "logo") !== false)
        {
            $link = $attrs['src'];
            break;
        }
    }

    if (substr($link, 0, 3) == "../")
        $link = $website . substr($link, 2);
    else if (substr($link, 0, 7) != "http://")
        $link = $website . $link;

    echo "<img src='". $link ."' width='130' style='max-height: 70px'>";
}

if (isset($_GET['q']))
	getIcon($_GET['q']);
?>

<form action="/" method="get">
<input type="text" name="q">
</form>
