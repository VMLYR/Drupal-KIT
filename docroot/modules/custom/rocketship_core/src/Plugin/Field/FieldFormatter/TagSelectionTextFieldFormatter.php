<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'header_text_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "tag_selection_textfield_formatter",
 *   label = @Translation("Tag Selection Textfield Formatter"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class TagSelectionTextFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'wrapper_override' => 'nothing',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['wrapper_override'] = [
      '#type' => 'select',
      '#title' => $this->t('Override wrapper selection'),
      '#description' => $this->t('Select a tag to wrap this output in, overriding the selection made by the client.'),
      '#default_value' => $this->getSetting('wrapper_override'),
      '#options' => [
        'nothing' => $this->t('Nothing'),
        'h1' => $this->t('h1'),
        'h2' => $this->t('h2'),
        'h3' => $this->t('h3'),
        'h4' => $this->t('h4'),
        'h5' => $this->t('h5'),
        'h6' => $this->t('h6'),
        'span' => $this->t('span'),
      ],
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary[] = t('Wrapper override: @override', ['@override' => $this->getSetting('wrapper_override')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      [$value, $tag] = array_pad(explode('***', $item->value), 2, 'h1');
      if ($this->getSetting('wrapper_override') !== 'nothing') {
        $tag = $this->getSetting('wrapper_override');
      }
      if (!$value) {
        continue;
      }

      // $value = '<' . $tag . '>' . $item->value . '</' . $tag . '>';.
      $elements[$delta] = [
        '#prefix' => '<' . $tag . '>',
        '#suffix' => '</' . $tag . '>',
        '#type' => 'inline_template',
        '#template' => '{{ value|nl2br }}',
        '#context' => ['value' => $value],
      ];
    }

    return $elements;
  }

}
