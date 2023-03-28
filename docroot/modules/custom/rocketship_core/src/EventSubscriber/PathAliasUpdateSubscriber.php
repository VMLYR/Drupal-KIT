<?php

namespace Drupal\rocketship_core\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\redirect\RedirectRepository;
use Drupal\rocketship_core\Event\PathAliasUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PathAliasUpdateSubscriber.
 *
 * @package Drupal\rocketship_core\EventSubscriber
 */
class PathAliasUpdateSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The settings of the redirect module.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $redirectConfig;

  /**
   * The redirect repository.
   *
   * @var \Drupal\redirect\RedirectRepository
   */
  protected $redirectRepository;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PathAliasUpdateEvent::PATH_ALIAS_UPDATE => ['onPathAliasUpdate'],
    ];
  }

  /**
   * PathAliasUpdateSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\redirect\RedirectRepository $redirect_repository
   *   The redirect repository.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, RedirectRepository $redirect_repository) {
    $this->entityTypeManager = $entity_type_manager;
    $this->redirectConfig = $config_factory->get('redirect.settings');
    $this->redirectRepository = $redirect_repository;
  }

  /**
   * Defines event listener.
   *
   * Handle path aliases update functionality that is useful for sites with a
   * drill-down structure.
   *
   * @param \Drupal\rocketship_core\Event\PathAliasUpdateEvent $event
   *   The path alias update event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function onPathAliasUpdate(PathAliasUpdateEvent $event) {
    $path_alias = $event->getEntity();
    $original_alias = $path_alias->original->getAlias();
    $updated_alias = $path_alias->getAlias();

    if ($updated_alias != $original_alias) {
      $path_alias_storage = $this->entityTypeManager->getStorage('path_alias');

      // Look for any aliases with the original alias as a part of it.
      // Let's take into account the original language because we don't want to
      // update aliases in other languages.
      $results = $path_alias_storage->getQuery()
        ->condition('path', $path_alias->getPath(), '<>')
        ->condition('langcode', $path_alias->language()->getId())
        ->condition('alias', $original_alias . '/%', 'LIKE')
        // Order from the newest to the oldest.
        ->sort('id', 'DESC')
        ->execute();

      // Nothing to do when no matches found.
      if (!$results) {
        return;
      }

      // Check if auto_redirect option is enabled.
      $auto_redirect = $this->redirectConfig->get('auto_redirect');
      $status_code = $this->redirectConfig->get('default_status_code');
      $redirect_storage = $this->entityTypeManager->getStorage('redirect');

      /** @var \Drupal\path_alias\PathAliasInterface[] $entities */
      $entities = $path_alias_storage->loadMultiple($results);
      foreach ($entities as $alias_to_update) {
        // Build and save the new alias.
        $new_alias = str_replace($original_alias, $updated_alias, $alias_to_update->getAlias());
        $alias_to_update->setAlias($new_alias);
        $alias_to_update->save();

        if (!$auto_redirect) {
          continue;
        }

        // Delete all redirects having the same source as this alias.
        redirect_delete_by_path($new_alias, $alias_to_update->language()->getId(), FALSE);

        if ($alias_to_update->getAlias() != $new_alias) {
          if (!$this->redirectRepository->findMatchingRedirect($alias_to_update->getAlias(), [], $alias_to_update->language()->getId())) {
            $redirect = $redirect_storage->create();
            $redirect->setSource($alias_to_update->getAlias());
            $redirect->setRedirect($alias_to_update->getPath());
            $redirect->setLanguage($alias_to_update->language()->getId());
            $redirect->setStatusCode($status_code);
            $redirect->save();
          }
        }
      }
    }
  }

}
