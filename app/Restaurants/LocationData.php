<?php


namespace App\Restaurants;

/**
 * A container class for any restaurant's location data
 *
 * @package App\Restaurants
 */
class LocationData
{
    public int $id;
    public string $name;
    public string $phone_number;
    /**
     * <pre>['lat' => 40.7139379, lng => -73.95794]</pre>
     * @var array
     */
    public array $geolocation;
    public string $timezone;
    /**
     * <pre>
     * [
     *  'line1' => '123 Fake St.',
     *  'line2' => 'Suite 100',
     *  'city' => 'Brooklyn',
     *  'state' => 'NY',
     *  'zip' => '11211'
     * ]
     * </pre>
     * @var array
     */
    public array $address;
    /**
     * <pre>
     * [ 'credit_card', 'gift_card', 'no_cash' ]
     * </pre>
     * @var array
     */
    public array $billing_methods;
    /**
     * <pre>
     * [
     *  'Monday' => [
     *      'start' => '09:00:00 America/New_York',
     *      'end' => '20:00:00 America/New_York'
     *  ],
     * 'Tuesday' => [
     *      'start' => '09:00:00 America/New_York',
     *      'end' => '20:00:00 America/New_York'
     *  ]...
     * ]
     * </pre>
     * @var array
     */
    public array $hours;
}
