<?php



/*

  Symfony has a nice client for making HTTP requests, but I'm trying to
  write this code with just the PHP standard library.

 */



function fetch($url, $recent_request=1) {
    $ch = curl_init ($url);
    curl_setopt ($ch, CURLOPT_COOKIE, "recent_request=$recent_request");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_KEEP_SENDING_ON_ERROR, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $output = curl_exec ($ch);
    curl_close($ch);
    $output_array = explode("\n", $output);
    for ($i=0; $i < count($output_array); $i++) {
        $item = $output_array[$i];
        $item = str_replace("\r", "", $item);
        $output_array[$i] = $item;
    }
    return $output_array;
}

/*
  A bit lazy, I'm just going to test the HTTP status codes. But
  I'm making this verbose so you can see what is happening. In
  real life I'd use a better framework for this.
 */
function tests() {

    $how_many_tests_pass = 0;

    $url1 = "http://127.0.0.1:8000/core.php?path=user_xml";
    $url2 = "http://127.0.0.1:8000/core.php?path=user_json";
    $url3 = "http://127.0.0.1:8000/core.php";

    $meta = fetch($url1);
    echo "The url is: \n";
    echo "$url1 \n";
    echo "HTTP Status should be: \n";
    echo "HTTP/1.1 200 OK \n";
    echo "HTTP Status is: \n";
    echo $meta[0] . " \n";
    echo "Content-type should be: \n";
    echo "Content-Type: application/xml \n";
    echo "Content type is: \n";
    echo $meta[6] . " \n";
    echo "\n\n";

    if ($meta[0] == 'HTTP/1.1 200 OK') {
        $how_many_tests_pass++;
    }

    $meta = fetch($url2);
    echo "The url is: \n";
    echo "$url2 \n";
    echo "HTTP Status should be: \n";
    echo "HTTP/1.1 200 OK \n";
    echo "HTTP Status is: \n";
    echo $meta[0] . " \n";
    echo "Content-type should be: \n";
    echo "Content-Type: application/json \n";
    echo "Content type is: \n";
    echo $meta[6] . " \n";
    echo "\n\n";

    if ($meta[0] == 'HTTP/1.1 200 OK') {
        $how_many_tests_pass++;
    }

    $meta = fetch($url3);
    echo "The url is: \n";
    echo "$url3 \n";
    echo "HTTP Status should be: \n";
    echo "HTTP/1.1 404 Not Found \n";
    echo "HTTP Status is: \n";
    echo $meta[0];
    echo  " \n";
    echo "Content-type should be: \n";
    echo "Content-type: text/html; charset=UTF-8 \n";
    echo "Content type is: \n";
    echo $meta[6] . " \n";
    echo "\n\n";

    if ($meta[0] == 'HTTP/1.1 404 Not Found') {
        $how_many_tests_pass++;
    }

    $meta = fetch($url1, 3651086169);
    echo "RATE LIMITING TEST \n";
    echo "The url is: \n";
    echo "$url1 \n";
    echo "HTTP Status should be: \n";
    echo "HTTP/1.1 429 Too Many Requests \n";
    echo "HTTP Status is: \n";
    echo $meta[0];
    echo  " \n";
    echo "Content-type should be: \n";
    echo "Content-type: text/html; charset=UTF-8 \n";
    echo "Content type is: \n";
    echo $meta[5] . " \n";
    echo "\n\n";

    if ($meta[0] == 'HTTP/1.1 429 Too Many Requests') {
        $how_many_tests_pass++;
    }

    echo "\n\n\n";
    echo $how_many_tests_pass . " of 4 tests passed.";



}




tests();
