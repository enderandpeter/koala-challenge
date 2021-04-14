<?php


namespace App\Restaurants\Koala;


use App\Restaurants\LocationData;
use App\Restaurants\MenuData;
use App\Restaurants\Retrievable;
use App\Restaurants\Retriever;
use GuzzleHttp\Exception\GuzzleException;
use SimpleXMLElement;

/**
 * Retrieve Koala XML Grill restaurant info
 *
 * @package App\Restaurants\Koala
 */
class XmlGrill extends Retriever implements Retrievable
{

    public function getLocationData(): LocationData | array
    {
        $data = $this->getRawLocationData();

        if(isset($data['error'])){
            return $data;
        }

        // Local copy of remote XML data
        $localData = new SimpleXMLElement($data['data']);
        $location = $localData;

        isset($this->config['id']) && $this->locationData->id = $this->config['id'];
        isset($location['name']) && $this->locationData->name = $location['name'];
        isset($location['telephone']) && $this->locationData->phone_number = $location['telephone'];
        $this->locationData->geolocation = [
            'lat' => (float) $location['latitude'] ?? null,
            'lng' => (float) $location['longitude'] ?? null
        ];
        $this->locationData->timezone = $this->getTimezone();

        return $this->locationData;
    }

    public function getMenuData(): MenuData | array
    {
        // TODO: Implement getMenuData() method.
    }

    public function getBillingMethods(array $location): array
    {
        // TODO: Implement getBillingMethods() method.
    }

    public function getHours(array $location): array
    {
        // TODO: Implement getHours() method.
    }

    public function getTimezone(array $location = []): string
    {
        $timezone = '';

        // Try to get the timezone at this geolocation using Google Timezone API
        try{
            $data = $this->httpClient->get(
                'https://maps.googleapis.com/maps/api/timezone/json',
                [
                    'query' => [
                        'location' => $this->locationData->geolocation['lat'] . ',' . $this->locationData->geolocation['lng'],
                        'timestamp' => time(),
                        'key' => config('restaurants.google-api-key')
                    ]
                ]
            )->getBody();

            $timezone = json_decode($data, true)['timeZoneId'] ?? '';
        } catch (GuzzleException $e){
            report($e);
        }

        return $timezone;
    }
}
