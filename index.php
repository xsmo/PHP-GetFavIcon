<?php


//
// Copyright (c) 2017, maleck.org - Moritz Maleck. All rights reserved.
//



header('Content-type: image/png');

// Link check
$domain = filter_input(INPUT_GET, "domain", FILTER_SANITIZE_STRING);
if(trim($domain) == ''){
    readfile('get_favicon_noicon.png');
    exit;
} else {
    if(!filter_var($domain, FILTER_VALIDATE_URL)) {
        if (strpos($domain,'http://') === false){
            $domain = 'http://'.$domain;
            if(!filter_var($domain, FILTER_VALIDATE_URL)) {
                readfile('get_favicon_noicon.png');
                exit;
            }
        }
    }
}

function getFavicon($url){
    # make the URL simpler
    $elems = parse_url($url);
    $url = $elems['scheme'].'://'.$elems['host'];

    # load site
    $output = file_get_contents($url);

    # look for the shortcut icon inside the loaded page
    $regex_pattern = "/rel=\"shortcut icon\" (?:href=[\'\"]([^\'\"]+)[\'\"])?/";
    preg_match_all($regex_pattern, $output, $matches);

    if(isset($matches[1][0])){
        $favicon = $matches[1][0];

        # check if absolute url or relative path
        $favicon_elems = parse_url($favicon);

        # if relative
        if(!isset($favicon_elems['host'])){
            $favicon = $url . '/' . $favicon;
        }

        return $favicon;
    }

    return false;
}

$favicon = getFavicon($domain);

if($favicon == '') {
    $url = $domain;
    $doc = new DOMDocument();
    $doc->strictErrorChecking = FALSE;
    $doc->loadHTML(file_get_contents($url));
    $xml = simplexml_import_dom($doc);
    $arr = $xml->xpath('//link[@rel="shortcut icon"]');
    $favicon = $arr[0]['href'];
    
    if(!filter_var($favicon, FILTER_VALIDATE_URL)) {
        if (strpos($favicon,$domain) === false){
            $favicon = $domain.$favicon;
            if (strpos($favicon,'www') === false){
                $bits = parse_url($favicon);    
                $newHost = substr($bits["host"],0,4) !== "www."?"www.".$bits["host"]:$bits["host"];    
                $favicon = $bits["scheme"]."://".$newHost.$bits["path"].(!empty($bits["query"])?"?".$bits["query"]:"");
            }
        }
    }
} else {
    if(!filter_var($favicon, FILTER_VALIDATE_URL)) {
        if (strpos($favicon,'http') === false){
            $favicon = 'http:'.$favicon;
        }
    }
}
$domain_ic = $domain.'/';

if($favicon == $domain_ic) {
    readfile('get_favicon_noicon.png');
    exit;
}

readfile($favicon);

