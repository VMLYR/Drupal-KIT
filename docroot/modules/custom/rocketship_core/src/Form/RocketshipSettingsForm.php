<?php

namespace Drupal\rocketship_core\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rocketship_core\Plugin\Layout\RocketshipCoreBaseLayout;

/**
 * Class RocketshipSettingsForm.
 */
class RocketshipSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'rocketship_core.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rocketship_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('rocketship_core.settings');

    $variants = $config->get('color_variants');

    $variationAmount = $form_state->get('variationAmount');

    if (!($variationAmount)) {
      if (empty($variants)) {
        $variationAmount = 1;
      }
      else {
        $variationAmount = count($variants);
      }
    }
    $form_state->set('variationAmount', $variationAmount);

    $form['css_structural'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable Structural CSS'),
      '#default_value' => $config->get('css_structural'),
      '#description' => t('Loads a CSS file with layout styling. If disabled, make sure you have your own theming for the layout options in place (eg. using a Dropsolid Rocketship theme).'),
    ];

    $form['css_colors'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable Colors CSS'),
      '#default_value' => $config->get('css_colors'),
      '#description' => t('Loads a CSS file that adds styling for the generated color variants. If disabled, make sure you have your own theming for these color variants (eg. using a Dropsolid Rocketship theme).'),
    ];

    $form['color_variants_group'] = [
      '#type' => 'fieldset',
      '#title' => t('Color variants'),
      '#description' => t("Leave a variation's fields empty to delete it.<br>See the README for more information."),
      '#open' => TRUE,
      '#attributes' => ['id' => 'color-variants-wrapper'],
      '#tree' => TRUE,
    ];

    $i = 0;
    while ($i < $variationAmount) {

      $form['color_variants_group'][$i] = [
        '#type' => 'fieldset',
        '#title' => t('Variant'),
        '#open' => TRUE,
      ];

      $form['color_variants_group'][$i]['name'] = [
        '#type' => 'textfield',
        '#title' => t('Name'),
        '#default_value' => isset($variants[$i]['name']) ? $variants[$i]['name'] : NULL,
        '#description' => t('A name to give this variant. It will be used to set a class on the paragraph'),
      ];

      $form['color_variants_group'][$i]['foreground'] = [
        '#type' => 'textfield',
        '#title' => t('Foreground color'),
        '#default_value' => isset($variants[$i]['foreground']) ? $variants[$i]['foreground'] : NULL,
        '#description' => t('Fill in a HEX value. This sets the color of the text and various other elements inside the paragraph'),
      ];

      $form['color_variants_group'][$i]['background'] = [
        '#type' => 'textfield',
        '#title' => t('Background color'),
        '#default_value' => isset($variants[$i]['background']) ? $variants[$i]['background'] : NULL,
        '#description' => t("Fill in a HEX value. This sets the color of the paragraph's background"),
      ];

      $form['color_variants_group'][$i]['link'] = [
        '#type' => 'textfield',
        '#title' => t('Link color'),
        '#default_value' => isset($variants[$i]['link']) ? $variants[$i]['link'] : NULL,
        '#description' => t('Fill in a HEX value. This sets the color of the links inside the paragraph'),
      ];

      $form['color_variants_group'][$i]['hover'] = [
        '#type' => 'textfield',
        '#title' => t('Link hover color'),
        '#default_value' => isset($variants[$i]['hover']) ? $variants[$i]['hover'] : NULL,
        '#description' => t('Fill in a HEX value. This sets the color of the link hovers inside the paragraph'),
      ];

      $i++;

    };

    $form['actions']['#type'] = 'actions';

    $form['actions']['add_item'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another variation'),
      '#submit' => ['::rpSettingsAddItem'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::rpSettingAjaxCallback',
        'wrapper' => 'color-variants-wrapper',
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $variants = $form_state->getValue('color_variants_group', []);

    foreach ($variants as $idx => $values) {
      $empties = 0;
      foreach ($values as $key => $val) {
        if (empty($val)) {
          $empties++;
          continue;
        }
        if ($key === 'name') {
          continue;
        }
        $valid = preg_match('/^#([A-Fa-f0-9]{3}){1,2}\b$/', $val);
        if (!$valid) {
          $form_state->setError($form['color_variants_group'][$idx][$key], t('Invalid HEX code'));
        }
      }
      if ($empties !== 0 && $empties !== 5) {
        // They're not all empty or filled in.
        $form_state->setError($form['color_variants_group'][$idx], t('Please either leave all fields empty for a variation or fill in each field.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $variants = $form_state->getValue('color_variants_group', []);

    // Strip empty variants.
    foreach ($variants as $idx => $values) {
      if (empty($values['name'])) {
        unset($variants[$idx]);
      }
    }
    // Re-index.
    $variants = array_values($variants);
    foreach ($variants as &$variant) {
      $variant['name'] = str_replace(['_', ' '], '-', $variant['name']);
      $variant['name'] = strtolower($variant['name']);
    }

    $this->config('rocketship_core.settings')
      ->set('color_variants', $variants)
      ->set('css_colors', $form_state->getValue('css_colors'))
      ->set('css_structural', $form_state->getValue('css_structural'))
      ->save();

    // Generate CSS file.
    RocketshipCoreBaseLayout::generateBackgroundColorsCSSFile();
  }


  /**
   * Implements callback for add more event.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function rpSettingsAddItem(array $form, FormStateInterface $form_state) {
    $variationAmount = $form_state->get('variationAmount');
    $variationAmount++;
    $form_state->set('variationAmount', $variationAmount);
    $form_state->setRebuild();
  }

  /**
   * Returns the part of the form for the Ajax callback.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Part of the form to return.
   */
  public function rpSettingAjaxCallback(array $form, FormStateInterface $form_state) {
    return $form['color_variants_group'];
  }

}
