<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\video_embed_field\Plugin\Field\FieldFormatter\Colorbox;
use Drupal\video_embed_field\Plugin\Field\FieldFormatter\Video;

/**
 * Plugin implementation of the thumbnail field formatter.
 *
 * @FieldFormatter(
 *   id = "link_vef_colorbox",
 *   label = @Translation("Link Colorbox"),
 *   field_types = {
 *     "video_embed_field"
 *   }
 * )
 */
class LinkVideoEmbedColorbox extends Colorbox {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $videos = $this->videoFormatter->viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      // Support responsive videos within the colorbox modal.
      if ($this->getSetting('responsive')) {
        $videos[$delta]['#attributes']['class'][] = 'video-embed-field-responsive-modal';
        $videos[$delta]['#attributes']['style'] = sprintf('width:%dpx;', $this->getSetting('modal_max_width'));
      }

      $link_text = $this->t($this->getSetting('link_text'));
      $entity = $items->getEntity();
      $field_name = $this->getSetting('link_text_field');
      if ($entity->hasField($field_name) && !empty($entity->get($field_name)->value)) {
        $link_text = $entity->get($field_name)->value;
      }

      $element[$delta] = [
        '#type' => 'container',
        '#attributes' => [
          'data-video-embed-field-modal' => (string) $this->renderer->render($videos[$delta]),
          'class' => ['video-embed-field-launch-modal'],
        ],
        '#attached' => [
          'library' => [
            'video_embed_field/colorbox',
            'video_embed_field/responsive-video',
          ],
        ],
        // Ensure the cache context from the video formatter which was rendered
        // early still exists in the renderable array for this formatter.
        '#cache' => [
          'contexts' => ['user.permissions'],
        ],
        'children' => [
          'link' => Link::fromTextAndUrl($this->t($link_text), Url::fromUri($videos[$delta]['children']['#url']))
            ->toRenderable(),
        ],
      ];
    }
    $this->colorboxAttachment->attach($element);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return Video::defaultSettings() + [
      'modal_max_width' => '854',
      'link_text' => '',
      'link_text_field' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $element += $this->videoFormatter->settingsForm([], $form_state);
    $element['modal_max_width'] = [
      '#title' => $this->t('Maximum Width'),
      '#type' => 'number',
      '#description' => $this->t('The maximum size of the video opened in the Colorbox window in pixels. For smaller screen sizes, the video will scale.'),
      '#required' => TRUE,
      '#field_suffix' => 'px',
      '#size' => 20,
      '#states' => ['visible' => [[':input[name*="responsive"]' => ['checked' => TRUE]]]],
      '#default_value' => $this->getSetting('modal_max_width'),
    ];
    $element['link_text_field'] = [
      '#type' => 'textfield',
      '#title' => t('Link text field'),
      '#default_value' => $this->getSetting('link_text_field'),
      '#description' => t('A field machine name that will be used for the text of the video popup link'),
    ];
    $element['link_text'] = [
      '#type' => 'textfield',
      '#title' => t('Link text'),
      '#default_value' => $this->getSetting('link_text'),
      '#description' => t('If the field is not found or has no value, this fallback is used'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Link that launches a modal window.');
    $summary[] = implode(',', $this->videoFormatter->settingsSummary());
    $summary[] = t('Link text field: @field', ['@field' => $this->getSetting('link_text_field')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return parent::calculateDependencies() + $this->videoFormatter->calculateDependencies();
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    $changed = FALSE;
    if (!empty($this->thirdPartySettings) && !empty($dependencies['module'])) {
      $old_count = count($this->thirdPartySettings);
      $this->thirdPartySettings = array_diff_key($this->thirdPartySettings, array_flip($dependencies['module']));
      $changed = $old_count != count($this->thirdPartySettings);
    }
    $video = $this->videoFormatter->onDependencyRemoval($dependencies);
    return $changed || $video;
  }

}
