<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Formatter.
 *
 * Plugin implementation of the 'entity reference rendered entity (related
 * padding)' formatter.
 *
 * @FieldFormatter(
 *   id = "related_reference_entity_view",
 *   label = @Translation("Rendered entity (related padding)"),
 *   description = @Translation("Display the referenced entities rendered by
 *   entity_view(), padded until cardinality is reached based on another
 *   reference field."), field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class RelatedPaddedReferenceItemFormatter extends EntityReferenceEntityFormatter implements ContainerFactoryPluginInterface {

  /**
   * Constants used internally.
   */
  const
    _AND = 'AND',
    _OR = 'OR',
    ASCENDING = 'ASC',
    DESCENDING = 'DESC';

  /**
   * Var.
   *
   * @var bool
   */
  protected $padded = FALSE;

  /**
   * Var.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $fieldManager;

  /**
   * Var.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Var.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * ListofentityIDstoexclude.
   *
   * @var array
   */
  protected $exclude = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $class = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    /** @var static $class */
    $class->setExtraServices($container);

    return $class;
  }

  /**
   * Inject some extra services.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container.
   */
  public function setExtraServices(ContainerInterface $container) {
    $this->fieldManager = $container->get('entity_field.manager');
    $this->languageManager = $container->get('language_manager');
    $this->entityRepository = $container->get('entity.repository');
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [
      'reference_selection_fields' => [],
      'between_fields_conjunction' => static::_OR,
      'sort' => 'created',
      'sort_direction' => static::DESCENDING,
      'limit' => '-1',
      'force_padding' => TRUE,
    ];
    return $settings + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['reference_selection_fields'] = [
      '#type' => 'select',
      '#title' => $this->t('Reference selection field'),
      '#description' => $this->t('Select fields to use to determine "related" entities. Entities that have one or more of the same entities referenced will be used to pad the result set until cardinality is reached.'),
      '#default_value' => $this->getSetting('reference_selection_fields'),
      '#options' => static::getOtherFieldsForThisBundle($this->fieldDefinition, 'entity_reference'),
      '#multiple' => TRUE,
    ];

    $elements['between_fields_conjunction'] = [
      '#type' => 'select',
      '#title' => $this->t('Conjunction between multiple fields'),
      '#default_value' => $this->getSetting('between_fields_conjunction'),
      '#options' => [
        static::_OR => static::_OR,
        static::_AND => static::_AND,
      ],
    ];

    $elements['sort'] = [
      '#type' => 'select',
      '#title' => $this->t('Sort by field'),
      '#description' => $this->t('Select a field to sort by'),
      '#default_value' => $this->getSetting('sort'),
      '#options' => static::getOtherFieldsForThisBundle($this->fieldDefinition),
    ];

    $elements['sort_direction'] = [
      '#type' => 'select',
      '#title' => $this->t('Sort direction'),
      '#description' => $this->t('Select sort direction'),
      '#default_value' => $this->getSetting('sort_direction'),
      '#options' => [
        static::DESCENDING => static::DESCENDING,
        static::ASCENDING => static::ASCENDING,
      ],
    ];

    $elements['limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pad the list so it reaches X elements'),
      '#default_value' => $this->getSetting('limit'),
      '#description' => $this->t('Set to -1 to pad until cardinality is reached'),
    ];

    $elements['force_padding'] = [
      '#title' => $this->t('Force padding'),
      '#description' => $this->t('If not enough entities can be found with the relationship to reach the padding limit, disregard the relationship to fill in the last remaining spots.'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('force_padding'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary[] = t('Relating entities based on @field using @conj conjunction', [
      '@field' => implode(', ', $this->getSetting('reference_selection_fields')),
      '@conj' => $this->getSetting('between_fields_conjunction'),
    ]);
    $summary[] = t('Sorting on @field @dir', [
      '@field' => $this->getSetting('sort'),
      '@dir' => $this->getSetting('sort_direction'),
    ]);
    $limit = $this->getSetting('limit') > 0 ? $this->getSetting('limit') : 'cardinality';
    $status = $this->getSetting('force_padding') ? 'enabled' : 'disabled';
    $summary[] = t('Padding to reach @limit, force padding @status', [
      '@limit' => $limit,
      '@status' => $status,
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $elements = parent::view($items, $langcode);

    if ($this->padded) {
      $field_level_access_cacheability = new CacheableMetadata();
      // Because we add entities based on a query, we have to add list tag
      // List tag is the same as the entity the field is attached to because
      // that's part of our isApplicable check.
      $field_level_access_cacheability->addCacheTags($items->getEntity()
        ->getEntityType()
        ->getListCacheTags());
      $field_level_access_cacheability->merge(CacheableMetadata::createFromRenderArray($elements))
        ->applyTo($elements);
    }

    return $elements;
  }

  /**
   * Get the padding limit.
   *
   * @return int
   *   The limit.
   */
  protected function getPaddingLimit() {
    // Base limit on cardinality.
    $max_entities = $this->getSetting('limit');
    if ($max_entities < 1) {
      // Formatter limit set to -1, use cardinality.
      $max_entities = $this->fieldDefinition
        ->getFieldStorageDefinition()
        ->getCardinality();
    }

    return $max_entities;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode) {
    $entities = [];
    $this->exclude = [];
    $parent = $items->getEntity();

    // Do the normal thing, but add their IDs to $this->exclude.
    foreach ($items as $delta => $item) {
      $this->exclude[] = $item->target_id;
      // Ignore items where no entity could be loaded in prepareView().
      if (!empty($item->_loaded)) {
        $entity = $item->entity;

        // Set the entity in the correct language for display.
        if ($entity instanceof TranslatableInterface) {
          $entity = $this->entityRepository->getTranslationFromContext($entity, $langcode);
        }

        $access = $this->checkAccess($entity);
        // Add the access result's cacheability, ::view() needs it.
        $item->_accessCacheability = CacheableMetadata::createFromObject($access);
        if ($access->isAllowed()) {
          // Add the referring item, in case the formatter needs it.
          $entity->_referringItem = $items[$delta];
          $entities[$delta] = $entity;
        }
      }
    }

    // Expand list of excluded entities to include itself.
    $this->exclude = array_merge($this->exclude, [$parent->id()]);

    // Check reference fields selected.
    $reference_fields = $this->getSetting('reference_selection_fields');
    if (empty($reference_fields) && !$this->getSetting('force_padding')) {
      // Nothing to base relationship on and no force padding requested.
      return $entities;
    }

    // Get max amount of entities.
    $max_entities = $this->getPaddingLimit();

    if ($max_entities < 1) {
      // We're dealing with inf max, we can't do anything more.
      return $entities;
    }

    // Determine if we have to pad anything.
    $missing = $max_entities - count($entities);
    if ($missing < 1) {
      // Already fully padded.
      return $entities;
    }

    // Set internal flag to notify that this field should and could be padded
    // is used to add {entity_type}_list to the cache tags. There's no need
    // to add it when the field itself is already fully padded or it's
    // impossible to pad due to settings.
    $this->padded = TRUE;

    // Build up references.
    $references = [];
    foreach ($reference_fields as $reference_field) {
      foreach ($parent->get($reference_field) as $item) {
        $references[$reference_field][$item->target_id] = $item->target_id;
      }
    }
    if (empty($references) && !$this->getSetting('force_padding')) {
      // Nothing to base relation on and no force padding requested.
      return $entities;
    }

    $entities = $this->getEntities($entities, $parent, $langcode, $references);

    return $entities;
  }

  /**
   * Build the basic query.
   *
   * @param \Drupal\Core\Entity\EntityInterface $parent
   *   The parent entity.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   A query.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function buildBasicQuery(EntityInterface $parent) {
    $loaded_entity_type = $parent->getEntityType();
    $entity_type = $loaded_entity_type->id();

    $query = $this->entityTypeManager->getStorage($entity_type)->getQuery();
    // If entity has status field, use it.
    $status_key = $loaded_entity_type->getKey('status');
    if ($status_key) {
      $query->condition($status_key, 1);
    }
    // Make sure bundles are respected IF the entity type has a bundle
    // If the bundle == entity type, this is an entity type without bundles.
    $bundle_key = $loaded_entity_type->getKey('bundle');
    if ($bundle_key && $parent->bundle() !== $parent->getEntityTypeId()) {
      $query->condition($bundle_key, $parent->bundle());
    }
    // If entity supports translations, only fetch entities with translations
    // in the current language.
    $langcode_key = $loaded_entity_type->getKey('langcode');
    if ($langcode_key) {
      $query->condition(
        $langcode_key,
        [
          Language::LANGCODE_NOT_SPECIFIED,
          $this->languageManager->getCurrentLanguage()->getId(),
        ],
        'IN'
      );
    }
    // Add access check.
    $query->accessCheck(TRUE);
    // Make sure we don't fetch the same entities already present in the field
    // or itself.
    $query->condition($loaded_entity_type->getKey('id'), $this->exclude, 'NOT IN');
    // Add the selected sort.
    $query->sort($this->getSetting('sort'), $this->getSetting('sort_direction'));

    return $query;
  }

  /**
   * Get entities to show for this field.
   *
   * @param array $entities
   *   All entities from the field.
   * @param \Drupal\Core\Entity\EntityInterface $parent
   *   Parent.
   * @param string $langcode
   *   Langcode.
   * @param array $references
   *   References.
   * @param bool $first
   *   First.
   *
   * @return array
   *   List of entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getEntities(array $entities,
                                 EntityInterface $parent,
                                 $langcode,
                                 array $references,
                                 $first = TRUE) {
    // Determine if we have to pad anything.
    $missing = $this->getPaddingLimit() - count($entities);

    if ($missing < 1) {
      // Already fully padded.
      return $entities;
    }

    $loaded_entity_type = $parent->getEntityType();
    $entity_type = $loaded_entity_type->id();

    // Fetch related entities.
    $query = $this->buildBasicQuery($parent);
    // We only fetch the amount required to pad the list.
    $query->range(0, $missing);

    // Grab the right condition group based on the selected
    // conjunction to group fields together.
    $between_field_conjunction = $this->getSetting('between_fields_conjunction');
    $group = $query->orConditionGroup();
    if ($between_field_conjunction == static::_AND) {
      $group = $query->andConditionGroup();
    }

    // Only support for OR within the same field for now
    // Will require refactor to database select otherwise.
    $attach_group = FALSE;
    foreach ($references as $reference_field => $ids) {
      if (!empty($ids)) {
        $group->condition($reference_field, $ids, 'IN');
        $attach_group = TRUE;
      }
    }
    if ($attach_group) {
      $query->condition($group);
    }

    $result = $query->execute();

    if (empty($result) && $first && $this->getSetting('force_padding')) {
      // Nothing found to pad the list, but let's dig deeper.
      return $this->getEntities($entities, $parent, $langcode, [], FALSE);
    }
    if (empty($result)) {
      // We got what we got.
      return $entities;
    }

    $loaded_entities = $this->entityTypeManager->getStorage($entity_type)
      ->loadMultiple($result);
    // Translate the entities and add them to the list.
    foreach ($loaded_entities as $entity) {
      $this->exclude[] = $entity->id();
      if ($entity instanceof TranslatableInterface) {
        $entity = $this->entityRepository->getTranslationFromContext($entity, $langcode);
      }
      // Add extra access checks? These should happen in the query, or we might
      // still not get our fully padded list. And I'd rather not start fetching
      // stuff one by one or recursing and hoping to get lucky.
      $entities[] = $entity;
    }

    $missing = $this->getPaddingLimit() - count($entities);
    if ($this->getSetting('force_padding') && $missing > 0 && $first) {
      $entities = $this->getEntities($entities, $parent, $langcode, [], FALSE);
    }

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if (!parent::isApplicable($field_definition)) {
      return FALSE;
    }
    $entity_type_field_is_targeting = $field_definition->getFieldStorageDefinition()
      ->getSetting('target_type');
    $entity_type_field_is_attached_to = $field_definition->getTargetEntityTypeId();
    $bundle_field_is_attached_to = $field_definition->getTargetBundle();

    // This formatter only applies if this field references the same
    // entity type as the field it is attached to. So attached to a node,
    // this field has to reference other nodes.
    if ($entity_type_field_is_targeting != $entity_type_field_is_attached_to) {
      return FALSE;
    }
    // Bundles is harder, and I guess not really needed? One reference field
    // can reference many bundles, and if the related is cross-bundle that
    // should be supported as well. But then each bundle should have the same
    // fields...
    // So for now require targeting only one bundle, and that bundle must be
    // the same as this bundle. If needed, we can expand cross-bundle support
    // and lower the "otherFieldForThisBundle" to only return fields that are
    // present for *all* the bundles this field targets.
    $handler_settings = $field_definition->getSetting('handler_settings') ?: [];
    $loaded_entity_type = \Drupal::entityTypeManager()
      ->getDefinition($entity_type_field_is_targeting);
    if ($bundle_key = $loaded_entity_type->getKey('bundle')) {
      if (!empty($handler_settings['target_bundles'])) {
        $bundle_ids = $handler_settings['target_bundles'];
        if (count($bundle_ids) > 1) {
          // No support for multiple bundles.
          return FALSE;
        }
        $bundle_field_is_targeting = reset($bundle_ids);
        if ($bundle_field_is_attached_to !== $bundle_field_is_targeting) {
          return FALSE;
        }
      }
      else {
        // No support for infi bundles.
        return FALSE;
      }
    }

    // This entity should have other reference fields present, to be used to
    // determine a relationship. They can reference whatever they want.
    $fields = static::getOtherFieldsForThisBundle($field_definition, 'entity_reference');

    return !empty($fields);
  }

  /**
   * Get other fields for this bundle.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   Field definition.
   * @param bool $field_type
   *   Field type.
   *
   * @return array
   *   Array of fields.
   */
  public static function getOtherFieldsForThisBundle(FieldDefinitionInterface $field_definition, $field_type = FALSE) {
    $fields = [];

    $entity_type_field_is_attached_to = $field_definition->getTargetEntityTypeId();
    $bundle_field_is_attached_to = $field_definition->getTargetBundle();

    // This field's attached entity needs to have other reference fields
    // referencing something else. This is a padding formatter and we need
    // another reference field to make a relationship. The other field should
    // be available on this bundle, IF there is a bundle. If there isn't,
    // this is a basefield and is available on all bundles. And then... shit.
    $fields_attached_to_this_entity_type = \Drupal::service('entity_field.manager')
      ->getFieldMap()[$entity_type_field_is_attached_to];
    $field_id = $field_definition->getName();

    foreach ($fields_attached_to_this_entity_type as $fieldname => $fieldmap) {
      if ($fieldname === $field_id) {
        continue;
      }
      // If field type is requested, match it.
      if ($field_type && $fieldmap['type'] !== $field_type) {
        continue;
      }
      // Separate field. This field should be present or available
      // for this bundle though.
      if (in_array($bundle_field_is_attached_to, $fieldmap['bundles'])) {
        $fields[$fieldname] = $fieldname;
      }
    }

    return $fields;
  }

}
