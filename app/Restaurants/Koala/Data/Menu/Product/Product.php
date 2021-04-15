<?php


namespace App\Restaurants\Koala\Data\Menu\Product;

/**
 * Container class for a product in a menu
 * @package App\Restaurants\Koala\Data\Menu
 */
class Product
{
    public string $id;
    public string $type = 'product';
    public string $name;
    public string $description;
    /**
     * @var Variation[]
     */
    public array $variations;
}
