<?php

namespace Drupal\Tests\rocketship_core\Functional;

use Drupal\Core\Language\LanguageInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\node\NodeInterface;
use Drupal\Tests\pathauto\Functional\PathautoTestHelperTrait;

/**
 * Class PathAliasUpdateTest.
 *
 * Covers multilingual and non-multilingual cases. Revisionable cases are not
 * covered due path_alias entity does not create a new revision.
 *
 * @group rocketship_core.path_alias
 * @group dropsolid
 */
class PathAliasUpdateTest extends KernelTestBase {

  use PathautoTestHelperTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'field',
    'user',
    'node',
    'link',
    'path',
    'token',
    'menu_ui',
    'pathauto',
    'language',
    'redirect',
    'rocketship_core',
    'menu_link_content',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The default language service.
   *
   * @var \Drupal\Core\Language\LanguageDefault
   */
  protected $languageDefault;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->languageManager = $this->container->get('language_manager');
    $this->languageDefault = $this->container->get('language.default');

    // Install needed schema and configs.
    $this->installSchema('system', 'sequences');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('redirect');
    $this->installEntitySchema('path_alias');
    $this->installEntitySchema('menu_link_content');

    $this->installSchema('node', ['node_access']);

    // Main menu will be created on installing system configs.
    $this->installConfig(['pathauto', 'system', 'language']);

    // Create page content type.
    $this->createContentType(['type' => 'page']);

    // Create url pattern for all node types.
    $this->createPattern('node', '/[node:menu-link:parent-alias]/[node:title]');

    // Make sure the custom token is safe.
    $safe_tokens = $this->config('pathauto.settings')->get('safe_tokens') ?? [];
    $safe_tokens[] = 'node:menu-link:parent-alias';
    $this->config('pathauto.settings')
      ->set('safe_tokens', $safe_tokens)
      ->save();

    $this->container->get('router.builder')->rebuild();
  }

  /**
   * Create content type without fields.
   *
   * @param array $values
   *   List of values.
   */
  protected function createContentType(array $values = []) {
    $this->entityTypeManager->getStorage('node_type')
      ->create($values)
      ->save();
  }

  /**
   * Create node without fields.
   *
   * @param array $values
   *   List of values.
   * @param \Drupal\menu_link_content\MenuLinkContentInterface|null $parent_link
   *   The parent menu link.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\node\NodeInterface
   *   Create node.
   */
  protected function createNodeWithMenuLink(array $values = [], MenuLinkContentInterface $parent_link = NULL) {
    $values += [
      'type' => 'page',
      'uid' => 0,
      'title' => $this->randomMachineName(),
      'status' => NodeInterface::PUBLISHED,
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    ];

    $node_storage = $this->entityTypeManager->getStorage('node');
    /** @var \Drupal\node\NodeInterface $node */
    $node = $node_storage->create($values);
    $node->save();

    $menu_link_storage = $this->entityTypeManager->getStorage('menu_link_content');
    $menu_link = $menu_link_storage->create([
      'title' => $node->getTitle(),
      'menu_name' => 'main',
      'link' => ['uri' => 'entity:node/' . $node->id()],
      'parent' => !is_null($parent_link) ? $parent_link->getPluginId() : NULL,
    ]);
    $menu_link->save();

    // Re-save node to apply new path alias after creation menu.
    $node->setNewRevision(FALSE);
    $node->save();

    return $node;
  }

  /**
   * Get related menu link of the node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   *
   * @return \Drupal\menu_link_content\MenuLinkContentInterface
   *   The related menu link content.
   */
  protected function getMenuLink(NodeInterface $node) {
    $storage = $this->entityTypeManager->getStorage('menu_link_content');

    $links = $storage->loadByProperties([
      'link.uri' => 'entity:node/' . $node->id(),
    ]);

    $this->assertCount(1, $links);

    return reset($links);
  }

  /**
   * Create test content with menu structure.
   *
   * @param string $langcode
   *   The if of the language.
   *
   * @return \Drupal\node\NodeInterface[]
   *   List of three created node.
   */
  protected function createNodes($langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED) {
    // Create first level content.
    $level1 = $this->createNodeWithMenuLink(['title' => 'Level 1', 'langcode' => $langcode]);
    $this->assertEntityAlias($level1, '/level-1');

    // Create second level content.
    $level1_menu_link = $this->getMenuLink($level1);
    $level2 = $this->createNodeWithMenuLink(['title' => 'Level 2', 'langcode' => $langcode], $level1_menu_link);
    $this->assertEntityAlias($level2, '/level-1/level-2');

    // Create third level content.
    $level2_menu_link = $this->getMenuLink($level2);
    $level3 = $this->createNodeWithMenuLink(['title' => 'Level 3', 'langcode' => $langcode], $level2_menu_link);
    $this->assertEntityAlias($level3, '/level-1/level-2/level-3');

    return [$level1, $level2, $level3];
  }

  /**
   * Change default and set current language.
   *
   * @param string $langcode
   *   The id of the language.
   */
  protected function switchCurrentLanguage($langcode) {
    $language = ConfigurableLanguage::load($langcode);

    // Change default language.
    $this->config('system.site')
      ->set('langcode', $language->getId())
      ->set('default_langcode', $language->getId())
      ->save();
    $this->languageDefault->set($language);

    // Make sure current language has been changed.
    $current_language = $this->languageManager->getCurrentLanguage()->getId();
    $this->assertEqual($current_language, $language->getId());
  }

  /**
   * Test path aliases for non translatable content.
   */
  public function testPathAliasNonMultilingualUpdate() {
    [$level1, $level2, $level3] = $this->createNodes();

    // Update title for second node.
    $level2->setTitle('Level 2 new');
    $level2->save();

    // Nothing changed for first node. Make sure that second level of the alias
    // has been updated for second and third levels of content.
    $this->assertEntityAlias($level1, '/level-1');
    $this->assertEntityAlias($level2, '/level-1/level-2-new');
    $this->assertEntityAlias($level3, '/level-1/level-2-new/level-3');

    // Update first item.
    $level1->setTitle('Level 1 new');
    $level1->save();

    // Make sure that first level of the alias has been updated for all content.
    $this->assertEntityAlias($level1, '/level-1-new');
    $this->assertEntityAlias($level2, '/level-1-new/level-2-new');
    $this->assertEntityAlias($level3, '/level-1-new/level-2-new/level-3');
  }

  /**
   * Test path aliases update for translatable content.
   */
  public function testPathAliasMultilingualUpdate() {
    $language = ConfigurableLanguage::createFromLangcode('nl');
    $language->save();

    [$level1_en, $level2_en] = $this->createNodes('en');

    // Switch current language to NL.
    $this->switchCurrentLanguage('nl');

    // Add NL translation.
    $level1_nl = $level1_en->addTranslation('nl', [
      'title' => 'Level 1 NL',
    ]);
    $level1_nl->save();

    // Make sure that EN alias has not been changed.
    $this->assertEntityAlias($level1_en, '/level-1');

    // Make sure that translated content has a new proper alias.
    $this->assertEntityAlias($level1_nl, '/level-1-nl');

    // Add translation for second node.
    $level2_nl = $level2_en->addTranslation('nl', [
      'title' => 'Level 2 NL',
    ]);
    $level2_nl->save();

    // Make sure that EN alias has not been changed.
    $this->assertEntityAlias($level2_en, '/level-1/level-2');

    // Make sure that translated content has new proper alias.
    $this->assertEntityAlias($level2_nl, '/level-1-nl/level-2-nl');

    // Update title for the first level.
    $level1_nl->setTitle('Level 1 NL new');
    $level1_nl->setNewRevision(FALSE);
    $level1_nl->save();

    // Make sure that alias of the translated nodes has been updated.
    $this->assertEntityAlias($level1_nl, '/level-1-nl-new');
    $this->assertEntityAlias($level2_nl, '/level-1-nl-new/level-2-nl');

    // Make sure that EN translation has not been updated.
    $this->assertEntityAlias($level1_en, '/level-1');
    $this->assertEntityAlias($level2_en, '/level-1/level-2');
  }

}
