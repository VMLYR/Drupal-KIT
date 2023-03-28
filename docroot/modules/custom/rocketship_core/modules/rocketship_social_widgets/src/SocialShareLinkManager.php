<?php

namespace Drupal\rocketship_social_widgets;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class SocialShareLinkManager
 * @package Drupal\rocketship_social_widgets
 */
class SocialShareLinkManager implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $shareLinkManager;

  /**
   * SocialShareLinkManager constructor.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->shareLinkManager = $entityTypeManager->getStorage('social_share_link');
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('entity_type.manager')
    );
  }

  /**
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   */
  public function getSocialShareLinks(){
    $links = $this->shareLinkManager->loadMultiple();
    uasort($links, '_sort_social_share_links');
    return $links;
  }

  /**
   * @return int
   */
  public function getSocialShareLinkNextWeight(){
    $links = $this->getSocialShareLinks();
    $weight = -10;

    foreach ($links as $link) {
      if($link->getWeight() > $weight){
        $weight = $link->getWeight();
      }
    }

    return $weight + 1;
  }

}