<?php

return [
    1 => [
        'class' => 'App\Restaurants\Koala\XmlGrill',
        'urls' => [
            'location' => 'https://koala-coding-challenge.s3.amazonaws.com/backend/koala-xml-grill-data.xml',
            'menu' => 'https://koala-coding-challenge.s3.amazonaws.com/backend/koala-xml-grill-data.xml'
        ]
    ],
    2 => [
        'class' => 'App\Restaurants\Koala\JsonEatery',
        'urls' => [
            'location' => 'https://koala-coding-challenge.s3.amazonaws.com/backend/koala-json-eatery-location.json',
            'menu' => 'https://koala-coding-challenge.s3.amazonaws.com/backend/koala-json-eatery-menu.json'
        ]
    ],
    'google-api-key' => env('GOOGLE_API_KEY')
];
