<?php


namespace App\Restaurants;

use Illuminate\Support\Collection;

/**
 * Implemented by classes that can retrieve restaurant data. These classes must provide their own implementation
 * of these methods since each data source will be different. These methods return this API's standard data structure for the
 * disparate data.
 *
 * @package App\Restaurants
 */
interface Retrievable
{
    public function getLocationData(): LocationData | array;
    public function getMenuData(): MenuData | array;
    public function getBillingMethods(array $location): array;
    public function getHours(array $location): array;
    public function getTimezone(array $location): string;
}
