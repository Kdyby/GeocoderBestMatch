# Quickstart

## Installation

You can install the extension using this command

```sh
$ composer require kdyby/geocoder-best-match
```

## Configuration example with Nette Framework

The Geocoder depends on `Ivory\HttpAdapter\HttpAdapterInterface`, so it has to be registered as a service for the providers.

```yml
services:
    httpAdapter: Ivory\HttpAdapter\CurlHttpAdapter
```

For my application, I had to use combination of providers, cache them and use comparators to get the result best matching the input.
The following example uses custom `CachingProvider`, which is not part of this package.

```yml
services:
    geocoder: Custom\CachingProvider(
        Kdyby\Geocoder\BestMatch\BestMatchAggregator(
            @geocoder.providers,
            Kdyby\Geocoder\BestMatch\Comparator\MoreDataComparator()
        )
    )

    geocoder.providers:
        class: Geocoder\ProviderAggregator()
        setup:
            - registerProvider(@geocoder.seznam)
            - registerProvider(@geocoder.google)
        autowired: false

    geocoder.seznam:
        class: Kdyby\Geocoder\BestMatch\BestMatchProvider(
            Kdyby\Geocoder\Provider\SeznamMaps\SeznamMapsProvider(@httpAdapter),
            @geocoder.provider.comparator
        )
        setup:
            - limit(5)
        autowired: false

    geocoder.google:
        class: Kdyby\Geocoder\BestMatch\BestMatchProvider(
            Geocoder\Provider\GoogleMaps(@httpAdapter, cs_CZ, CZ, TRUE),
            @geocoder.provider.comparator
        )
        setup:
            - limit(0)
        autowired: false

    geocoder.provider.comparator:
        class: Kdyby\Geocoder\BestMatch\Comparator\BigCitiesFirstComparator(
            ['Praha', 'Brno', 'Ostrava', 'Hradec Kr(á|a)lov(é|e)', 'Liberec', 'Plze(ň|n)', 'Olomouc'],
            Kdyby\Geocoder\BestMatch\Comparator\LevenshteinDistanceComparator()
        )
        autowired: false
```

First, we have two geocoding providers `geocoder.seznam` and `geocoder.google`. They're both wrapped in `BestMatchProvider`,
which uses passed comparator to sort the results. Also, the `geocoder.seznam` has limit of 5 best results.

Then we have a `geocoder.provider.comparator` which is a `BigCitiesFirstComparator` comparator configured with list of sorted big cities from Czech Republic.
This allows me to sort results based on the city the geocoder returns. Sometimes the user enters only the street and number without city.
I'm simply assuming that when you enter only the street, you're from probably from Prague or Brno.

Next, there is a `LevenshteinDistanceComparator` comparator as a fallback for the `BigCitiesFirstComparator`. When two entries are identical for the comparator,
it uses the fallback and sorts them by levenshtein distance which, simply put, measures the number of different symbols in two strings. This is checked against the user input.

This is all wrapped in the `geocoder.providers`, which simple holds onto the providers.

The `geocoder.providers` is wrapped in `BestMatchAggregator` which iterates over all the providers, merges all the returned addresses
and sorts them by the fact of simply having returned more data from the provider and if the data matches the user input better.

If you have this kind of complex setup, at the end you only have to call

```php
$results = $geocoder->geocode($searchQuery);
```

and you will have a `AddressCollection` of normalized addresses sorted by the similarity to the user input.
Calling the `$results->first()` returns the best matching address that you can then process.
