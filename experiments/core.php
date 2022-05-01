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

function flatten($user_array, $flat=[], $outer_key='') {
    foreach(array_keys($user_array) as $array_key) {
        $keyname = $outer_key . $array_key;
        if (is_array($user_array[$array_key])) {
            $flat = flatten($user_array[$array_key], $flat, $keyname);
        } else {
            $flat[$keyname] = $user_array[$array_key];
        }
    }

    return $flat;
}

function process($user) {
    $user_array_raw = json_decode($user, true);
    $user_array = $user_array_raw['results'][0];
    $flat = flatten($user_array);
    return $flat;
}

function supported_api($user_array) {
    $message = '';
    foreach($user_array as $key => $value) {
        $message .= "$key, ";
    }
    $message = substr($message, 0, -2);
    return $message;
}

function generate($user_array) {

    $filtered = [];
    $schema = $_REQUEST['schema'];
    if (!$schema) $schema = ['namefirst', 'namelast', 'locationstreetnumber', 'locationstreetname', 'locationcity'];
    $supported = supported_api($user_array);

    foreach ($schema as $value) {
        if (array_key_exists($value, $user_array)) {
            $filtered[] = $user_array[$value];
        } else {
            $filtered[] = "You requested '$value' but that is not part of our schema. You may request these fields: $supported";
        }
    }

    return $filtered;
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

    $quantity = (int) $_REQUEST['quantity'];
    // for security, we test this input, and override if it
    // is not an integer, or if it wasn't set.
    if (!is_int($quantity)) {
        $quantity = 10;
    } else {
        if ($quantity == 0) $quantity = 10;
        if ($quantity > 100) $quantity = 100;
    }

    // Since I got some 503 or timeouts on the API, we will include 10 retries;
    $r = 0;
    $combined = [];

    for($i=0; $i < $quantity && $r < 10; $i++) {
        $user = fetch();
        if ($user) {
            $user_array = process($user);
            $combined[] = generate($user_array);
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
            echo "timeout error so we will re-try Random User \n\n";
        }
    }

    return $combined;
}

/*
  In the 'main' branch we hardcoded the functions `user_xml`
  and `user_json` but here we use a closure to create a bit
  of polymorphism.

  A more interesting use would be if we possibly queried
  multiple APIs, and the function was shaped by the existence
  of certain keys in $combined, which would tell us which
  API had been called. That would give us a much more
  flexible system.
 */
function respond($route='xml') {
    $combined = call_api();

    if ($route == 'xml') {
        $func  = function() use ($combined) {
            $dom = create_xml($combined);
            return $dom->saveXML();
        };
    } else {
        $func  = function() use ($combined) {
            return '{ "users" : ' . json_encode($combined) . ' }';
        };
    }

    return $func;
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
    if ($route == 'xml') {
        header("HTTP/1.1 200 OK");
        header('Content-Type: application/xml');
        echo respond($route)();
    } elseif ($route == 'json') {
        header("HTTP/1.1 200 OK");
        header('Content-Type: application/json');
        echo respond($route)();
    } else {
        header("HTTP/1.1 404 Not Found");
        echo "This is not a known path.";
    }


}

routes();
