<?php

namespace Drupal\c11n_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Custom process plugin to trim and clean string
 *
 * @MigrateProcessPlugin(
 *   id = "custom_string_trim"
 * )
 */
class CustomStringTrim extends ProcessPluginBase
{
    public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property)
    {
        // Trim to specified length, convert to lowercase
        $length = $this->configuration['length'] ?? 50;
        return substr(strtolower(trim($value)), 0, $length);
    }
}
