<?php

declare(strict_types=1);

namespace Drupal\anytown\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;

class AnytownHelp
{
  /**
   * Implements hook_help().
   */
    #[Hook('help')]
    public function help($route_name, RouteMatchInterface $route_match): string
    {
        if ($route_name === 'help.page.anytown') {
            /** @var \Drupal\Core\Session\AccountProxyInterface $current_user */
            $current_user = \Drupal::service('current_user');

            return '<p>' . t("Hi %name, the anytown module provides code specific to the Anytown Farmer's market website.This includes the weather forecast page, block, and related settings.", ['%name' => $current_user->getDisplayName()]) . '</p>';
        }
        return '';
    }
}
