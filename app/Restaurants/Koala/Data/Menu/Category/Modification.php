<?php


namespace App\Restaurants\Koala\Data\Menu\Category;

/**
 * Container class for the possible options that can be chosen from a modifier
 *
 * @package App\Restaurants\Koala\Data\Menu\Category
 */
class Modification
{
    public string $id;
    public string $name;
    public ?float $price;
    public ?bool $onByDefault;
}
