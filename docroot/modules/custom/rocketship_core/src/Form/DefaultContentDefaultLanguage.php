<?php

namespace Drupal\rocketship_core\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DefaultContentDefaultLanguage.
 */
class DefaultContentDefaultLanguage extends ConfigFormBase {

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'rocketship_core.defaultcontentdefaultlanguage',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'default_content_default_language';
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $languageManager) {
    parent::__construct($config_factory);
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = $this->t('Default Content Default Language');
    $form['info']['#markup'] = $this->t('Set the language to use when migrating default and demo content. This may differ from the site\'s actual default language, which could be disabled.');

    $languages = $this->languageManager->getLanguages();
    $options = [];

    foreach ($languages as $language) {
      $options[$language->getId()] = $language->getName();
    }

    $config = $this->config('rocketship_core.defaultcontentdefaultlanguage');
    $form['default_language'] = [
      '#type' => 'select',
      '#title' => $this->t('Default language'),
      '#options' => $options,
      '#default_value' => !empty($config->get('default_language')) ? $config->get('default_language') : $this->languageManager->getDefaultLanguage()
        ->getId(),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('rocketship_core.defaultcontentdefaultlanguage')
      ->set('default_language', $form_state->getValue('default_language'))
      ->save();
  }

}
