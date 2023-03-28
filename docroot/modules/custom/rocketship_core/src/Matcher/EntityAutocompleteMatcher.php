<?php

# Src: https://www.chapterthree.com/blog/how-alter-entity-autocomplete-results-drupal-8

namespace Drupal\rocketship_core\Matcher;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\EntityAutocompleteMatcherInterface;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;

/**
 * Class EntityAutocompleteMatcher
 *
 * @package Drupal\rocketship_core\Matcher
 */
class EntityAutocompleteMatcher implements EntityAutocompleteMatcherInterface {

  /**
   * The autocomplete matcher for entity references.
   *
   * @var \Drupal\Core\Entity\EntityAutocompleteMatcherInterface
   */
  protected $matcher;

  /**
   * The entity reference selection handler plugin manager.
   *
   * @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface
   */
  protected $selectionManager;

  /**
   * Constructs an EntityAutocompleteMatcher object.
   *
   * @param \Drupal\Core\Entity\EntityAutocompleteMatcherInterface $matcher
   *   The autocomplete matcher for entity references.
   * @param \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selection_manager
   *   The entity reference selection handler plugin manager.
   */
  public function __construct(EntityAutocompleteMatcherInterface $matcher, SelectionPluginManagerInterface $selection_manager) {
    $this->matcher = $matcher;
    $this->selectionManager = $selection_manager;
  }

  /**
   * Gets matched labels based on a given search string.
   */
  public function getMatches($target_type, $selection_handler, $selection_settings, $string = '') {

    $matches = [];

    $options = $selection_settings + [
      'target_type' => $target_type,
      'handler' => $selection_handler,
    ];

    $handler = $this->selectionManager->getInstance($options);

    if (isset($string)) {
      // Get an array of matching entities.
      $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
      $entity_labels = $handler->getReferenceableEntities($string, $match_operator, 10);

      // Loop through the entities and convert them into autocomplete output.
      foreach ($entity_labels as $values) {
        foreach ($values as $entity_id => $label) {

          $entity = \Drupal::entityTypeManager()->getStorage($target_type)->load($entity_id);
          $entity = \Drupal::service('entity.repository')->getTranslationFromContext($entity);

          $type = !empty($entity->type->entity) ? $entity->type->entity->label() : $entity->bundle();
          $status = '';
          if ($entity->getEntityType()->id() == 'node') {
            $status = ($entity->isPublished()) ? ", Published" : ", Unpublished";
          }

          $key = $label . ' (' . $entity_id . ')';
          // Strip things like starting/trailing white spaces, line breaks and tags.
          $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
          // Names containing commas or quotes must be wrapped in quotes.
          $key = Tags::encode($key);
          $label = '<span class="ui-menu-item-wrapper__title">' . $label . ' (' . $entity_id . ')</span><span class="ui-menu-item-wrapper__type">[' . $type . ']</span>';
          $matches[] = ['value' => $key, 'label' => $label];
        }
      }
    }

    return $matches;
  }

}
