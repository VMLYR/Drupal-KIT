<?php

namespace Drupal\Tests\rocketship_core\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Class PathAliasUpdateCalculationTest.
 *
 * @group rocketship_core.path_alias
 * @group dropsolid
 */
class PathAliasUpdateCalculationTest extends UnitTestCase {

  /**
   * Get a list of test aliases.
   *
   * @return array
   *   List of aliases.
   */
  protected function getListOfPathAliases() {
    return [
      [
        'langcode' => 'en',
        'path' => '/node/1',
        'alias' => '/level-1',
      ],
      [
        'langcode' => 'en',
        'path' => '/node/2',
        'alias' => '/level-1/level-2',
      ],
      [
        'langcode' => 'nl',
        'path' => '/node/1',
        'alias' => '/level-1-nl',
      ],
      [
        'langcode' => 'nl',
        'path' => '/node/2',
        'alias' => '/level-1-nl/level-2-nl',
      ],
    ];
  }

  /**
   * Look for any aliases with the original alias as a part of it.
   *
   * @param array $path_alias_updated
   *   Path alias updated.
   *
   * @return array
   *   List of alias items.
   */
  protected function findAliasMatches(array $path_alias_updated) {
    $matches = array_filter($this->getListOfPathAliases(), function ($path_alias) use ($path_alias_updated) {
      // Skip original source path.
      if ($path_alias_updated['path'] == $path_alias['path']) {
        return FALSE;
      }

      // Make sure we filter results by language.
      if ($path_alias_updated['langcode'] != $path_alias['langcode']) {
        return FALSE;
      }

      return strpos($path_alias['alias'], $path_alias_updated['alias']) === 0;
    });

    return array_values($matches);
  }

  /**
   * Test a case when we updated existing alias by providing new alias.
   */
  public function testPathAliasCalculation() {
    // Fetch test path aliases.
    $path_aliases = $this->getListOfPathAliases();

    // Let's assume we updated first alias from the list.
    $path_alias_updated = ['new_alias' => '/level-1-new'] + $path_aliases[0];

    // Look for any aliases with the original alias as a part of it.
    $matches = $this->findAliasMatches($path_alias_updated);

    // Make sure we have one match.
    $this->assertCount(1, $matches);

    // Make sure we found second alias from the list.
    $this->assertArrayEquals($path_aliases[1], $matches[0]);

    // Generate new alias for one match.
    $new_alias = str_replace($path_alias_updated['alias'], $path_alias_updated['new_alias'], $matches[0]['alias']);

    // Make sure we generated the proper alias.
    $this->assertEquals('/level-1-new/level-2', $new_alias);
  }

  /**
   * Test a case when no matches found due a filter by language.
   */
  public function testPathAliasMultilingualNoMatches() {
    // Fetch test path aliases.
    $path_aliases = $this->getListOfPathAliases();

    // Let's assume we updated first alias from the list but used FR language.
    $path_alias_updated = [
      'new_alias' => '/level-1-new',
      'langcode' => 'fr',
    ] + $path_aliases[0];

    // Look for any aliases with the original alias as a part of it.
    $matches = $this->findAliasMatches($path_alias_updated);

    // No matches found because we don't have FR alias in the list.
    $this->assertCount(0, $matches);
  }

  /**
   * Test a case when no matches found due a filter by part of existing aliases.
   */
  public function testPathAliasNoMatches() {
    $path_alias_updated = [
      'path' => '/node/3',
      'alias' => '/level-root',
      'new_alias' => '/level-root-new',
      'langcode' => 'en',
    ];

    // Look for any aliases with the original alias as a part of it.
    $matches = $this->findAliasMatches($path_alias_updated);

    // No matches found due the original alias is not a part of any existing.
    $this->assertCount(0, $matches);
  }

}
