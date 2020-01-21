#!/usr/bin/php
<?php /* Traffic Genorator by Andrew B. */ ?>
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* ___________              _____  _____.__                   ________                 *
* \__    ___/___________ _/ ____\/ ____\__| ____            /  _____/  ____   ____    *
*   |    |  \_  __ \__  \\   __\\   __\|  |/ ___\   ______ /   \  ____/ __ \ /    \   *
*   |    |   |  | \// __ \|  |   |  |  |  \  \___  /_____/ \    \_\  \  ___/|   |  \  *
*   |____|   |__|  (____  /__|   |__|  |__|\___  >          \______  /\___  >___|  /  *
*                       \/                     \/                  \/     \/     \/   *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
<?php
error_reporting(0);
require('traffic_files/ua.php');

# URL declaration
$url = cin('* Enter the URL: ');

# Referer Declaration
$ref = cin('* Referer URL: ');

# Times to visit Declaration
$times  = cin('* Number of times to visit: ');

$origeonal = $times;
$num       = 1;
$good      = 0;
$bad       = 0;
echo "* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\n";
echo "* Firing the lasers...                                                                *\n";
echo "* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\n";
while($times != 0) {
	$ua      = random_user_agent();
	$proxy   = rand_proxy();
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_PROXY, $proxy);
	curl_setopt($curl, CURLOPT_USERAGENT, $ua);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($curl);
	curl_close($curl);
	if($result) {
		$str = "* $num. $proxy";
		$c   = 86 - strlen($str);
		while($c != 0) {
			$str = "$str ";
			$c   = $c-1;
		}
		echo "$str*\n";
		$good++;
	} else {
		$str = "* $num. There was an error...";
		$str = box_in($str);
		echo "$str*\n";
		$bad++;
	}
	$num++;
	$times = $times - 1;
}
$rate = $good / $origeonal;
$str  = "* \n* Success rate: $rate";
$str  = box_in($str);
echo "$str*\n";
echo "* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\n";

function cin($prompt='') {
	echo $prompt;
	$handle = fopen("php://stdin","r");
	return fgets($handle);
}

function box_in($string) {
	$c   = 86 - strlen($string);
	while($c != 0) {
		$string = "$string ";
		$c   = $c-1;
	}
	return $string;
}


function fetch_proxies( $type ) {
    $types = array(
    	'usa'=>'http://www.us-proxy.org/',
    	'ssl'=>'http://www.sslproxies.org/',
    	'uk'=>'http://free-proxy-list.net/uk-proxy.html',
    	'socks'=>'http://www.socks-proxy.net/',
    	'anonymous'=>'http://free-proxy-list.net/anonymous-proxy.html'
    );
    if(!isset($types[$type])) {
        return "Usage: ?type={Proxy type}\nTypes: usa, ssl, uk, socks, anonymous";
    }
    $source = file_get_contents($types[strtolower($_GET['type'])]);
    preg_match_all('/<tbody>(.*?)<\/tbody>/is', $source, $matches);
    $array = explode("\n", $matches[1][0]);
    $return = array();
    foreach($array as $line) {
    	$line = explode(str_replace('</tr></td>','',str_replace('<tr><td>','',$line)),$line);
    	if(isset($line[0]) && isset($line[1])) {
    		$return[] = "{$line[0]}:{$line[1]}";
    	}
    }
    return $return;
}

function rand_proxy() {
	$proxies        = fetch_proxies('ssl');
	$key         = rand(0,25);
	$proxy       = $proxies[$key];
	while(!check_proxy($proxy)) {
		$key         = rand(0,25);
		$proxy       = $proxies[$key];
	}
	return $proxy;
}

function check_proxy( $proxy /* ip:port */ ) {
	$proxy = explode(':', $proxy);
	$ch = curl_init('http://api.proxyipchecker.com/pchk.php');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,'ip='.$proxy[0].'&port='.$proxy[1]);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$res = explode(';', curl_exec($ch));
	return ($res[0]==false)?false:true; 
}
?>
