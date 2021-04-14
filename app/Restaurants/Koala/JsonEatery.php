<?php


namespace App\Restaurants\Koala;


use App\Restaurants\LocationData;
use App\Restaurants\MenuData;
use App\Restaurants\Retrievable;
use App\Restaurants\Retriever;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Str;

/**
 * Retrieve Koala JSON Eatery restaurant info
 *
 * @package App\Restaurants\Koala
 */
class JsonEatery extends Retriever implements Retrievable
{
    public static array $availableBillingMethods = [
        'CREDIT_CARD_PROCESSING' => 'credit_card'
    ];
    public function getLocationData(): LocationData | array
    {
        $data = $this->getRawLocationData();

        if(isset($data['error'])){
            return $data;
        }

        // Local copy of remote JSON data
        $localData = json_decode($data['data'], true);

        $location = $localData['locations'][0];

        isset($this->config['id']) && $this->locationData->id = $this->config['id'];
        isset($location['name']) && $this->locationData->name = $location['name'];
        isset($location['phone_number']) && $this->locationData->phone_number = $location['phone_number'];
        $this->locationData->geolocation = [
          'lat' => $location['coordinates']['latitude'] ?? null,
          'lng' => $location['coordinates']['longitude'] ?? null
        ];
        isset($location['timezone']) && $this->locationData->timezone = $location['timezone'];
        $this->locationData->billing_methods = $this->getBillingMethods($location);
        isset($location['address']) && $address = $location['address'];
        $this->locationData->address = [
            'city' => isset($address['locality']) ? Str::title($address['locality']) : null,
            'state' => $address['administrative_district_level_1'] ?? null,
            'zip' => $address['postal_code'] ?? null
        ];
        $this->locationData->hours = $this->getHours($location);

        return $this->locationData;
    }

    public function getMenuData(): MenuData | array
    {
        $data = $this->getRawMenuData();

        if(isset($data['error'])){
            return $data;
        }
        // TODO: Implement getMenuData() method.
    }

    public function getBillingMethods(array $location): array
    {
        return collect($location['capabilities'])->map(function($capability){
            return self::$availableBillingMethods[$capability];
        })->toArray();
    }

    public function getHours(array $location): array
    {
        $periods = $location['business_hours']['periods'];

        return collect($periods)->map(function($period) use ($location){
            $abbr = Str::title($period['day_of_week']);
            $day = collect(self::$daysOfWeek)->first(function($d) use ($abbr){
                return Str::startsWith($d, $abbr);
            });

            $start = (
                new DateTime($period['start_local_time'],
                    new DateTimeZone($location['timezone']))
            )->format(self::$timeFormat);
            $end = (
                new DateTime($period['end_local_time'],
                    new DateTimeZone($location['timezone']))
            )->format(self::$timeFormat);

            return [
                compact('day', 'start', 'end')
            ];
        })->toArray();
    }

    public function getTimezone(array $location): string
    {
        return $location['timezone'];
    }
}
