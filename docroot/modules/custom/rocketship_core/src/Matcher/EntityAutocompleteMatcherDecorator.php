<?php

namespace Drupal\rocketship_core\Matcher;

use Drupal\Core\Entity\EntityAutocompleteMatcherInterface;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;

/**
 * This class is decorator for "rocketship_core.autocomplete_matcher"
 * service.
 */
class EntityAutocompleteMatcherDecorator extends EntityAutocompleteMatcher {

  /**
   * Constructs a EntityAutoCompleteMatcherDecorator object.
   *
   * @param \Drupal\Core\Entity\EntityAutocompleteMatcherInterface $matcher
   *   The autocomplete matcher for entity references.
   * @param \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selection_manager
   *   The entity reference selection handler plugin manager.
   */
  public function __construct(EntityAutocompleteMatcherInterface $matcher, SelectionPluginManagerInterface $selection_manager) {
    parent::__construct($matcher, $selection_manager);
  }

}
