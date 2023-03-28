<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Plugin\Field\FieldFormatter\GenericFileFormatter;

/**
 * Plugin implementation of the 'rs_file_download_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "rs_file_download_formatter",
 *   label = @Translation("Download button"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class DownloadLinkFileFormatter extends GenericFileFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();

    $settings['fallback_title'] = 'Download';

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['fallback_title'] = [
      '#title' => $this->t('Use this text as link text'),
      '#description' => $this->t('Replace the file link with this text when the description is empty.'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('fallback_title'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($fallback = $this->getSetting('fallback_title')) {
      $summary[] = $this->t('Use @fallback_title as link text', ['@fallback_title' => $fallback]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $item = $file->_referringItem;

      $fallback = $this->getSetting('fallback_title');
      $description = $this->getSetting('use_description_as_link_text') ? $item->description : NULL;
      if (empty($description)) {
        $description = $fallback;
      }

      $elements[$delta] = [
        '#theme' => 'file_link',
        '#file' => $file,
        '#description' => $description,
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
