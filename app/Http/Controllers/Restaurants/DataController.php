<?php

namespace App\Http\Controllers\Restaurants;

use App\Http\Controllers\Controller;
use App\Restaurants\InfoRetriever;
use App\Restaurants\LocationData;
use App\Restaurants\Menu;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;

/**
 * Manage restaurant data
 *
 * @package App\Http\Controllers\Restaurants
 */
class DataController extends Controller
{
    private InfoRetriever $infoRetriever;

    public function getData(int $id, string $dataType = 'location'): LocationData | Menu | array | JsonResponse
    {
        try {
            $this->infoRetriever = app()->makeWith(InfoRetriever::class,
                compact('id', 'dataType')
            );
        } catch(BindingResolutionException $e){
            report($e);
            return response()->json([
                'error' => "Could not find the utility for getting data for this restaurant"
            ], 500);
        } catch(\Exception $e){
            report($e);
            return response()->json([
                'error' => "An unexpected error occurred"
            ], 500);
        }

        return response()->json($this->infoRetriever->getData());
    }
}
