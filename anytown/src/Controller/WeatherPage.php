<?php

declare(strict_types=1);

namespace Drupal\anytown\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\anytown\ForecastClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for anytown.weather_page route.
 */
class WeatherPage extends ControllerBase
{
    /**
     * The forecast client.
     *
     * @var \Drupal\anytown\ForecastClientInterface
     */
    private $forecastClient;
    /**
     * Constructs a new WeatherPage object.
     *
     * @param \Drupal\anytown\ForecastClientInterface $forecast_client
     *   The forecast client.
     */
    public function __construct(ForecastClientInterface $forecast_client)
    {
        $this->forecastClient = $forecast_client;
    }
    /**
     *{inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('anytown.forecast_client')
        );
    }
    /**
    * Builds the response.
    */
    public function build(string $style): array
    {
    // Style should be one of 'short', or 'extended'. And default to 'short'.
        $style = (in_array($style, ['short', 'extended'])) ? $style : 'short';
        $settings = $this->config('anytown.settings');


        $url = 'https://raw.githubusercontent.com/DrupalizeMe/module-developer-guide-demo-site/main/backups/weather_forecast.json';
        $forecast_data = $this->forecastClient->getForecastData($url);
        $rows = [];
        if ($forecast_data) {
            foreach ($forecast_data as $item) {
                [
                'weekday' => $weekday,
                'description' => $description,
                'high' => $high,
                'low' => $low,
                'icon' => $icon,
                ] = $item;

                $rows[] = [
                $weekday,
                // Complex data for a cell, like HTML, can be represented as a nested
                // render array.
                [
                    'data' => [
                    '#markup' => "<img alt=\"{$description}\" src=\"{$icon}\" width=\"200\" height=\"200\" />",
                    ],
                ],
                [
                    'data' => [
                    '#markup' => "<em>{$description}</em> with a high of {$high} and a low of {$low}",
                    ],
                ],
                ];
            }

            $weather_forecast = [
                '#type' => 'table',
                '#header' => [
                'Day',
                '',
                'Forecast',
                ],
                '#rows' => $rows,
                '#attributes' => [
                'class' => ['weather_page--forecast-table'],
                ],
            ];
        } else {
            // Or, display a message if we can't get the current forecast.
            $weather_forecast = ['#markup' => '<p>Could not get the weather forecast. Dress for anything.</p>'];
        }

        $build = [
        'weather_intro' => [
            '#markup' => "<p>Check out this weekend's weather forecast and come prepared. The market is mostly outside, and takes place rain or shine.</p>",
        ],
        'weather_forecast' => $weather_forecast,
        'weather_closures' => [
            '#theme' => 'item_list',
            '#title' => 'Weather related closures',
            '#items' => [
            'Ice rink closed until winter - please stay off while we prepare it.',
            'Parking behind Apple Lane is still closed from all the rain last weekend.',
            ],
        ],
        '#cache' => [
        // This content will vary if the settings for the module change, so we
        // specify that here using cache tags.
        //
        // This will end up looking like 'config:anytown.settings' but when
        // available it's better to use the getCacheTags() method to retrieve
        // tags rather than hard-code them.
        'tags' => $settings->getCacheTags(),
        // Remember, this page can be accessed via multiple URLs, like /weather
        // and /weather/extended. And varies depending on the URL, so we also
        // need to add a cache context for the URL so that the content is cached
        // per-url.
        'contexts' => ['url'],
        ],
        ];
        return $build;
    }
}
