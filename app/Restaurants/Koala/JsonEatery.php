<?php


namespace App\Restaurants\Koala;


use App\Restaurants\Koala\Data\Menu\Category\Category;
use App\Restaurants\Koala\Data\Menu\Category\Modification;
use App\Restaurants\Koala\Data\Menu\Category\Modifier;
use App\Restaurants\Koala\Data\Menu\Product\Product;
use App\Restaurants\Koala\Data\Menu\Product\Variation;
use App\Restaurants\LocationData;
use App\Restaurants\Koala\Data\Menu\Menu;
use App\Restaurants\Retrievable;
use App\Restaurants\Retriever;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Str;
use SimpleXMLElement;

/**
 * Retrieve Koala JSON Eatery restaurant info
 *
 * @package App\Restaurants\Koala
 */
class JsonEatery extends Retriever implements Retrievable
{
    protected static array $availableBillingMethods = [
        'CREDIT_CARD_PROCESSING' => 'credit_card'
    ];
    public function getLocationData(): LocationData | array
    {
        $data = $this->getRawLocationData();

        if(isset($data['error'])){
            // An error was detected
            return $data;
        }

        // Local copy of remote JSON data
        $localData = json_decode($data['data'], true);

        $location = $localData['locations'][0] ?? null;

        if($location){
            // There is location data to return
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
        }

        return $this->locationData;
    }

    public function getMenuData(): Menu | array
    {
        $data = $this->getRawMenuData();

        if(isset($data['error'])){
            return $data;
        }

        // Local copy of remote JSON data
        $localData = json_decode($data['data'], true);
        $menuList = $localData['objects'] ?? null;

        isset($this->config['id']) && $this->menuData->id = $this->config['id'];

        if($menuList){
            /**
             * @var $menuData Menu
             */
            $menuData = collect($menuList)->reduce(function(Menu $menuData, $menuListObject){
                $objectType = $menuListObject['type'] ?? '';

                // See if this object is a category
                if($objectType === 'CATEGORY'){
                    // Create category if it does not exist
                    $categoryObject = $menuListObject;
                    $categoryObjectName = $categoryObject['category_data']['name'] ?? '';

                    // See if the category exists in the local Menu object being built
                    $catIndex = collect($menuData->categories)->search(function(Category $category) use ($categoryObjectName){
                        $menuDataCatName = $category->name ?? '';
                        return $menuDataCatName === $categoryObjectName;
                    });

                    // Create a new local category for this object
                    if($catIndex === false){
                        /**
                         * @var $newCat Category
                         */
                        $newCat = app(Category::class);
                        $newCat->name = $categoryObjectName;
                        $newCat->id = $categoryObject['id'] ?? '';
                        $menuData->categories[] = $newCat;
                    }
                }

                // Create product (item) if it does not exist

                // See if this object is a product (item)
                if($objectType === 'ITEM'){
                    // The object is a product
                    $productObject = $menuListObject;
                    $productObjectId = $productObject['id'] ?? '';

                    // See if the product is already in our local collections of categories
                    $localCatIndex = collect($menuData->categories)->search(function(Category $category) use ($productObjectId){
                        return collect($category->products)->search(function(Product $product) use ($productObjectId){
                            return $product->id === $productObjectId;
                        });
                    });

                    if($localCatIndex === false){
                        // The product was not found in any category in our local Menu object, so the product will be created
                        /**
                         * @var $newProduct Product
                         */
                        $newProduct = app(Product::class);
                        $newProduct->id = $productObjectId;
                        $newProduct->name = $productObject['item_data']['name'] ?? '';
                        $newProduct->description = $productObject['item_data']['description'] ?? '';

                        // Create the product variations
                        $productObjectVariations = $productObject['item_data']['variations'];
                        $newProduct->variations = collect($productObjectVariations)->map(function($var){
                            /**
                             * @var $newVariation Variation
                             */
                            $newVariation = app(Variation::class);
                            $newVariation->id = $var['id'] ?? '';
                            $newVariation->name = $var['item_variation_data']['name'] ?? '';

                            $amount = $var['item_variation_data']['price_money']['amount'] ?? null;
                            if($amount){
                                $newVariation->price = (int) $amount / 100;
                            }

                            return $newVariation;
                        })->toArray();

                        // Only add the product if we can find the category it belongs to
                        $categoryId = $productObject['item_data']['category_id'];

                        $localCatIndex = collect($this->menuData->categories)->search(function(Category $category) use ($categoryId){
                            return $category->id === $categoryId;
                        });

                        if(isset($this->menuData->categories[$localCatIndex])){
                            // Add the product to our local categories
                            $this->menuData->categories[$localCatIndex]->products[] = $newProduct;
                        }
                    }
                }

                // See if the object is a category modifier list
                if($objectType === 'MODIFIER_LIST'){
                    $modifierListObject = $menuListObject;
                    $modifierListObjectId = $modifierListObject['id'] ?? '';

                    // Get the category that this modifier list is for
                    /**
                     * @var $category Category
                     */
                    $category = $this->menuData->categories[count($this->menuData->categories) - 1];

                    /**
                     * @var $modifier Modifier
                     */
                    $modifier = app(Modifier::class);
                    $modifier->id = $modifierListObjectId;
                    $modifier->name = $modifierListObject['modifier_list_data']['name']  ?? '';

                    $modifierListObjectMods = $modifierListObject['modifier_list_data']['modifiers'] ?? [];

                    $modifier->modifications = collect($modifierListObjectMods)->map(function($mod){
                        /**
                         * @var $modification Modification
                         */
                        $modification = app(Modification::class);

                        $modification->id = $mod['id'] ?? '';
                        $modification->name = $mod['modifier_data']['name'] ?? '';
                        $modification->onByDefault = $mod['modifier_data']['on_by_default'] ?? null;

                        $amount = $mod['modifier_data']['price_money']['amount'] ?? null;
                        if($amount){
                            $modification->price = (int) $amount / 100;
                        }

                        return $modification;
                    })->toArray();

                    if(!isset($category->modifiers)){
                        $category->modifiers = [];
                    }
                    $category->modifiers[] = $modifier;
                }

                return $menuData;
            }, $this->menuData);
        }

        $this->menuData = $menuData ?? [
            'error' => 'No menu data found',
            'code' => -3
        ];

        return $this->menuData;
    }

    public function getBillingMethods(array|SimpleXMLElement $location): array
    {
        return collect($location['capabilities'])->map(function($capability){
            return self::$availableBillingMethods[$capability];
        })->toArray();
    }

    public function getHours(array|SimpleXMLElement $location): array
    {
        $periods = $location['business_hours']['periods'];

        // Convert all business hours data to our local format
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

            return compact('day', 'start', 'end');
        })->sortBy(function($period){
            return (new DateTime($period['day']))->format('w');
        })->values()->toArray();
    }

    public function getTimezone(array $location): string
    {
        return $location['timezone'];
    }
}
