<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RestaurantDataTest extends TestCase
{
    /**
     * Test the expected presence of restaurant data
     *
     * @return void
     */
    public function testRestaurantData()
    {
        // Koala JSON Eatery
        $this->json('GET', route('get-restaurant-data', ['id' => 2]))->seeJson([
            'id' => 2,
            'name' => 'Koala JSON Eatery',
            'phone_number' => '+1 555-555-5555',
            'geolocation' => [
                'lat' => 40.7139379,
                'lng' => -73.95794
            ],
            'timezone' => 'America/New_York',
            'billing_methods' => ['credit_card'],
            'address' => [
                'city' => 'Brooklyn',
                'state' => 'NY',
                'zip' => '11211'
            ],
            'hours' => [
                [
                    [
                        'day' => 'Monday',
                        'start' => '09:00:00',
                        'end' => '20:00:00'
                    ]
                ],
                [
                    [
                        'day' => 'Tuesday',
                        'start' => '09:00:00',
                        'end' => '20:00:00'
                    ]
                ],
                [
                    [
                        'day' => 'Wednesday',
                        'start' => '09:00:00',
                        'end' => '20:00:00'
                    ]
                ],
                [
                    [
                        'day' => 'Thursday',
                        'start' => '09:00:00',
                        'end' => '20:00:00'
                    ]
                ],
                [
                    [
                        'day' => 'Friday',
                        'start' => '09:00:00',
                        'end' => '20:00:00'
                    ]
                ]
            ]
        ]);

        // Koala XML Grill
        $this->json('GET', route('get-restaurant-data', ['id' => 1]))->seeJson([
            'id' => 1,
            'name' => 'Koala XML Grill',
            'phone_number' => '5555555555',
            'geolocation' => [
                'lat' => 40.7139379,
                'lng' => -73.95794
            ],
            'timezone' => 'America/New_York'
        ]);
    }
}
