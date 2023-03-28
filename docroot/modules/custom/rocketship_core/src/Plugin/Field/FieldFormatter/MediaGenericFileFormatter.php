<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation of the 'media_generic_file_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "media_generic_file_formatter",
 *   label = @Translation("Media-ready Generic file formatter"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class MediaGenericFileFormatter extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /** @var \Drupal\media\MediaInterface $media */
    $media = $items->getParent()->getEntity();
    if ($media instanceof TranslatableInterface) {
      $media = \Drupal::service('entity.repository')
        ->getTranslationFromContext($media, $langcode);
    }

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $item = $file->_referringItem;
      $elements[$delta] = [
        '#theme' => 'file_link',
        '#file' => $file,
        '#description' => $media->getName(),
        '#cache' => [
          'tags' => $file->getCacheTags(),
        ],
      ];
      // Pass field item attributes to the theme function.
      if (isset($item->_attributes)) {
        $elements[$delta] += ['#attributes' => []];
        $elements[$delta]['#attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }
    }

    return $elements;
  }

}
