<?php

namespace Drupal\rocketship_core\Form;

use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rocketship_core\MigrateGenerator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class RocketshipSettingsForm.
 */
class MigrateGeneratorForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rs_migrate_generator';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['module_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Migration Module Name'),
      '#required' => TRUE,
      '#default_value' => 'rs_generated_migrate',
    ];

    $content_entity_types = array_reduce(\Drupal::entityTypeManager()->getDefinitions(), function (array $carry, EntityTypeInterface $definition) {
      if (!$definition instanceof ContentEntityType) {
        return $carry;
      }
      $carry[$definition->id()] = t('@plural-label (<code>@provider</code>)', [
        '@plural-label' => ucfirst((string) $definition->getPluralLabel()),
        '@provider' => $definition->getProvider(),
      ]);
      return $carry;
    }, []);
    ksort($content_entity_types);

    $form['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select entity type to generate migrate for'),
      '#options' => $content_entity_types,
      '#default_value' => current(array_keys($content_entity_types)),
      '#ajax' => [
        'callback' => '::myAjaxCallback',
        'disable-refocus' => FALSE,
        'event' => 'change',
        'wrapper' => 'entity-output',
      ],
    ];

    $form['entities'] = [
      '#type' => 'entity_autocomplete',
      '#maxlength' => 5000,
      '#target_type' => $form_state->getValue('entity_type', current(array_keys($content_entity_types))),
      '#tags' => TRUE,
      '#title' => $this->t('Entities to migrate'),
      '#ajax' => [],
      '#prefix' => '<div id="entity-output">',
      '#suffix' => '</div>',
    ];

    $form['default_content_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Default content mode'),
      '#description' => $this->t('Replaced langcode with callback that respects default content language selected for Rocketship'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];

    return $form;
  }

  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    $form['entities']['#target_type'] = $form_state->getValue('entity_type');
    return $form['entities'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $list = $form_state->getValue('entities') ?? [];
    $entity_ids = array_map(function ($item) {
      return $item['target_id'];
    }, $list);

    $generator = new MigrateGenerator(
      $form_state->getValue('entity_type'),
      [],
      $entity_ids,
      $form_state->getValue('module_name'),
      $form_state->getValue('default_content_mode', FALSE)
    );
    /** @var \Drupal\Core\File\FileSystemInterface $system */
    $system = \Drupal::service('file_system');

    $dir = $generator->generateMigrate();
    if (file_exists($dir . ".tar.gz")) {
      $system->deleteRecursive($dir . ".tar.gz");
    }
    $archive = new ArchiveTar($dir . ".tar.gz");
    $archive->addModify([$dir], '', $dir);
    $system->deleteRecursive($dir);
    $headers = [
      'Content-disposition' => 'attachment; filename="' . $form_state->getValue('module_name') . ".tar.gz" . '"',
    ];
    $form_state->setResponse(new BinaryFileResponse($dir . ".tar.gz", 200, $headers, TRUE));
  }

}
