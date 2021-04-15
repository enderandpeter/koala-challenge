<?php


namespace App\Restaurants\Koala\Data\Menu\Category;

/**
 * Container class for modifiers, which are options that pertain to any product in a category
 * @package App\Restaurants\Koala\Data\Menu\Category
 */
class Modifier
{
    public string $id;
    public string $name;
    /**
     * @var Modification[]|null
     */
    public ?array $modifications;
}
