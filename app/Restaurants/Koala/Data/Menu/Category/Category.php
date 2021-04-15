<?php


namespace App\Restaurants\Koala\Data\Menu\Category;

use App\Restaurants\Koala\Data\Menu\Product\Product;

/**
 * Container class for categories
 * @package App\Restaurants\Koala\Data\Menu
 */
class Category
{
    public string $id;
    public string $name;
    public string $type = 'category';
    /**
     * @var Product[]
     */
    public array $products = [];
    /**
     * @var Modifier[]|null
     */
    public ?array $modifiers;
}
