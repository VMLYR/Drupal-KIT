<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "multiviewmode_entityref_entity_view",
 *   label = @Translation("Multiple View Mode Rendered entity"),
 *   description = @Translation("Display the referenced entities rendered by entity_view(). Select different view mode per bundle."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MultiViewModeEntityReferenceEntityFormatter extends EntityReferenceEntityFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'view_modes' => [
        'default' => 'default',
      ],
      'link' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $view_modes = $this->getSetting('view_modes');

    $entity_type_id = $this->getFieldSetting('target_type');

    $bundles = \Drupal::service('entity_type.bundle.info')
      ->getBundleInfo($entity_type_id);

    $elements['view_modes']['default'] = [
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions($entity_type_id),
      '#title' => t('View mode (fallback)'),
      '#description' => t('Select the default view mode for any unselected bundles.'),
      '#default_value' => isset($view_modes['default']) ? $view_modes['default'] : 'default',
      '#required' => TRUE,
    ];

    foreach ($bundles as $key => $info) {
      $options = $this->entityDisplayRepository->getViewModeOptionsByBundle($entity_type_id, $key);
      $elements['view_modes'][$key] = [
        '#type' => 'select',
        '#options' => $options,
        '#title' => t('View mode for :bundle', [':bundle' => $info['label']]),
        '#default_value' => isset($view_modes[$key]) ? $view_modes[$key] : 'default',
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $view_modes = $this->getSetting('view_modes');
    $entity_type_id = $this->getFieldSetting('target_type');
    $bundles = \Drupal::service('entity_type.bundle.info')
      ->getBundleInfo($entity_type_id);
    foreach ($bundles as $key => $info) {
      $default = isset($view_modes[$key]) ? $view_modes[$key] : $view_modes['default'];
      $summary[] = t('Render bundle @bundle as @mode', [
        '@bundle' => $info['label'],
        '@mode' => $default,
      ]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $view_modes = $this->getSetting('view_modes');
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      // Due to render caching and delayed calls, the viewElements() method
      // will be called later in the rendering process through a '#pre_render'
      // callback, so we need to generate a counter that takes into account
      // all the relevant information about this field and the referenced
      // entity that is being rendered.
      $recursive_render_id = $items->getFieldDefinition()
          ->getTargetEntityTypeId()
        . $items->getFieldDefinition()->getTargetBundle()
        . $items->getName()
        // We include the referencing entity, so we can render default images
        // without hitting recursive protections.
        . $items->getEntity()->id()
        . $entity->getEntityTypeId()
        . $entity->id();

      if (isset(static::$recursiveRenderDepth[$recursive_render_id])) {
        static::$recursiveRenderDepth[$recursive_render_id]++;
      }
      else {
        static::$recursiveRenderDepth[$recursive_render_id] = 1;
      }

      // Protect ourselves from recursive rendering.
      if (static::$recursiveRenderDepth[$recursive_render_id] > static::RECURSIVE_RENDER_LIMIT) {
        $this->loggerFactory->get('entity')
          ->error('Recursive rendering detected when rendering entity %entity_type: %entity_id, using the %field_name field on the %bundle_name bundle. Aborting rendering.', [
            '%entity_type' => $entity->getEntityTypeId(),
            '%entity_id' => $entity->id(),
            '%field_name' => $items->getName(),
            '%bundle_name' => $items->getFieldDefinition()->getTargetBundle(),
          ]);
        return $elements;
      }

      $view_builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
      $view_mode = isset($view_modes[$entity->bundle()]) ? $view_modes[$entity->bundle()] : $view_modes['default'];
      $elements[$delta] = $view_builder->view($entity, $view_mode, $entity->language()
        ->getId());

      // Add a resource attribute to set the mapping property's value to the
      // entity's url. Since we don't know what the markup of the entity will
      // be, we shouldn't rely on it for structured data such as RDFa.
      if (!empty($items[$delta]->_attributes) && !$entity->isNew() && $entity->hasLinkTemplate('canonical')) {
        $items[$delta]->_attributes += [
          'resource' => $entity->toUrl()
            ->toString(),
        ];
      }
    }

    return $elements;
  }

}
