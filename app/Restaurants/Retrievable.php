<?php


namespace App\Restaurants;

use App\Restaurants\Koala\Data\Menu\Menu;
use SimpleXMLElement;

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
    public function getMenuData(): Menu | array;
    public function getBillingMethods(array|SimpleXMLElement $location): array;
    public function getHours(array|SimpleXMLElement $location): array;
    public function getTimezone(array $location): string;
}
