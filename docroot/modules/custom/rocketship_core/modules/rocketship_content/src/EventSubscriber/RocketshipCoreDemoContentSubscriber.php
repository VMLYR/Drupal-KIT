<?php

namespace Drupal\rocketship_content\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RocketshipCookiePolicySubscriber.
 */
class RocketshipCoreDemoContentSubscriber implements EventSubscriberInterface {

  /**
   * Var.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * RocketshipCookiePolicySubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_ROW_SAVE] = ['onMigratePostRowSaveEvent'];

    return $events;
  }

  /**
   * Callback for the event.
   *
   * This does assume there's only one node being migrated. If there are
   * multiple then the last one will stick, as this is executed for every row.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   The event.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function onMigratePostRowSaveEvent(MigratePostRowSaveEvent $event) {
    $migration = $event->getMigration();
    if ($migration->id() === 'rocketship_content_node_page') {
      // Based on uuid, set the node as homepage, 404 or 403.
      $nids = $event->getDestinationIdValues();
      $nid = reset($nids);
      $node = Node::load($nid);
      $uuid = $node->uuid();

      switch ($uuid) {
        case '4a30cf14-7946-4686-ab91-52204280c5b7':
          $this->setUpHomepage($node);
          break;

        case 'f25fa72b-d34d-4640-9dd3-b502314a80ff':
          $this->setUp404Page($node);
          break;

        case 'cae3fec8-f97b-48a0-913c-4cd809ff26de':
          $this->setUp403Page($node);
          break;
      }
    }
  }

  /**
   * Set up the 403 page.
   *
   * Replaces metatags and delete metatag 403 defaults.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp403Page(NodeInterface $node) {
    $this->configFactory->getEditable('system.site')
      ->set('page.403', '/node/' . $node->id())
      ->save();

    // Also set its urls to [site:url] instead of the node url.
    $metatags = $node->get('field_meta_tags')->value;
    $metatags = unserialize($metatags);

    $metatags['robots'] = 'noindex';

    $serialized = serialize($metatags);
    $node->set('field_meta_tags', $serialized);
    $node->save();

    // Disable the 403 metatags so node metatags are output.
    $this->configFactory->getEditable('metatag.metatag_defaults.403')
      ->set('status', FALSE)
      ->save();

    try {
      // Exclude this page from being indexed.
      /** @var \Drupal\simple_sitemap\Simplesitemap $generator */
      $generator = \Drupal::service('simple_sitemap.generator');
      $settings = [
        'index' => FALSE,
        'priority' => 0,
        'changefreq' => NULL,
        'include_images' => FALSE,
      ];
      $generator->setEntityInstanceSettings('node', $node->id(), $settings);
    }
    catch (\Exception $e) {
      \Drupal::messenger()
        ->addWarning('Failed to exclude 403 node from simple sitemap indexing. Please do so manually by editing the 403 node and excluding it from simple sitemap indexing.');
    }
  }

  /**
   * Set up the 404 page.
   *
   * Replaces metatags and delete metatag 404 defaults.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp404Page(NodeInterface $node) {
    $this->configFactory->getEditable('system.site')
      ->set('page.404', '/node/' . $node->id())
      ->save();

    // Also set its urls to [site:url] instead of the node url.
    $metatags = $node->get('field_meta_tags')->value;
    $metatags = unserialize($metatags);

    $metatags['robots'] = 'noindex';

    $serialized = serialize($metatags);
    $node->set('field_meta_tags', $serialized);
    $node->save();

    // Disable the 404 metatags so node metatags are output.
    $this->configFactory->getEditable('metatag.metatag_defaults.404')
      ->set('status', FALSE)
      ->save();

    try {
      // Exclude this page from being indexed.
      /** @var \Drupal\simple_sitemap\Simplesitemap $generator */
      $generator = \Drupal::service('simple_sitemap.generator');
      $settings = [
        'index' => FALSE,
        'priority' => 0,
        'changefreq' => NULL,
        'include_images' => FALSE,
      ];
      $generator->setEntityInstanceSettings('node', $node->id(), $settings);
    }
    catch (\Exception $e) {
      \Drupal::messenger()
        ->addWarning('Failed to exclude 404 node from simple sitemap indexing. Please do so manually by editing the 404 node and excluding it from simple sitemap indexing.');
    }
  }

  /**
   * Set up the frontpage.
   *
   * Replaces metatags and delete metatag frontpage defaults.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUpHomepage(NodeInterface $node) {
    $this->configFactory->getEditable('system.site')
      ->set('page.front', '/node/' . $node->id())
      ->save();

    // Also set its urls to [site:url] instead of the node url.
    $metatags = $node->get('field_meta_tags')->value;
    $metatags = unserialize($metatags);

    $metatags['canonical_url'] = '[site:url]';
    $metatags['shortlink'] = '[site:url]';
    $metatags['og_url'] = '[site:url]';
    $metatags['twitter_cards_page_url'] = '[site:url]';

    $serialized = serialize($metatags);
    $node->set('field_meta_tags', $serialized);
    $node->save();

    // Disable the frontpage metatags so node metatags are output.
    $this->configFactory->getEditable('metatag.metatag_defaults.front')
      ->set('status', FALSE)
      ->save();

    try {
      // Exclude this page from being indexed, homepage gets indexed as is
      // because we added it as custom link. If we index this node, it'll get
      // indexed using its alias. No bueno.
      /** @var \Drupal\simple_sitemap\Simplesitemap $generator */
      $generator = \Drupal::service('simple_sitemap.generator');
      $settings = [
        'index' => FALSE,
        'priority' => 0,
        'changefreq' => NULL,
        'include_images' => FALSE,
      ];
      $generator->setEntityInstanceSettings('node', $node->id(), $settings);
    }
    catch (\Exception $e) {
      \Drupal::messenger()
        ->addWarning('Failed to exclude homepage node from simple sitemap indexing. Please do so manually by editing the homepage node and excluding it from simple sitemap indexing.');
    }

  }

}
