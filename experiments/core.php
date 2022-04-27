<?php


// The Random User API occassionally throws these errors:
//
// <b>Warning</b>:  file_get_contents(https://randomuser.me/api/): failed to
// open stream: HTTP request failed! HTTP/1.1 503 Service Temporarily Unavailable
//
// so we will turn off all errors, so they don't polute the XML or JSON
// responses, but turn these back on if you want to do any more development
// on this script.


ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(-1);


const SOURCE="https://randomuser.me/api/";


function fetch() {
    $user = file_get_contents(SOURCE);
    return $user;
}


function process($user) {
    $user_array = json_decode($user, true);
    return $user_array;
}

function full_name($user_array) {
    $full_name = '';
    $full_name .= $user_array['results'][0]['name']['title'];
    $full_name .= ' ';
    $full_name .= $user_array['results'][0]['name']['first'];
    $full_name .= ' ';
    $full_name .= $user_array['results'][0]['name']['last'];
    return $full_name;
}

function phone($user_array) {
    $phone = $user_array['results'][0]['phone'];
    return $phone;
}

function email($user_array) {
    $email = $user_array['results'][0]['email'];
    return $email;
}

function country($user_array) {
    $country = $user_array['results'][0]['location']['country'];
    return $country;
}

function generate($user_array) {
    $filtered = [];
    $filtered['full_name'] = full_name($user_array);
    $filtered['phone'] = phone($user_array);
    $filtered['email'] = email($user_array);
    $filtered['country'] = country($user_array);
    return $filtered;
}

/*
  See the README for an explanation of this function
 */
function last_name_to_unicode_sum($last_name) {

    $sum = 0;
    $chars = str_split($last_name);

    foreach ($chars as $char) {
        $sum = $sum + ord($char);
    }

    return $sum;
}

function array2xml($array, $node, &$dom) {
    foreach($array as $key => $value) {
        if(preg_match("/^[0-9]/", $key))
            $key = "node-{$key}";
        $key = preg_replace("/[^a-z0-9_\-]+/i", '', $key);

        if($key==='')
            $key = '_';

        $a = $dom->createElement($key);
        $node->appendChild($a);

        if(!is_array($value))
            $a->appendChild($dom->createTextNode($value));
        else
            array2xml($value, $a, $dom);
    }
}

function create_xml($combined) {
    $dom = new DOMDocument('1.0', 'utf-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $root = $dom->createElement('root');
    $dom->appendChild($root);
    array2xml($combined, $root, $dom);
    return $dom;
}


function call_api() {

    $quantity = $_REQUEST['quantity'];
    // for security, we test this input, and override if it
    // is not an integer, or if it wasn't set.
    if (!is_int($quantity)) {
        $quantity = 10;
    } else {
        if ($quantity > 100) $quantity = 100;
    }


    $combined = [];

    // Since I got some 503 or timeouts on the API, we will include 10 retries;
    $r = 0;

    for($i=0; $i < $quantity && $r < 10; $i++) {
        $user = fetch();
        if ($user) {
            $user_array = process($user);
            $generated = generate($user_array);
            $last_name_sum = last_name_to_unicode_sum($user_array['results'][0]['name']['last']);
            $combined[$last_name_sum] = $generated;
        } else {
            // I got some 503 or timeout errors on the Random User API:
            //
            // <b>Warning</b>:  file_get_contents(https://randomuser.me/api/):
            // failed to open stream: HTTP request
            // failed! HTTP/1.1 503 Service Temporarily Unavailable
            //
            // so lets retry up to 10 times, in the hopes of getting some users,
            // but we don't want to retry an infinite number of times.
            $i--;
            $r++;
        }
    }

    rsort($combined);
    return $combined;
}

/*
  The XML is validated by the browser (whereas the json I validated here: https://jsonlint.com)
 */
function user_xml() {
    $combined = call_api();
    $dom = create_xml($combined);
    echo $dom->saveXML();
}

/*
  I validated the output here: https://jsonlint.com
 */
function user_json() {
    $combined = call_api();
    echo '{ "users" : ' . json_encode($combined) . ' }';
}

/*
  Here we establish a primitive rate limit on our API.
  The user is only allowed to make one request every 15 seconds.
  This is perhaps the only system that can be done in a stateless
  fashion, anything else would involve saving data to a database,
  or daemonizing the service and holding values in a variable
  that lasted from one request to another.
 */
function user_exceeds_rate_limit() {

    $now = time();
    $then = $now - 15;
    if ($_REQUEST['recent_request'] != '') {
        $recent_request = (int) $_REQUEST['recent_request'];
    }

    // in real life, we would want a more sophisticated system then this, as
    // this can be hacked simply by not sending a cookie, however, this is the
    // only stateless approach to a rate_limit. A real system needs a database.
    if (!is_int($recent_request)) {
        setcookie('recent_request', time());
        return false;
    }

    if (is_int($recent_request)) {
        if ($recent_request > $then) {
            return true;
        } else {
            setcookie('recent_request', time());
            return false;
        }
    }
}


function routes() {

    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        header("HTTP/1.1 405 Method Not Allowed");
        echo "You are only allowed to make GET requests.";
        return false;
    }

    if (user_exceeds_rate_limit()) {
        header("HTTP/1.1 429 Too Many Requests");
        echo "You are only allowed to make one request every 15 seconds.";
        return false;
    }


    $route = $_REQUEST['path'];

    // if we simply called $route we would have a very flexible system,
    // but it would be insecure, so we whitelist for security.
    // Here we have 2 routes and then a default route.
    if ($route == 'user_xml') {
        header("HTTP/1.1 200 OK");
        header('Content-Type: application/xml');
        user_xml();
    } elseif ($route == 'user_json') {
        header("HTTP/1.1 200 OK");
        header('Content-Type: application/json');
        user_json();
    } else {
        header("HTTP/1.1 404 Not Found");
        echo "This is not a known path.";

    }


}

routes();
