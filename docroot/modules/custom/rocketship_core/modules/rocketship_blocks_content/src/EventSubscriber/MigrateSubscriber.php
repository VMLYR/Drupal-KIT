<?php

namespace Drupal\rocketship_blocks_content\EventSubscriber;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\layout_builder\InlineBlockUsageInterface;
use Drupal\layout_builder\Plugin\Block\InlineBlock;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MigrateSubscriber
 *
 * @package Drupal\rocketship_blocks_content\EventSubscriber
 */
class MigrateSubscriber implements EventSubscriberInterface {

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
    $events[MigrateEvents::POST_IMPORT] = ['onMigratePostImportEvent'];

    return $events;
  }

  /**
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function onMigratePostImportEvent(MigrateImportEvent $event) {
    // So this used to be in the post row event, and checked for the node migrate.
    // But that isn't good enough, because of reference blocks and stubs not working
    // with layout builder. So now, I guess, we run this code after every migrate of
    // this group and see if we can load nodes or not. Means this'll run at least
    // 3 times or something. But at least it'll work regardless of how people
    // trigger the migrate.
    $migration = $event->getMigration();

    if (strpos($migration->id(), 'rocketship_blocks_content') !== 0) {
      return;
    }

    $entity_type_id = 'node';
    $entity_type_storage = $this->entityTypeManager->getStorage($entity_type_id);

    $path = \Drupal::service('extension.list.module')->getPath('rocketship_blocks_content');
    $handle = fopen("$path/assets/csv/rocketship_blocks_content_node_page.csv", "r");
    // skip first line
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE) {
      $entities = $entity_type_storage->loadByProperties(['uuid' => $data[0]]);
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

}
