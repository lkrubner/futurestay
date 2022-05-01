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

    http://127.0.0.1:8000/core.php?path=xml

    http://127.0.0.1:8000/core.php?path=json

    http://127.0.0.1:8000/core.php

Of HTTP status codes, you should get a 200, a 200, and a 404.

To test the rate limit feature, set a cookie with a time in the future. This is a timestamp many years in the future:

    curl --verbose --cookie "recent_request=2651077198" "http://127.0.0.1:8000/core.php?path=json"

Or, open two web browsers and hit refresh quickly in both of them. Sometimes I was able to get this error message in the browser if I hit "refesh" very quickly, but you're at the mercy of what your web browser is doing in the background. This is a stateless rate limit that relies on cookies, as such it would be easy for a user to hack, but anything better would require the maintenance of some state.

You can specify the schema like this:

    http://127.0.0.1:8000/core.php?path=json&schema[]=namefirst&schema[]=namelast&schema[]=locationcoordinateslatitude&schema[]=locationcoordinateslongitude&schema[]=phone&schema[]=picture&quantity=3

We've included `picture` which is a mistake. This lets you see the error message, which explains the schema.I believe in verbose error messages that explain things to the user.



## Closures and meta-programming

In recent years I've done a lot of programming in Clojure, Javascript, and Ruby. In all of these languages, closures are central.

Way back in 2001, Douglas Crockford wrote "Private Members in JavaScript" a famous essay that had a big impact on how I thought about computer programming.

    http://crockford.com/javascript/private.html

This essay is where I first learned about the concept of closures. At the time, PHP did not support closures, but it has added them. I've been away from PHP for a few years, so I haven't had a chance to work with them, but I did experiment with them in this branch, so that now the response is a function returned from the `respond` function, and to call it we add `()` to the end of the call, getting syntax that looks more like Javascript than PHP:

    echo respond($route)();

I haven't done much with it, but the concept is interesting. How might this be useful? Let's compare this to class object oriented programming.




## Object Oriented Programming

Please look at the Wikipedia page for Object Oriented Programming:

https://en.wikipedia.org/wiki/Object-oriented_programming

That article contains a section called "Criticism." Search for "Krubner" and you'll see I'm listed there. They link to one of my most famous technical essays, which I wrote in 2014.

What do we hope for, when we use object oriented programming? A few things, but here are 2 big ones:

encapsulation

polymorphism

As others have pointed out, closures offer very high levels of encapsulation and polymorphism. A function that produces a function (a function factory) can embed variables into a template, producing a function that has a variable data that cannot be altered (a private member, as Doug Crockford pointed out) and yet the template can be altered based on what is fed to the function.

This allows a lot of safety and flexibility, with less ceremony, and less configuration, then classic object oriented programming.

I didn't go very far with it in the code so far (let me know if you'd like to see that) but we can imagine, if this app pulled data from multiple APIs, the `respond` function could be further customized to generate functions based on which API was in use, which it could autodetect based on the names of the fields that come back from a data call.

## Which code is easier to read?

Some programmers argue that Javascript or Ruby is difficult to read when it has too many closures. But thenm, some programmers argue that object oriented programming can involve very tall object hierarchies, which sometimes leads to confusion about which version of a method is being called in any particular context. I think that it is a matter of preference, which style programmers find easy to read. But I think this is an interesting topic to discuss, and I'm always happy to share what I've learned over the years.
