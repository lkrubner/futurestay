# futurestay

Some experiments in simple web apps using PHP


## To start

Check out the code from Github:

    git clone git@github.com:lkrubner/futurestay.git

    cd futurestay/experiments

PHP does not have as many easy-to-embed web servers as the ecosystem of Java or Javascript offers. In Java or Clojure I can simply embed Jetty, which is a great industrial strength web server. It takes one line of code. With PHP, typically the code is run with Nginx or Apache, both of which take some effort to setup. But PHP does afford us with a simple web server that we can use for the sake of running this app and testing it.

Run this in your terminal:

    php -S 127.0.0.1:8000

And then in your browser you can look at these 3 pages:

    http://127.0.0.1:8000/core.php?path=user_xml

    http://127.0.0.1:8000/core.php?path=user_json

    http://127.0.0.1:8000/core.php

Of HTTP status codes, you should get a 200, a 200, and a 404.

To test the rate limit feature, set a cookie with a time in the future. This is a timestamp many years in the future:

    curl --verbose --cookie "recent_request=2651077198" "http://127.0.0.1:8000/core.php?path=user_json"

Or, open two web browsers and hit refresh quickly in both of them. Sometimes I was able to get this error message in the browser if I hit "refesh" very quickly, but you're at the mercy of what your web browser is doing in the background. This is a stateless rate limit that relies on cookies, as such it would be easy for a user to hack, but anything better would require the maintenance of some state.

To run the high level functional tests, please do this:

    php -f tests.php

Please scroll to the bottom to see why I here preferred a high level functional test over unit tests.



## How to sort by last name?


Try this in your terminal:

    curl --verbose https://randomuser.me/api/

Does `آدرین احمدی` come before `m` or after `m`? I don't think this can be answered meaningfully, unless we engage in some arbitrary path through Unicode.

I see the need to come up with a way to sort by last names of different languages. For instance:

        (
            [full_name] => Mrs Regina Welch
            [phone] => (446)-585-8912
            [email] => regina.welch@example.com
            [country] => United States
        )


        (
            [full_name] => Mr آدرین احمدی
            [phone] => 097-78111274
            [email] => adryn.hmdy@example.com
            [country] => Iran
        )

There is no natural sorting between languages, so this request seems difficult.

My first thought was to use setlocale, to set the correct sorting for each language. There is a good explanation here:

https://stackoverflow.com/questions/832709/natural-sorting-algorithm-in-php-with-support-for-unicode

If I knew the language then I could use setlocale:

       setlocale(LC_COLLATE,'pl_PL.UTF-8');
       $PL = array('łyżka','Żeźnia','żebrak','grzegrzółka','Ósemka','2-mięsieczny źrebak');
       usort($PL,'strcoll');
       => array('2-mięsieczny źrebak','grzegrzółka','łyżka','Ósemka','żebrak','Żeźnia'

I would need to get the correct country codes, from the country names. One possibility is that I work find a workable country code like this:

    $countries = [];
    $countries["Andorra"] = "AD";
    $countries["United Arab Emirates"] = "AE";
    $countries["Afghanistan"] = "AF";
    $countries["Antigua and Barbuda"] = "AG";
    $countries["Anguilla"] = "AI";
    $countries["Albania"] = "AL";
    $countries["Armenia"] = "AM";
    $countries["Netherlands Antilles"] = "AN";
    $countries["Angola"] = "AO";
    $countries["Antarctica"] = "AQ";
    $countries["Argentina"] = "AR";
    $countries["American Samoa"] = "AS";
    $countries["Austria"] = "AT";
    $countries["Australia"] = "AU";
    $countries["Aruba"] = "AW";
    $countries["Azerbaijan"] = "AZ";
    $countries["Bosnia and Herzegovina"] = "BA";
    $countries["Barbados"] = "BB";
    $countries["Bangladesh"] = "BD";
    $countries["Belgium"] = "BE";
    $countries["Burkina Faso"] = "BF";
    $countries["Bulgaria"] = "BG";
    $countries["Bahrain"] = "BH";
    $countries["Burundi"] = "BI";
    $countries["Benin"] = "BJ";
    $countries["Bermuda"] = "BM";
    $countries["Brunei"] = "BN";
    $countries["Bolivia"] = "BO";
    $countries["Brazil"] = "BR";
    $countries["Bahamas"] = "BS";
    $countries["Bhutan"] = "BT";
    $countries["Bouvet Island"] = "BV";
    $countries["Botswana"] = "BW";
    $countries["Belarus"] = "BY";
    $countries["Belize"] = "BZ";
    $countries["Canada"] = "CA";
    $countries["Cocos (Keeling) Islands"] = "CC";
    $countries["Congo, The Democratic Republic of the"] = "CD";
    $countries["Central African Republic"] = "CF";
    $countries["Congo"] = "CG";
    $countries["Switzerland"] = "CH";
    $countries["Côte d?Ivoire"] = "CI";
    $countries["Cook Islands"] = "CK";
    $countries["Chile"] = "CL";
    $countries["Cameroon"] = "CM";
    $countries["China"] = "CN";
    $countries["Colombia"] = "CO";
    $countries["Costa Rica"] = "CR";
    $countries["Cuba"] = "CU";
    $countries["Cape Verde"] = "CV";
    $countries["Christmas Island"] = "CX";
    $countries["Cyprus"] = "CY";
    $countries["Czech Republic"] = "CZ";
    $countries["Germany"] = "DE";
    $countries["Djibouti"] = "DJ";
    $countries["Denmark"] = "DK";
    $countries["Dominica"] = "DM";
    $countries["Dominican Republic"] = "DO";
    $countries["Algeria"] = "DZ";
    $countries["Ecuador"] = "EC";
    $countries["Estonia"] = "EE";
    $countries["Egypt"] = "EG";
    $countries["Western Sahara"] = "EH";
    $countries["Eritrea"] = "ER";
    $countries["Spain"] = "ES";
    $countries["Ethiopia"] = "ET";
    $countries["Finland"] = "FI";
    $countries["Fiji Islands"] = "FJ";
    $countries["Falkland Islands"] = "FK";
    $countries["Micronesia, Federated States of"] = "FM";
    $countries["Faroe Islands"] = "FO";
    $countries["France"] = "FR";
    $countries["Gabon"] = "GA";
    $countries["United Kingdom"] = "GB";
    $countries["Grenada"] = "GD";
    $countries["Georgia"] = "GE";
    $countries["French Guiana"] = "GF";
    $countries["Ghana"] = "GH";
    $countries["Gibraltar"] = "GI";
    $countries["Greenland"] = "GL";
    $countries["Gambia"] = "GM";
    $countries["Guinea"] = "GN";
    $countries["Guadeloupe"] = "GP";
    $countries["Equatorial Guinea"] = "GQ";
    $countries["Greece"] = "GR";
    $countries["South Georgia and the South Sandwich Islands"] = "GS";
    $countries["Guatemala"] = "GT";
    $countries["Guam"] = "GU";
    $countries["Guinea-Bissau"] = "GW";
    $countries["Guyana"] = "GY";
    $countries["Hong Kong"] = "HK";
    $countries["Heard Island and McDonald Islands"] = "HM";
    $countries["Honduras"] = "HN";
    $countries["Croatia"] = "HR";
    $countries["Haiti"] = "HT";
    $countries["Hungary"] = "HU";
    $countries["Indonesia"] = "ID";
    $countries["Ireland"] = "IE";
    $countries["Israel"] = "IL";
    $countries["India"] = "IN";
    $countries["British Indian Ocean Territory"] = "IO";
    $countries["Iraq"] = "IQ";
    $countries["Iran"] = "IR";
    $countries["Iceland"] = "IS";
    $countries["Italy"] = "IT";
    $countries["Jamaica"] = "JM";
    $countries["Jordan"] = "JO";
    $countries["Japan"] = "JP";
    $countries["Kenya"] = "KE";
    $countries["Kyrgyzstan"] = "KG";
    $countries["Cambodia"] = "KH";
    $countries["Kiribati"] = "KI";
    $countries["Comoros"] = "KM";
    $countries["Saint Kitts and Nevis"] = "KN";
    $countries["North Korea"] = "KP";
    $countries["South Korea"] = "KR";
    $countries["Kuwait"] = "KW";
    $countries["Cayman Islands"] = "KY";
    $countries["Kazakstan"] = "KZ";
    $countries["Laos"] = "LA";
    $countries["Lebanon"] = "LB";
    $countries["Saint Lucia"] = "LC";
    $countries["Liechtenstein"] = "LI";
    $countries["Sri Lanka"] = "LK";
    $countries["Liberia"] = "LR";
    $countries["Lesotho"] = "LS";
    $countries["Lithuania"] = "LT";
    $countries["Luxembourg"] = "LU";
    $countries["Latvia"] = "LV";
    $countries["Libyan Arab Jamahiriya"] = "LY";
    $countries["Morocco"] = "MA";
    $countries["Monaco"] = "MC";
    $countries["Moldova"] = "MD";
    $countries["Madagascar"] = "MG";
    $countries["Marshall Islands"] = "MH";
    $countries["Macedonia"] = "MK";
    $countries["Mali"] = "ML";
    $countries["Myanmar"] = "MM";
    $countries["Mongolia"] = "MN";
    $countries["Macao"] = "MO";
    $countries["Northern Mariana Islands"] = "MP";
    $countries["Martinique"] = "MQ";
    $countries["Mauritania"] = "MR";
    $countries["Montserrat"] = "MS";
    $countries["Malta"] = "MT";
    $countries["Mauritius"] = "MU";
    $countries["Maldives"] = "MV";
    $countries["Malawi"] = "MW";
    $countries["Mexico"] = "MX";
    $countries["Malaysia"] = "MY";
    $countries["Mozambique"] = "MZ";
    $countries["Namibia"] = "NA";
    $countries["New Caledonia"] = "NC";
    $countries["Niger"] = "NE";
    $countries["Norfolk Island"] = "NF";
    $countries["Nigeria"] = "NG";
    $countries["Nicaragua"] = "NI";
    $countries["Netherlands"] = "NL";
    $countries["Norway"] = "NO";
    $countries["Nepal"] = "NP";
    $countries["Nauru"] = "NR";
    $countries["Niue"] = "NU";
    $countries["New Zealand"] = "NZ";
    $countries["Oman"] = "OM";
    $countries["Panama"] = "PA";
    $countries["Peru"] = "PE";
    $countries["French Polynesia"] = "PF";
    $countries["Papua New Guinea"] = "PG";
    $countries["Philippines"] = "PH";
    $countries["Pakistan"] = "PK";
    $countries["Poland"] = "PL";
    $countries["Saint Pierre and Miquelon"] = "PM";
    $countries["Pitcairn"] = "PN";
    $countries["Puerto Rico"] = "PR";
    $countries["Palestine"] = "PS";
    $countries["Portugal"] = "PT";
    $countries["Palau"] = "PW";
    $countries["Paraguay"] = "PY";
    $countries["Qatar"] = "QA";
    $countries["Réunion"] = "RE";
    $countries["Romania"] = "RO";
    $countries["Russian Federation"] = "RU";
    $countries["Rwanda"] = "RW";
    $countries["Saudi Arabia"] = "SA";
    $countries["Solomon Islands"] = "SB";
    $countries["Seychelles"] = "SC";
    $countries["Sudan"] = "SD";
    $countries["Sweden"] = "SE";
    $countries["Singapore"] = "SG";
    $countries["Saint Helena"] = "SH";
    $countries["Slovenia"] = "SI";
    $countries["Svalbard and Jan Mayen"] = "SJ";
    $countries["Slovakia"] = "SK";
    $countries["Sierra Leone"] = "SL";
    $countries["San Marino"] = "SM";
    $countries["Senegal"] = "SN";
    $countries["Somalia"] = "SO";
    $countries["Suriname"] = "SR";
    $countries["Sao Tome and Principe"] = "ST";
    $countries["El Salvador"] = "SV";
    $countries["Syria"] = "SY";
    $countries["Swaziland"] = "SZ";
    $countries["Turks and Caicos Islands"] = "TC";
    $countries["Chad"] = "TD";
    $countries["French Southern territories"] = "TF";
    $countries["Togo"] = "TG";
    $countries["Thailand"] = "TH";
    $countries["Tajikistan"] = "TJ";
    $countries["Tokelau"] = "TK";
    $countries["Turkmenistan"] = "TM";
    $countries["Tunisia"] = "TN";
    $countries["Tonga"] = "TO";
    $countries["East Timor"] = "TP";
    $countries["Turkey"] = "TR";
    $countries["Trinidad and Tobago"] = "TT";
    $countries["Tuvalu"] = "TV";
    $countries["Taiwan"] = "TW";
    $countries["Tanzania"] = "TZ";
    $countries["Ukraine"] = "UA";
    $countries["Uganda"] = "UG";
    $countries["United States Minor Outlying Islands"] = "UM";
    $countries["United States"] = "US";
    $countries["Uruguay"] = "UY";
    $countries["Uzbekistan"] = "UZ";
    $countries["Holy See (Vatican City State)"] = "VA";
    $countries["Saint Vincent and the Grenadines"] = "VC";
    $countries["Venezuela"] = "VE";
    $countries["Virgin Islands, British"] = "VG";
    $countries["Virgin Islands, U.S."] = "VI";
    $countries["Vietnam"] = "VN";
    $countries["Vanuatu"] = "VU";
    $countries["Wallis and Futuna"] = "WF";
    $countries["Samoa"] = "WS";
    $countries["Yemen"] = "YE";
    $countries["Mayotte"] = "YT";
    $countries["Yugoslavia"] = "YU";
    $countries["South Africa"] = "ZA";
    $countries["Zambia"] = "ZM";
    $countries["Zimbabwe"  = "ZW";

I could use that to setlocale(). However, I'm not clear even this approach would work. Let me know if you want me to try it.

Would the comparison across languages be meaningful in any way? I don't believe so. I think `rsort` can handle the Latin alphabet, but among different languages, I think all I can do is create an arbitrary system for sorting.

I could use a Unicode aware library like this:

    function last_name_to_unicode_sum($last_name) {

    $sum = 0;
    $chars = str_split($last_name);

    foreach ($chars as $char) {
        $sum + $sum + IntlChar::ord($char);
        echo $sum;
    }

    return $sum;
    }

However, I'd have to install the International extension to run this code, and you would also have to install the International extension. So I am cheating a bit and simply use `ord()`.

This gives me arrays with keys like this:

    [1310] => Array
        (
            [full_name] => Mr Loek Çalişkan
            [phone] => (944)-980-1205
            [email] => loek.caliskan@example.com
            [country] => Netherlands
        )

    [727] => Array
        (
            [full_name] => Ms Hana Hunstad
            [phone] => 56073322
            [email] => hana.hunstad@example.com
            [country] => Norway
        )

    [685] => Array
        (
            [full_name] => Mr Mílton Almeida
            [phone] => (95) 3307-7703
            [email] => milton.almeida@example.com
            [country] => Brazil
        )

    [629] => Array
        (
            [full_name] => Monsieur Mathias Dufour
            [phone] => 076 164 40 15
            [email] => mathias.dufour@example.com
            [country] => Switzerland
        )

    [371] => Array
        (
            [full_name] => Madame Maya Adam
            [phone] => 075 500 07 31
            [email] => maya.adam@example.com
            [country] => Switzerland
        )

    [965] => Array
        (
            [full_name] => Miss Lori Rodriquez
            [phone] => (131)-430-5871
            [email] => lori.rodriquez@example.com
            [country] => United States
        )

    [513] => Array
        (
            [full_name] => Ms Ana Kelly
            [phone] => (333)-364-0138
            [email] => ana.kelly@example.com
            [country] => United States
        )

    [746] => Array
        (
            [full_name] => Ms Kathy Stewart
            [phone] => 016974 20998
            [email] => kathy.stewart@example.com
            [country] => United Kingdom
        )

    [505] => Array
        (
            [full_name] => Mrs Heather Burke
            [phone] => 051-994-4198
            [email] => heather.burke@example.com
            [country] => Ireland
        )

    [510] => Array
        (
            [full_name] => Ms Isabella Mason
            [phone] => 026 1494 9212
            [email] => isabella.mason@example.com
            [country] => United Kingdom
        )

I then call rsort on that, but I don't know if doing so is meaningful.

Again, if I was working with a single language, then it would be possible to use `setlocale` and the sort functions.

If there is a meaningful kind of sorting that would work across all of Unicode, I'm happy to implement, but let me know specifically what you are looking for.



## Object Oriented Programming

Please look at the Wikipedia page for Object Oriented Programming:

https://en.wikipedia.org/wiki/Object-oriented_programming

That article contains a section called "Criticism." Search for "Krubner" and you'll see I'm listed there. They link to one of my most famous technical essays, which I wrote in 2014.

For the last few years I've advocated for the Functional Paradigm. I think it is a clean style. I've written a lot of microservices in Clojure, often keeping the entire app in a single file, which, I have found, makes it easier to hand-off to a new programmer who is just learning the code. Arguably, this transfers complexity away from the app and towards the overall system of microservies. I'm happy to have that conversation.

I realize PHP has become increasingly object oriented over the years. While I wrote this app in the same functional style I would use in Clojure, I'm also happy to re-write this as a Laravel app. Please let me know if you'd like to see that.



## Unit tests versus high level functional tests

I've often written about this on my weblog, and I'm happy to discuss further. I'm also happy to adjust to whatever the team's policies are regarding unit tests or high level functional tests. The short version of the argument that I typically make goes like this:

Unit tests are very useful when you have a system where most data is generated internally to the system. So, in your typical CMS, unit tests can be very useful, since users are typically using the system to create the data in the system. In such cases you know the database schema, and so you can validate the structure of your data fairly well. However, when consuming 3rd party APIs, unit tests are less useful. When doing a large ETL project, the most likely source of bugs will be changes or mistakes the 3rd party API that you are trying to use. The crucial fact is that unit tests will rely on mock data, which is easy to provide when your system generates the data, but when the data is generated by some outside company, then your mock data is likely to become obsolete quickly. In such cases, your unit tests will pass, but they really should fail -- they would fail if they interacted with the real data coming from the other company. So in projects that are mostly about consuming APIs, it is best to focus on high level functional tests. So this project includes the `tests.php` file, but no unit tests. Let me know if you'd like to see unit tests, I'm happy to add them in.
