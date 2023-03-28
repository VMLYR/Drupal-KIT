<?php

namespace Drupal\menu_clickthrough\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Renderer;
use Drupal\menu_clickthrough\Menu\MenuHelperInterface;
use Drupal\menu_clickthrough\Path\PathHelperInterface;
use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;
use Drupal\system\Entity\Menu;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'clickthrough_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "clickthrough_formatter",
 *   label = @Translation("Clickthrough formatter"),
 *   field_types = {
 *     "clickthrough_field_type"
 *   }
 * )
 */
class ClickthroughFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Var.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * Var.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Var.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Var.
   *
   * @var \Drupal\menu_clickthrough\Path\PathHelperInterface
   */
  protected $pathHelper;

  /**
   * Var.
   *
   * @var \Drupal\menu_clickthrough\Menu\MenuHelperInterface
   */
  protected $menuHelper;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * ClickthroughFormatter constructor.
   *
   * @param string $plugin_id
   *   Plugin ID.
   * @param string $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   Field definition.
   * @param array $settings
   *   Settings.
   * @param string $label
   *   Label.
   * @param string $view_mode
   *   View mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   Menu tree.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repo.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Renderer.
   * @param \Drupal\menu_clickthrough\Path\PathHelperInterface $path_helper
   *   Path helper.
   * @param \Drupal\menu_clickthrough\Menu\MenuHelperInterface $menu_helper
   *   Menu helper.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language Manager.
   */
  public function __construct($plugin_id,
                              $plugin_definition,
                              FieldDefinitionInterface $field_definition,
                              array $settings,
                              $label,
                              $view_mode,
                              array $third_party_settings,
                              MenuLinkTreeInterface $menu_tree,
                              EntityRepositoryInterface $entity_repository,
                              Renderer $renderer,
                              PathHelperInterface $path_helper,
                              MenuHelperInterface $menu_helper,
                              LanguageManagerInterface $languageManager) {

    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->menuTree = $menu_tree;
    $this->entityRepository = $entity_repository;
    $this->renderer = $renderer;
    $this->pathHelper = $path_helper;
    $this->menuHelper = $menu_helper;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('menu.link_tree'),
      $container->get('entity.repository'),
      $container->get('renderer'),
      $container->get('menu_clickthrough.path_helper'),
      $container->get('menu_clickthrough.menu_helper'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->viewValue($item);
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return array
   *   The textual output generated.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function viewValue(FieldItemInterface $item) {
    $parent = $item->menu;

    if ($parent === 'self:') {
      $parent = $this->getCurrentPageMenu();
    }
    if ($parent === '') {
      // No menu item was found which matches the current page.
      // No output will be rendered.
      if (\Drupal::currentUser()->id() > 0) {
        \Drupal::messenger()
          ->addMessage(t('No menu item was found for the current page'), 'warning');
      }
      // Do not cache the empty result as the node has been misconfigured.
      return [
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }

    $menu_name = substr($parent, 0, stripos($parent, ':'));
    $menu_link = str_replace($menu_name . ':', '', $parent);

    // Load the tree, check if root level was chosen.
    if ($menu_link == '') {
      $tree = $this->menuTree->load($menu_name, new MenuTreeParameters());
    }
    else {
      $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
      $parameters->setRoot($menu_link);
      // Change the request to expand all children and limit the depth to
      // the immediate children of the root.
      $parameters->expandedParents = [];
      $parameters->setMinDepth(1);
      $parameters->setMaxDepth(1);
      $tree = $this->menuTree->load($menu_name, $parameters);
    }

    // Let Drupal core build the tree, this automatically loads the urls of the
    // requested items in the correct weight and adds required cache tags.
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    $build = $this->menuTree->build($tree);

    // Overwrite the build with the build overview.
    $build = $this->renderMenuOverview($build);

    // Add the menu as a cache dependency.
    $this->renderer->addCacheableDependency($build, Menu::load($menu_name));
    $build['#cache']['context'][] = 'route.menu_active_trails:' . $menu_name;

    return $build;
  }

  /**
   * Modifies a menu tree render array to a menu clickthrough render array.
   *
   * @param array $build
   *   Build array.
   *
   * @return array
   *   Altered build.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function renderMenuOverview(array $build) {
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $build['#theme'] = 'menu_clickthrough_overview';
    if (!empty($build['#items'])) {
      foreach ($build['#items'] as $key => $item) {
        if (!$item['original_link'] instanceof MenuLinkContent) {
          unset($build['#items'][$key]);
          continue;
        }
        /** @var \Drupal\menu_link_content\MenuLinkContentInterface $entity */
        $entity = $this->entityRepository->loadEntityByUuid('menu_link_content', $item['original_link']->getDerivativeId());
        if ($entity->hasTranslation($language)) {
          $entity = $entity->getTranslation($language);
        }

        /** @var \Drupal\Core\Url $url */
        $url = $item['url'];
        $link_text = t('Read more about') . ' '. $item['title'];
        $title = Link::fromTextAndUrl($item['title'], $url);
        $link = Link::fromTextAndUrl($link_text, $url);
        $image = Link::fromTextAndUrl($entity->get('menu_clickthrough_image')
          ->view('default'), $url);
        $description = $entity->get('menu_clickthrough_description')
          ->view('default');

        $build['#items'][$key] = [
          '#theme' => 'menu_clickthrough_item',
          '#title' => $title->toRenderable(),
          '#description' => $description,
          '#image' => $image->toRenderable(),
          '#link' => $link->toRenderable(),
        ];

        // add aria-label to links with no text (eg. image) or generic text (eg 'read more')
        $build['#items'][$key]['#image']['#attributes'] = array('aria-label' => array($link_text));
        $build['#items'][$key]['#title']['#attributes'] = array('aria-label' => array($link_text));

        // Add each menu item as a cache dependency to the build.
        $this->renderer->addCacheableDependency($build, $entity);
      }
    }

    // Unset unnecessary keys.
    unset($build['#menu_name']);
    unset($build['#sorted']);
    return $build;
  }

  /**
   * Fetch the menu link name of the menu which matches the current path.
   *
   * @return string
   *   Return value.
   */
  protected function getCurrentPageMenu() {
    $trail_urls = $this->pathHelper->getUrls();
    $menu_links = $this->menuHelper->getMenuLinks();

    foreach (array_reverse($trail_urls) as $trail_url) {
      foreach ($menu_links as $menu_link) {
        if ($menu_link->getUrlObject()->toString() == $trail_url->toString()) {
          return $menu_link->getPluginDefinition()['menu_name'] . ':' . $menu_link->getPluginId();
        }
      }
    }
    return '';
  }

}
