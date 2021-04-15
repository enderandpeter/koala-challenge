# Restaurant Data Parser

A REST API made with [Lumen](https://lumen.laravel.com) for obtaining data from restaurant locations that store their data very differently.

## Requirements

* PHP 8
* [Composer](https://getcomposer.org/download/)

## Installation

1. Please clone this project from the git repo.
2. Please copy `.env.example` to `.env`
2. The contents of this project is that of
a typical [Lumen](https://lumen.laravel.com/) project so that [the typical installation instructions](https://lumen.laravel.com/docs/8.x/installation) apply.
You may serve the `public` folder with a typical web server program like `httpd` or `nginx` with a PHP module or FPM, or run the development server with the command:

```bash
php -S localhost:8000 -t public
```

**Strongly Recommended**: Please set `GOOGLE_API_KEY` in `.env` to the value of the Google API key provided to you. This will let the app
detect the timezone for restaurant data that does not explicitly provide this but does provide a geolocation.

Then, simply hit the URL endpoints in question and watch the magic happen.

## Tests

There are example JSON responses from this app in `storage/tests`. The tests can be ran with the command:

```bash
./vendor/bin/phpunit  
```

Here is the general format of the response data:

### Location

* (int) id
* name
* phone_number
* business_hours
* address
* billing_methods[]
    * credit_card
    * gift_card
    * no_cash

### Menu

* (int) id
* categories[]
    * id
    * name
    * modifiers
        * id
        * name
        * modifications[]
            * id
            * name
            * (float) price
            * on_by_default
    * products[]
        * id
        * name
        * variations[]
            * id
            * name
            * (float) price
    
## Assumptions

* A unique integer ID has been attributed to each restaurant location. In the most realistic scenario, there would be a single source generating unique IDs
  for each restaurant. It would not be feasible for each restaurant to create an ID that we would have to discover in their endpoint response, as there
  could be no guarantee that each restaurant would create a unique ID. Our mapping of IDs to a restaurant would be in a datastore of some kind.
  To keep things very simple, `config/restaurants.php` contains this mapping. A larger application might have this in a DB.

* Each remote endpoint to location or menu data are assumed to be referring to a single location or menu.

* No expected data structure was described for this application's endpoint responses, so I tried to define something that
  that includes as much shared data between these as seems necessary.
  
* The locations are assumed to be in the US and use USD currency. Cash is assumed to be used at a location unless explicitly disallowed.

## General Architecture

The `\App\Http\Controllers\Restaurants\DataController::getData` controller method handles all requests. It determines the `dataType` being requested,
either 'location' or 'menu'. It also finds the right restaurant data class to create an instance of, since each data source is unique. The mapping of
IDs to a specifc restaurant data class is in `config/restaurants.php`.

Each of these classes extend the `\App\Restaurants\Retriever` class which contains several properties and methods shared between these classes.
They also implement the `\App\Restaurants\Retrievable` interface which defines the signature of methods that these individual `Retriever` classes
are guaranteed to provide.

In the `App\Restaurants\Koala\Data` namespace are several "container classes" that are meant to define public properties that dictate the structure
of the final JSON response. These classes allow for managing of data properties that might have default values or might be nullable so that the property
will not show up if it is not explicitly assigned.
