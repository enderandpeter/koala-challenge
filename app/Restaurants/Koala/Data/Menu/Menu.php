<?php


namespace App\Restaurants\Koala\Data\Menu;

/**
 * A container class for any restaurant's menu data
 *
 * @package App\Restaurants
 */
class Menu
{
    public int $id;
    /**
     * @var Category[]
     */
    public array $categories = [];
}
