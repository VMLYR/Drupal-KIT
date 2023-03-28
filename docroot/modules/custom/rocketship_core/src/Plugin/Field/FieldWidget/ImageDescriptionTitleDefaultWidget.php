<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;

/**
 * Plugin implementation of the 'imageDescriptionTitle_default_widget' widget.
 *
 * @FieldWidget(
 *   id = "imageDescriptionTitle_default_widget",
 *   label = @Translation("Default widget"),
 *   field_types = {
 *     "imagedescriptiontitle"
 *   }
 * )
 */
class ImageDescriptionTitleDefaultWidget extends ImageWidget {

  /**
   * Form API callback: Processes a image_image field element.
   *
   * Expands the image_image type to include the alt and title fields.
   * This method is assigned as a #process callback in formElement() method.
   *
   * {@inheritdoc}
   */
  public static function process($element,
                                 FormStateInterface $form_state,
                                 $form) {

    $item = $element['#value'];

    $element['idttitle'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => isset($item['idttitle']) ? $item['idttitle'] : '',
      '#description' => t('This is the title'),
      '#maxlength' => 1024,
      '#weight' => 1000,
      // Do this to hide the form element on the upload form.
      '#access' => (bool) $item['fids'],
      '#required' => (bool) $item['fids'],
    ];

    // Get the value & format.
    $textAreaValue = '';
    $format = NULL;
    if (is_array($item['idttextarea']) && isset($item['idttextarea']['value'], $item['idttextarea']['format'])) {
      $textAreaValue = $item['idttextarea']['value'];
      $format = $item['idttextarea']['format'];
    }
    elseif (!empty($item['idttextarea']) && is_string($item['idttextarea']) && isset($item['idttextareaformat'])) {
      $textAreaValue = $item['idttextarea'];
      $format = $item['idttextareaformat'];
    }

    // If the format isn't set we get the default one.
    if (!$format) {
      $account = \Drupal::currentUser();
      $filterFormats = filter_formats($account);
      $filterFormat = reset($filterFormats);
      $format = $filterFormat->get('format');
    }

    $element['idttextarea'] = [
      '#type' => 'text_format',
      '#default_value' => $textAreaValue,
      '#format' => $format,
      '#access' => (bool) $item['fids'],
      '#attributes' => ['class' => ['js-text-full', 'text-full']],
    ];

    $element = parent::process($element, $form_state, $form);

    // Make sure it uses our own template instead of the generic image one.
    $element['#theme'] = 'idt_widget';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = parent::massageFormValues($values, $form, $form_state);

    foreach ($values as &$item) {
      // Remap the values from the form to the field properties.
      $item['idttextareaformat'] = $item['idttextarea']['format'];
      $item['idttextarea'] = $item['idttextarea']['value'];
    }
    return $values;
  }

}
