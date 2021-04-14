<?php


namespace App\Restaurants;

use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Retrieves restaurant info
 *
 * @package App\Restaurants
 */
class InfoRetriever
{
    private string $dataRetrievalMethodName;
    private Retrievable $retrievable;

    /**
     * InfoRetriever constructor.
     * @param int $id
     * @param string $dataType
     * @throws BindingResolutionException
     */
    public function __construct(int $id, string $dataType = 'location')
    {
        $dataConfig = config('restaurants')[$id];
        $retrievableClassName = $dataConfig['class'];
        $dataConfig['id'] = $id;

        $this->retrievable = app()->makeWith($retrievableClassName, ['config' => $dataConfig]);

        $capitalizedDataType = ucfirst($dataType);
        $dataRetrievalMethodName = "get${capitalizedDataType}Data";
        $this->dataRetrievalMethodName = $dataRetrievalMethodName;
    }

    public function getData(): LocationData | MenuData | array{
        return $this->retrievable->{$this->dataRetrievalMethodName}();
    }
}
