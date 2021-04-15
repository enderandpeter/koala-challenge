<?php


namespace App\Restaurants\Koala;


use App\Restaurants\Koala\Data\Menu\Category\Category;
use App\Restaurants\Koala\Data\Menu\Product\Product;
use App\Restaurants\Koala\Data\Menu\Product\Variation;
use App\Restaurants\LocationData;
use App\Restaurants\Koala\Data\Menu\Menu;
use App\Restaurants\Retrievable;
use App\Restaurants\Retriever;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use SimpleXMLElement;

/**
 * Retrieve Koala XML Grill restaurant info
 *
 * @package App\Restaurants\Koala
 */
class XmlGrill extends Retriever implements Retrievable
{
    protected static array $availableBillingMethods = [
        'Credit Card' => 'credit_card',
        'Gift Card' => 'gift_card'
    ];

    public function getLocationData(): LocationData | array
    {
        $data = $this->getRawLocationData();

        if(isset($data['error'])){
            return $data;
        }

        // Local copy of remote XML data
        $localData = new SimpleXMLElement($data['data']);
        $restaurant = $localData;

        isset($this->config['id']) && $this->locationData->id = $this->config['id'];
        isset($restaurant['name']) && $this->locationData->name = $restaurant['name'];
        isset($restaurant['telephone']) && $this->locationData->phone_number = $restaurant['telephone'];
        $this->locationData->geolocation = [
            'lat' => (float) $restaurant['latitude'] ?? null,
            'lng' => (float) $restaurant['longitude'] ?? null
        ];
        $this->locationData->timezone = $this->getTimezone();
        $this->locationData->billing_methods = $this->getBillingMethods($restaurant);
        $this->locationData->address = [
            'line1' => (string) $restaurant['streetaddress'],
            'city' => (string) $restaurant['city'],
            'state' => (string) $restaurant['state'],
            'zip' => (string) $restaurant['zip']
        ];
        $this->locationData->hours = $this->getHours($restaurant);

        return $this->locationData;
    }

    public function getMenuData(): Menu | array
    {
        $data = $this->getRawMenuData();

        if(isset($data['error'])){
            return $data;
        }

        // Local copy of remote XML data
        $localData = new SimpleXMLElement($data['data']);
        $restaurant = $localData;

        isset($this->config['id']) && $this->menuData->id = $this->config['id'];

        $categories = $restaurant->xpath('//category');

        collect($categories)->each(function(SimpleXMLElement $category){
            /**
             * @var $newCat Category
             */
            $newCat = app(Category::class);
            $newCat->id = (string) $category['id'] ?? '';
            $newCat->name = (string) $category['name'] ?? '';

            $products = $category->xpath('//product');

            collect($products)->each(function(SimpleXMLElement $product) use ($newCat){
                /**
                 * @var $newProduct Product
                 */
                $newProduct = app(Product::class);

                $newProduct->id = (string) $product['id'] ?? '';
                $newProduct->name = (string) $product['name'] ?? '';
                $newProduct->description = (string) $product['description'] ?? '';

                $options = $product->xpath('//option');

                collect($options)->each(function(SimpleXMLElement $option) use($newProduct){
                    /**
                     * @var $newVariation Variation
                     */
                    $newVariation = app(Variation::class);
                    $newVariation->id = (string) $option['id'] ?? '';
                    $newVariation->name = (string) $option['name'] ?? '';

                    $amount = (float) $option['cost'] ?? null;

                    if($amount){
                        $newVariation->price = $amount;
                    }

                    $newProduct->variations[] = $newVariation;
                });

                $newCat->products[] = $newProduct;
            });

            $this->menuData->categories[] = $newCat;
        });

        return $this->menuData;
    }

    public function getBillingMethods(array|SimpleXMLElement $location): array
    {
        $billingMethods = $location->xpath('//billingmethod');
        return collect($billingMethods)->map(function($billingmethod){
            return self::$availableBillingMethods[(string) $billingmethod] ?? null;
        })->filter(function($element){
            return $element;
        })->values()->toArray();
    }

    public function getHours(array|SimpleXMLElement $location): array
    {
        $periods = $location->xpath('//hours/period[@type="pickup"]');

        return collect($periods)->map(function(SimpleXMLElement $period){
            $day = isset($period['day']) ? (string) $period['day'] : null;
            $start = isset($period['from']) ? (new DateTime((string) $period['from']))->format(self::$timeFormat) : null;
            $end = isset($period['to']) ? (new DateTime((string) $period['to']))->format(self::$timeFormat) : null;;

            return compact('day', 'start', 'end');
        })->sortBy(function($period){
            return (new DateTime($period['day']))->format('w');
        })->values()->toArray();
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
