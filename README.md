# Restaurant Data Parser

A REST API for obtaining data from restaurant locations that store their data very differently.

## Installation

If you have this project in a zip file, please unzip it. Otherwise, clone it from a remote repo. The contents of this project is that of
a typical [Lumen](https://lumen.laravel.com/) project so that [the typical installation instructions](https://lumen.laravel.com/docs/8.x/installation) apply.
You may serve the `public` folder or run the development server with the command:

```bash
php -S localhost:8000 -t public
```

Then, simply hit the URL endpoints in question and watch the magic happen.

## Requirements

For the best results, run this app on PHP 8.

## Assumptions

* A unique integer ID has been attributed to each restaurant location. In the most realistic scenario, there would be a single source generating unique IDs
  for each restaurant. It would not be feasible for each restaurant to create an ID that we would have to discover in their endpoint response, as there
  could be no guarantee that each restaurant would create a unique ID. Our mapping of IDs to a restaurant would be in a datastore of some kind.
  To keep things very simple, `config/restaurant.php` contains this mapping. A larger application might have this in a DB.

* Each remote endpoint to location or menu data are assumed to be referring to a single location or menu.

* No expected data structure was described for this application's endpoint responses, so I tried to define something that
  that includes as much shared data between these as seems necessary.
  
* The locations are assumed to be in the US and use USD currency. Cash is assumed to be used at both locations.
  

