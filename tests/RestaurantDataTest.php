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
        $this->json('GET', route('get-restaurant-data', ['id' => 2]))->seeJson(
            json_decode(
                file_get_contents(
                    storage_path('tests/location-koala-json-response.json')
                ),
                true
            )
        );
        $this->json('GET', route('get-restaurant-data', ['id' => 2, 'dataType' => 'menu']))->seeJson(
            json_decode(
                file_get_contents(
                    storage_path('tests/menu-koala-json-response.json')
                ),
                true
            )
        );

        // Koala XML Grill
        $this->json('GET', route('get-restaurant-data', ['id' => 1]))->seeJson(
            json_decode(
                file_get_contents(
                    storage_path('tests/location-koala-xml-response.json')
                ),
                true)
        );

        $this->json('GET', route('get-restaurant-data', ['id' => 1, 'dataType' => 'menu']))->seeJson(
            json_decode(
                file_get_contents(
                    storage_path('tests/menu-koala-xml-response.json')
                ),
                true)
        );
    }
}
