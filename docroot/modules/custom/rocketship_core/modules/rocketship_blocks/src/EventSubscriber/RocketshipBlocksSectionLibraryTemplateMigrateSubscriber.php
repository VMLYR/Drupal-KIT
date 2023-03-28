<?php

namespace Drupal\rocketship_blocks\EventSubscriber;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\layout_builder\InlineBlockUsageInterface;
use Drupal\layout_builder\Plugin\Block\InlineBlock;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RocketshipBlocksSectionLibraryTemplateMigrateSubscriber
 *
 * @package Drupal\rocketship_blocks\EventSubscriber
 */
class RocketshipBlocksSectionLibraryTemplateMigrateSubscriber implements EventSubscriberInterface {

  /**
   * List of Migration IDs that contain entities
   * with layout overrides. Or at least the possibility
   * of having layout overrides.
   */
  const LAYOUT_BUILDER_ENTITY_MIGRATES = [
    'rs_blocks_templates',
  ];

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @var \Drupal\layout_builder\InlineBlockUsageInterface
   */
  protected $inlineBlockUsage;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * MigrateSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   * @param \Drupal\layout_builder\InlineBlockUsageInterface $inlineBlockUsage
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager, LoggerChannelFactoryInterface $loggerChannelFactory, InlineBlockUsageInterface $inlineBlockUsage) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->logger = $loggerChannelFactory->get('layout_builder_post_migrate');
    $this->inlineBlockUsage = $inlineBlockUsage;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_ROW_SAVE] = ['onMigratePostRowSaveEvent'];

    return $events;
  }

  /**
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \ReflectionException
   */
  public function onMigratePostRowSaveEvent(MigratePostRowSaveEvent $event) {
    $migration = $event->getMigration();

    if (!in_array($migration->id(), static::LAYOUT_BUILDER_ENTITY_MIGRATES)) {
      return;
    }

    if (empty($event->getDestinationIdValues())) {
      // Nothing to check
      return;
    }

    // Figure out the entity type so we can load it.
    $entity_type_id = str_replace('entity:', '', $migration->getDestinationConfiguration()['plugin']);
    // Check it exists
    try {
      $entity_type_storage = $this->entityTypeManager->getStorage($entity_type_id);
    } catch (PluginNotFoundException $e) {
      // entity type does not exist. Log it and move on.
      $this->logger->critical('Migrate with @id could not be checked for layout builder inline block usage because the entity type @type does not exist.',
        [
          '@id' => $migration->id(),
          '@type' => $entity_type_id,
        ]);
      return;
    }

    $entities = $entity_type_storage->loadMultiple($event->getDestinationIdValues());
    foreach ($entities as $entity) {
      $fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $entity->bundle());
      foreach ($fields as $field) {
        if ($field->getType() === 'layout_section') {
          $data = $entity->get($field->getName());
          foreach ($data as $item) {
            /** @var \Drupal\layout_builder\Section $section */
            $section = $item->section;
            foreach ($section->getComponents() as $component) {
              $plugin = $component->getPlugin();
              if ($plugin instanceof InlineBlock) {
                $configuration = $plugin->getConfiguration();
                $block = NULL;
                if (isset($configuration['block_uuid'])) {
                  $loaded_blocks = $this->entityTypeManager->getStorage('block_content')
                    ->loadByProperties(['uuid' => $configuration['block_uuid']]);
                  $block = !empty($loaded_blocks) ? current($loaded_blocks) : NULL;
                }
                if (!$block) {
                  $this->logger->warning('Could not add @block to usage table because it does not exist.',
                    ['@block' => serialize($plugin->getConfiguration())]);
                  continue;
                }
                $this->inlineBlockUsage->addUsage($block->id(), $entity);
                // Now set correct revision ID. Too much stuff relies on it.
                $configuration = $plugin->getConfiguration();
                $configuration['block_revision_id'] = $block->getRevisionId();
                $component->setConfiguration($configuration);
              }
            }
          }
        }
      }
      $entity->save();
    }
  }

}
