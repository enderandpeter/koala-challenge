<?php


namespace App\Restaurants;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

/**
 * Shared capabilities for classes that can retrieve restaurant data
 *
 * @package App\Restaurants
 */
abstract class Retriever
{
    public static array $dataTypes = [
      'location',
      'menu'
    ];

    public static array $daysOfWeek = [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday'
    ];

    public static string $timeFormat = 'H:i:s';

    protected static array $availableBillingMethods;

    private string $dataType;

    private string $locationDataUrl;
    protected LocationData $locationData;

    private string $menuDataUrl;
    protected MenuData $menuData;

    private ?string $error;
    private ?int $errorCode;

    protected Client $httpClient;

    protected array $config;

    public function __construct(
        Client $httpClient,
        LocationData $locationData,
        MenuData $menuData,
        array $config
    )
    {
        $this->httpClient = $httpClient;
        $this->locationData = $locationData;
        $this->menuData = $menuData;
        $this->config = $config;

        // Set the data URLs for each dataType
        collect($config['urls'])->each(function($url, $dataType){
            $this->{"${dataType}DataUrl"} = $url;
        });
    }

    /**
     * Get the raw remote data. This method will set the dataType for the Retriever.
     *
     * @param string $dataType
     * @return array
     */
    public function getRemoteData(string $dataType): array{
        $data = '';

        if(collect(self::$dataTypes)->contains($dataType)){
            $this->dataType = $dataType;

            if($this->{"${dataType}DataUrl"}){
                try{
                    $data = $this->httpClient->get($this->{"${dataType}DataUrl"})->getBody();
                } catch (GuzzleException $e){
                    report($e);
                    $this->error = "An error occurred when loading original $dataType data";
                    $this->errorCode = -2;
                }
            }
        }

        if($data){
            return compact('data');
        } else {
            return [
                'error' => [
                    'message' => $this->error ?? "Data for ($dataType) not found at " . $this->{"${dataType}DataUrl"},
                    'code' => $this->errorCode ?? -1
                ]
            ];
        }
    }

    public function getDataType(): string{
        return $this->dataType;
    }

    public function getRawLocationData(): array{
        return $this->getRemoteData('location');
    }

    public function getRawMenuData(): array{
        return $this->getRemoteData('menu');
    }

    public function getError(): string{
        return $this->error;
    }

    public function getErrorCode(): string{
        return $this->errorCode;
    }

    /**
     * Get the container object
     *
     */
    public function getLocationData(): LocationData | array
    {
        return $this->locationData;
    }

    /**
     * Set the container object
     *
     * @param LocationData $locationData
     */
    public function setLocationData(LocationData $locationData): void
    {
        $this->locationData = $locationData;
    }

    /**
     * Get the container object
     *
     */
    public function getMenuData(): MenuData | array
    {
        return $this->menuData;
    }

    /**
     * Get the container object
     *
     * @param MenuData $menuData
     */
    public function setMenuData(MenuData $menuData): void
    {
        $this->menuData = $menuData;
    }
}
