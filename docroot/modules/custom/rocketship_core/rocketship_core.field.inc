<?php

/**
 * @file
 * Contains preprocess function.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for idt widget templates.
 *
 * Default template: idt-widget.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: A render element representing the image field widget.
 */
function template_preprocess_idt_widget(array &$variables) {
  $element = $variables['element'];

  $variables['attributes'] = [
    'class' => [
      'image-widget',
      'js-form-managed-file',
      'form-managed-file',
      'clearfix',
    ],
  ];

  if (!empty($element['fids']['#value'])) {
    $file = reset($element['#files']);
    $element['file_' . $file->id()]['filename']['#suffix'] = ' <span class="file-size">(' . format_size($file->getSize()) . ')</span> ';
  }

  $variables['data'] = [];
  foreach (Element::children($element) as $child) {
    $variables['data'][$child] = $element[$child];
  }

}
