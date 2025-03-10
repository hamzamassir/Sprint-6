<?php

declare(strict_types=1);

namespace Drupal\anytown;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Forecast retrieval API client.
 */
class ForecastClient implements ForecastClientInterface
{
    /**
     * Guzzle HTTP client.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * Logger.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * ForecastClient constructor.
     *
     * @param \GuzzleHttp\ClientInterface $http_client
     *   HTTP client.
     * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
     *   Logger factory.
     */
    public function __construct(ClientInterface $http_client, LoggerChannelFactoryInterface $logger_factory)
    {
        $this->httpClient = $http_client;
        $this->logger = $logger_factory->get('anytown');
    }

    /**
     *{@inheritdoc}
     */
    public function getForecastData(string $url): array
    {
        try {
            $response = $this->httpClient->get($url);
            $json = json_decode($response->getBody()->getContents());
        } catch (GuzzleException $e) {
            $this->logger->warning($e->getMessage());
            return null;
        }
        $forecast = [];
        foreach ($json->list as $day) {
            $forecast[$day->day] = [
                'weekday' => ucfirst($day->day),
                'description' => $day->weather[0]->description,
                'high' => $this->kelvinToFahrenheit($day->main->temp_max),
                'low' => $this->kelvinToFahrenheit($day->main->temp_min),
                'icon' => $day->weather[0]->icon,
            ];
        }

        return $forecast;
    }

    /**
     * Convert Kelvin to Fahrenheit.
     *
     * @param float $kelvin
     *   Temperature in Kelvin.
     *
     * @return float
     *   Temperature in Fahrenheit.
     */
    public static function kelvinToFahrenheit(float $kelvin): float
    {
        return round(($kelvin - 273.15) * 9 / 5 + 32);
    }
}
