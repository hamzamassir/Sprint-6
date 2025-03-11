<?php

namespace Drupal\movie_directory\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'uppercase_text' formatter.
 */
#[FieldFormatter(
    id: "movie_uppercase_text",
    label: new TranslatableMarkup("Movie Uppercase Text"),
    field_types: ["string"]
)]
class UppercaseTextFormatter extends FormatterBase
{
    /**
     * {@inheritdoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode)
    {
        $elements = [];
        foreach ($items as $delta => $item) {
            $elements[$delta] = [
                '#markup' => strtoupper($item->value),
            ];
        }
        return $elements;
    }
}
