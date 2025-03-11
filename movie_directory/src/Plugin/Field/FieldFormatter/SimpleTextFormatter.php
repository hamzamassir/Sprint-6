<?php

namespace Drupal\movie_directory\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'simple_text' formatter.
 */
#[FieldFormatter(
    id: "movie_simple_text",
    label: new TranslatableMarkup("Movie Simple Text"),
    field_types: ["string"]
)]
class SimpleTextFormatter extends FormatterBase
{
    /**
     * {@inheritdoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode)
    {
        $elements = [];
        foreach ($items as $delta => $item) {
            $elements[$delta] = [
                '#markup' => $item->value,
            ];
        }
        return $elements;
    }
}
