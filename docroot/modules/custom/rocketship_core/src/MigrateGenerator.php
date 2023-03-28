<?php

namespace Drupal\rocketship_core;

use Drupal\block_content\Plugin\Block\BlockContentBlock;
use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\layout_builder\Plugin\Block\InlineBlock;
use Drupal\system\FileDownloadController;
use Drupal\webform\Plugin\Block\WebformBlock;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class MigrateGenerator {

  /**
   * @var string
   */
  private $entity_type_id;

  /**
   * @var array
   */
  private $bundles;

  /**
   * @var array
   */
  private $ids;

  /**
   * @var string
   */
  private $module_name;

  /**
   * @var array
   */
  private $generated_files = [];

  /**
   * @var array
   */
  private $created_migrates = [];

  /**
   * @var array
   */
  private $handled_entities = [];

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $menuLinkContentStorage;

  /**
   * @var bool
   */
  private $default_content_mode;

  /**
   * @var array
   */
  private $blockUsageMigrates = [];

  /**
   * MigrateGenerator constructor.
   *
   * @param $entity_type_id
   * @param array $bundles
   * @param array $ids
   * @param string $module_name
   * @param bool $default_content_mode
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct($entity_type_id, $bundles = [], $ids = [], $module_name = 'rs_generated_migrate', $default_content_mode = FALSE) {
    $this->entity_type_id = $entity_type_id;
    $this->bundles = $bundles;
    $this->ids = $ids;
    $this->module_name = $module_name;
    $this->menuLinkContentStorage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
    $this->default_content_mode = $default_content_mode;
  }

  /**
   * @param $directory
   *
   * @return mixed
   */
  public function mkdir($directory) {
    $directory = pathinfo($directory, PATHINFO_DIRNAME);
    $file_system = \Drupal::service('file_system');
    if (!\Drupal::service('stream_wrapper_manager')
      ->isValidScheme(StreamWrapperManager::getScheme($directory))) {
      // Only trim if we're not dealing with a stream.
      $directory = rtrim($directory, '/\\');
    }

    // Check if directory exists.
    if (!is_dir($directory)) {
      return $file_system->mkdir($directory, NULL, TRUE);
    }
  }

  /**
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \ReflectionException
   * @throws \Twig\Error\RuntimeError|\Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function generateMigrate() {
    // ok, so, generate a module? Cuz we do need to have those hooks to alter
    // the shiz. It's just a module, install and info file I guess shouldn't be too much worK?
    include_once \Drupal::root() . '/core/themes/engines/twig/twig.engine';

    // create dir
    $dir = 'temporary://generated/' . $this->module_name;
    //    $dir = \Drupal::root() . '/modules/contrib/rocketship_core/modules/' . $this->module_name;
//    $this->mkdir($dir . '/config/install');
//    $this->mkdir($dir . '/assets');
//    $this->mkdir($dir . '/src/EventSubscriber');

    // create migrate group
    $yaml = [
      'id' => $this->module_name . '_group',
      'label' => $this->module_name . ' group',
      'description' => '',
      'source_type' => '',
      'module' => NULL,
      'shared_configuration' => NULL,
    ];
    $this->generated_files["config/install/migrate_plus.migration_group.{$yaml['id']}.yml"] = Yaml::dump($yaml, 50000, 2, Yaml::DUMP_OBJECT_AS_MAP);

    $this->generateMigrateForEntityType($this->entity_type_id, $this->bundles, $this->ids);

    // Re-order generated migrates to make sure any menu_link_content migrates happen last.
    // If I ever refactor this to not be terrible that'll happen automagically
    if (isset($this->created_migrates[$this->module_name . '_menu_link_content_menu_link_content'])) {
      unset($this->created_migrates[$this->module_name . '_menu_link_content_menu_link_content']);
      $this->created_migrates[$this->module_name . '_menu_link_content_menu_link_content'] = $this->module_name . '_menu_link_content_menu_link_content';
    }

    $path = drupal_get_path('module', 'rocketship_core');
    $files = [
      $path . '/assets/templates/generated_migrate_info_file.yml.twig' => "$dir/$this->module_name.info.yml",
      $path . '/assets/templates/generated_migrate_install_file.php.twig' => "$dir/$this->module_name.install",
      $path . '/assets/templates/generated_migrate_module_file.php.twig' => "$dir/$this->module_name.module",
      $path . '/assets/templates/generated_migrate_services_file.yml.twig' => "$dir/$this->module_name.services.yml",
      $path . '/assets/templates/generated_migrate.eventsubscriber_file.php.twig' => "$dir/src/EventSubscriber/MigrateSubscriber.php",
    ];

    foreach ($files as $source => $target) {
      $markup = twig_render_template($source, [
        'module_name' => $this->module_name,
        'migrate_list' => $this->created_migrates,
        'theme_hook_original' => 'not-applicable',
        'layout_builder_entity_migrates' => $this->blockUsageMigrates,
      ]);
      $body = (string) $markup;
      $this->mkdir($target);
      file_put_contents($target, $body);
    }

    foreach ($this->generated_files as $filename => $contents) {
      $file_parts = pathinfo($filename);
      $this->mkdir("$dir/$filename");
      $file = ("$dir/$filename");

      switch ($file_parts['extension']) {
        case 'csv':
          $handle = fopen($file, 'w');
          foreach ($contents as $row) {
            fputcsv($handle, $row);
          }
          fclose($handle);
          break;
        default:
          file_put_contents($file, $contents);
          break;
      }
    }

    return $dir;
  }

  /**
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field
   *
   * @return bool
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function skipThisField(FieldDefinitionInterface $field) {
    if ($field->getTargetEntityTypeId() === 'section_library_template') {
      if (in_array($field->getName(), [
        'type',
        'layout_section',
        'image',
      ])) {
        return FALSE;
      }
    }
    if ($field->getTargetEntityTypeId() === 'menu_link_content') {
      if (in_array($field->getName(), [
        'title',
        'description',
        'menu_name',
        'link',
        'external',
        'rediscover',
        'weight',
        'expanded',
        'enabled',
        'parent',
        'menu_clickthrough_description',
        'menu_clickthrough_image',
      ])) {
        return FALSE;
      }
    }
    if (in_array($field->getType(), ['layout_translation', 'metatag'])) {
      return TRUE;
    }
    if ($field->getName() === 'reusable' && $field->getTargetEntityTypeId() === 'block_content') {
      return FALSE;
    }
    $entity_type = \Drupal::entityTypeManager()->getDefinition($field->getTargetEntityTypeId());
    if ($field->getName() === $entity_type->getKey('label')) {
      return FALSE;
    }
    if ($field->getName() === $entity_type->getKey('status')) {
      return FALSE;
    }
    if ($field->getName() === $entity_type->getKey('uuid')) {
      return FALSE;
    }
    if ($field->getName() === $entity_type->getKey('langcode')) {
      return FALSE;
    }
    if ($field->getName() === 'path') {
      return FALSE;
    }
    if ($field->isComputed()) {
      return TRUE;
    }
    return empty($field->getTargetBundle());
  }

  /**
   * @param $entity_type_id
   * @param $bundles
   *
   * @param array $ids
   *
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \ReflectionException|\Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function generateMigrateForEntityType($entity_type_id, $bundles, $ids = []) {

    $entities = [];
    if (!empty($ids)) {
      $entities = \Drupal::entityTypeManager()
        ->getStorage($entity_type_id)
        ->loadMultiple($ids);
    }

    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager */
    $entityFieldManager = \Drupal::service('entity_field.manager');
    if (empty($bundles)) {
      $bundles = array_keys(\Drupal::service('entity_type.bundle.info')
        ->getBundleInfo($entity_type_id));
    }

    foreach ($bundles as $bundle) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface[] $bundle_entities */
      $bundle_entities = [];
      foreach ($entities as $entity) {
        if ($entity->bundle() == $bundle) {
          // add em to handled entities now?
          $this->handled_entities[$entity->uuid()] = $entity->uuid();
          $bundle_entities[] = $entity;
        }
      }

      $id = "{$this->module_name}_{$entity_type_id}_{$bundle}";
      $yaml = [
        'id' => $id,
        'migration_group' => $this->module_name . '_group',
        'source' => [
          'plugin' => 'csv',
          'path' => "/assets/csv/$id.csv",
          'header_offset' => 0,
          'ids' => [
            'uuid',
          ],
        ],
        'process' => [
          'uuid' => 'uuid',
        ],
        'destination' => [
          'plugin' => "entity:$entity_type_id",
          'default_bundle' => $bundle,
        ],
        'migration_dependencies' => [
          'required' => [],
          'optional' => [],
        ],
      ];

      $fields = $entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
      $generated_csv_headers = [];
      $rows = [];

      foreach ($fields as $field) {

        $entity_type = \Drupal::entityTypeManager()->getDefinition($field->getTargetEntityTypeId());
        if ($field->getName() === $entity_type->getKey('langcode') && $this->default_content_mode) {
          // Custom handling to use the callback for setting the language.
          // Don't add it to headers, don't add it to rows.
          $yaml['process']["{$field->getName()}"] = [
            [
              'plugin' => 'callback',
              'callable' => "rocketship_core_get_default_content_default_language",
            ],
          ];
          continue;
        }

        if ($this->skipThisField($field)) {
          continue;
        }

        if ($field->getName() === 'parent' && $entity_type_id === 'menu_link_content') {
          // Recurse if that menu link hasn't been handled yet?
          foreach ($bundle_entities as $bundle_entity) {
            $parent = $bundle_entity->get('parent')->value;
            if ($parent && strpos($parent, 'menu_link_content:') === 0) {
              $parent_uuid = str_replace('menu_link_content:', '', $parent);
              $menu_links = $this->menuLinkContentStorage->loadByProperties(['uuid' => $parent_uuid]);
              if ($menu_links) {
                $menu_links = [reset($menu_links)->id()];
                $this->generateMigrateForEntityType('menu_link_content', [], $menu_links);
              }
            }
          }
        }
        if ($field->getName() === 'link' && $entity_type_id === 'menu_link_content') {
          $generated_csv_headers[] = "{$field->getName()}_title";
          $generated_csv_headers[] = "{$field->getName()}_uri";

          $yaml['process']["{$field->getName()}/title"] = [
            [
              'plugin' => 'get',
              'source' => "{$field->getName()}_title",
            ],
          ];
          //todo: this shit gotta run after the correct migrate! so figure that shit out.
          $yaml['process']["{$field->getName()}/uri"] = [
            [
              'plugin' => 'callback',
              'callable' => "_{$this->module_name}_link_uuid_lookup",
              'source' => "{$field->getName()}_uri",
            ],
          ];

          // Do it manually, replace entity id with entity uuid and then a process which reverts it
          // but for that we need to know what migrate contains the entities... cuz we gotta do a lookup
          $referenced_entities = [];
          foreach ($bundle_entities as $bundle_entity) {
            /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $link */
            $link = $bundle_entity->get('link')->first();
            if ($link->isEmpty()) {
              $rows[$bundle_entity->uuid()][] = NULL;
              $rows[$bundle_entity->uuid()][] = NULL;
              continue;
            }
            $title = NULL;
            $uri = $link->uri;
            if (strpos($uri, 'entity:') === 0) {
              $uri = str_replace('entity:', '', $uri);
              [$menu_entity_type_id, $menu_entity_id] = explode('/', $uri);
              $loaded_entity = \Drupal::entityTypeManager()->getStorage($menu_entity_type_id)->load($menu_entity_id);
              $uri = 'uuid:' . $menu_entity_type_id . '/' . $loaded_entity->uuid();

              if (!isset($this->handled_entities[$loaded_entity->uuid()])) {
                $referenced_entities[] = $loaded_entity;
              }

            }
            $rows[$bundle_entity->uuid()][] = $title;
            $rows[$bundle_entity->uuid()][] = $uri;
          }

          foreach ($referenced_entities as $ref_entity) {
            $this->generateMigrateForEntityType($ref_entity->getEntityTypeId(), [$ref_entity->bundle()], [$ref_entity->id()]);
          }

          continue;
        }

        switch ($field->getType()) {
          case 'entity_reference_revisions':
            $generated_csv_headers[] = $field->getName();
            $referenced_entities = [];
            foreach ($bundle_entities as $idx => $entity) {
              $data = $entity->get($field->getName());
              $values = [];
              foreach ($data as $item) {
                if (!isset($this->handled_entities[$item->entity->uuid()])) {
                  $referenced_entities[] = $item->target_id;
                }
                $values[] = $item->entity->uuid();
              }

              $rows[$entity->uuid()][] = implode('|', $values);
            }

            $migrates = $this->generateMigrateForEntityType(
              $field->getSetting('target_type'),
              $field->getSetting('handler_settings')['target_bundles'],
              $referenced_entities
            );

            $migrate_ids = array_map(function ($migrate) use (&$yaml) {
              $yaml['migration_dependencies']['required'][] = $migrate['id'];
              return $migrate['id'];
            }, $migrates);

            $yaml['process'][$field->getName()] = [
              [
                'plugin' => 'migration_lookup',
                'migration' => $migrate_ids,
                'source' => $field->getName(),
              ],
              [
                'plugin' => 'sub_process',
                'process' => [
                  'target_id' => '0',
                  'target_revision_id' => '1',
                ],
              ],
            ];
            if ($field->getFieldStorageDefinition()->getCardinality() != 1) {
              $yaml['process'][$field->getName()] = [
                [
                  'plugin' => 'explode',
                  'source' => $field->getName(),
                  'delimiter' => '|',
                ],
                [
                  'plugin' => 'migration_lookup',
                  'migration' => $migrate_ids,
                  // no source or it fucks up
                ],
                [
                  'plugin' => 'sub_process',
                  'process' => [
                    'target_id' => '0',
                    'target_revision_id' => '1',
                  ],
                ],
              ];
            }
            break;
          case 'entity_reference':
            $generated_csv_headers[] = $field->getName();
            $referenced_entities = [];
            foreach ($bundle_entities as $idx => $bundle_entity) {
              $data = $bundle_entity->get($field->getName());
              $values = [];
              foreach ($data as $item) {
                if (!isset($this->handled_entities[$item->entity->uuid()])) {
                  $referenced_entities[] = $item->target_id;
                }
                $values[] = $item->entity->uuid();
              }

              $rows[$bundle_entity->uuid()][] = implode('|', $values);
            }

            $reference_field_entity_type_id = $field->getSetting('target_type');
            $reference_field_bundles = $field->getSetting('handler_settings')['target_bundles'];
            if (empty($referenced_entities) && $reference_field_entity_type_id == $entity_type_id && $reference_field_bundles == $bundles) {
              // No more recursing if this entity references itself and we don't need to handle any more entities.
              // really gotta refactor this so migrate writing and csv filling are separate steps.
              $migrate_ids = [$yaml['id']];
            }
            else {
              $migrates = $this->generateMigrateForEntityType(
                $field->getSetting('target_type'),
                $field->getSetting('handler_settings')['target_bundles'],
                $referenced_entities
              );
              $migrate_ids = array_map(function ($migrate) use (&$yaml) {
                $yaml['migration_dependencies']['required'][] = $migrate['id'];
                return $migrate['id'];
              }, $migrates);
            }

            $yaml['process'][$field->getName()] = [
              [
                'plugin' => 'migration_lookup',
                'migration' => $migrate_ids,
                'source' => $field->getName(),
              ],
            ];
            if ($field->getFieldStorageDefinition()->getCardinality() != 1) {
              $yaml['process'][$field->getName()] = [
                [
                  'plugin' => 'explode',
                  'source' => $field->getName(),
                  'delimiter' => '|',
                ],
                [
                  'plugin' => 'migration_lookup',
                  'migration' => $migrate_ids,
                  // no source or it fucks up
                ],
              ];
            }

            break;
          case 'image':
          case 'file':
            $generated_csv_headers[] = $field->getName();
            $referenced_entities = [];
            foreach ($bundle_entities as $idx => $bundle_entity) {
              $data = $bundle_entity->get($field->getName());
              $values = [];
              foreach ($data as $item) {
                if (!isset($this->handled_entities[$item->entity->uuid()])) {
                  $referenced_entities[] = $item->target_id;
                }
                $values[] = $item->entity->uuid();
              }

              $rows[$bundle_entity->uuid()][] = implode('|', $values);
            }

            $file_migrate = $this->generateFileMigrate($referenced_entities);
            $yaml['migration_dependencies']['required'][] = $file_migrate['id'];

            $yaml['process'][$field->getName()] = [
              [
                'plugin' => 'migration_lookup',
                'migration' => $file_migrate['id'],
                'source' => $field->getName(),
              ],
            ];
            if ($field->getFieldStorageDefinition()->getCardinality() != 1) {
              $yaml['process'][$field->getName()] = [
                [
                  'plugin' => 'explode',
                  'source' => $field->getName(),
                  'delimiter' => '|',
                ],
                [
                  'plugin' => 'migration_lookup',
                  'migration' => $file_migrate['id'],
                  // no source, or it fucks up.
                ],
              ];
            }

            break;
          case 'layout_section':
            // Store that this entity type has layout override and should be handled
            // by a post migrate subscriber. Store the migrate ID
            $this->blockUsageMigrates[$id] = $id;

            // This gonna be the big boy. The biggest of boys even.
            // And 100% relies on the uuid block layout patch thing.
            $generated_csv_headers[] = $field->getName();
            $referenced_entities = $referenced_bundles = [];
            foreach ($bundle_entities as $idx => $bundle_entity) {
              $data = $bundle_entity->get($field->getName());
              $values = [];
              foreach ($data as $item) {
                /** @var \Drupal\layout_builder\Section $section */
                $section = $item->section;
                if (strpos($section->getLayoutId(), 'rs_') === 0) {
                  // Check for background image.
                  $config = $section->getLayout()->getConfiguration();
                  if (!empty($config['background_image'])) {
                    // This should be a UUID now
                    $medias = \Drupal::entityTypeManager()->getStorage('media')
                      ->loadByProperties(['uuid' => $config['background_image']]);
                    if ($medias) {
                      $media = reset($medias);
                      $this->generateMigrateForEntityType('media', [$media->bundle()], [$media->id()]);
                    }
                  }
                }
                // Now loop over the section, grab all the inline and content
                // blocks and add those to $referenced_entities.
                foreach ($section->getComponents() as $component) {
                  $plugin = $component->getPlugin();
                  if ($plugin instanceof InlineBlock or $plugin instanceof BlockContentBlock) {
                    $reflectionMethod = new \ReflectionMethod($plugin, 'getEntity');
                    $reflectionMethod->setAccessible(TRUE);
                    /** @var \Drupal\block_content\Entity\BlockContent $block */
                    $block = $reflectionMethod->invoke($plugin);
                    if (!isset($this->handled_entities[$block->uuid()])) {
                      $referenced_entities[] = $block->id();
                    }
                    $referenced_bundles[] = $block->bundle();
                    $configuration = $plugin->getConfiguration();
                    // Empty out revision ID to hopefully avoid a metric ton of issues.
                    $configuration['block_revision_id'] = NULL;
                    $component->setConfiguration($configuration);
                  }
                  if ($plugin instanceof WebformBlock) {
                    $form = $plugin->getConfiguration()['webform_id'];
                    $webform = \Drupal::config('webform.webform.' . $form);
                    $raw_data = $webform->getRawData();
                    if (isset($raw_data['_core'])) {
                      unset($raw_data['_core']);
                    }
                    $this->generated_files['config/install/webform.webform.' . $form . '.yml'] = Yaml::dump($raw_data, 50000, 2, Yaml::DUMP_OBJECT_AS_MAP);
                  }

                }
                $values[] = serialize($section);
              }

              $rows[$bundle_entity->uuid()][] = base64_encode(implode('|', $values));
            }

            if (!empty($referenced_entities)) {
              $this->generateMigrateForEntityType('block_content', $referenced_bundles, $referenced_entities);
            }

            $yaml['process'][$field->getName()] = [
              [
                'plugin' => 'callback',
                'source' => $field->getName(),
                'callable' => 'base64_decode',
              ],
              [
                'plugin' => 'explode',
                'delimiter' => '|',
              ],
              [
                'plugin' => 'callback',
                'callable' => 'unserialize',
              ],
            ];

            break;
          case 'webform':
            // todo: also check for webforms in sections
            // Export the webforms in question and that's about it?
            foreach ($bundle_entities as $bundle_entity) {
              $data = $bundle_entity->get($field->getName());
              foreach ($data as $item) {
                $form = $item->target_id;
                $webform = \Drupal::config('webform.webform.' . $form);
                $raw_data = $webform->getRawData();
                if (isset($raw_data['_core'])) {
                  unset($raw_data['_core']);
                }
                $this->generated_files['config/install/webform.webform.' . $form . '.yml'] = Yaml::dump($raw_data, 50000, 2, Yaml::DUMP_OBJECT_AS_MAP);
              }
            }
          // now let default handle it?
          default:
            $cardinality = $field->getFieldStorageDefinition()->getCardinality();
            $property_definitions = $field->getFieldStorageDefinition()->getPropertyDefinitions();
            $properties_to_migrate = [];
            foreach ($property_definitions as $property => $definition) {
              if ($definition->isComputed()) {
                continue;
              }
              if ($definition->getDataType() === 'map') {
                // DOn't support map atm.
                continue;
              }
              $properties_to_migrate[] = $property;
            }
            sort($properties_to_migrate);

            if (count($properties_to_migrate) > 1 && $cardinality != 1) {
              $generated_csv_headers[] = $field->getName();
              $flipped_properties_to_migrate = array_flip($properties_to_migrate);
              $flipped_properties_to_migrate = array_map(function ($item) {
                return (string) $item;
              }, $flipped_properties_to_migrate);
              //combo wombo property
              $yaml['process'][$field->getName()] = [
                [
                  'plugin' => 'explode',
                  'source' => $field->getName(),
                  'delimiter' => '|',
                ],
                [
                  'plugin' => 'explode',
                  'delimiter' => '***',
                ],
                [
                  'plugin' => 'sub_process',
                  'process' => $flipped_properties_to_migrate,
                ],
              ];
              foreach ($bundle_entities as $idx => $bundle_entity) {
                $data = $bundle_entity->get($field->getName());
                $values = [];
                foreach ($data as $item) {
                  $field_values = [];
                  foreach ($properties_to_migrate as $property) {
                    $field_values[] = $item->{$property};
                  }
                  /** @var \Drupal\Core\Field\FieldItemBase $item */
                  $values[] = implode('***', $field_values);
                }
                $rows[$bundle_entity->uuid()][] = implode('|', $values);
              }
            }

            if (count($properties_to_migrate) > 1 && $cardinality === 1) {
              // Separate stuff.
              foreach ($properties_to_migrate as $property) {
                $generated_csv_headers[] = "{$field->getName()}_{$property}";
                $yaml['process']["{$field->getName()}/{$property}"] = [
                  [
                    'plugin' => 'get',
                    'source' => "{$field->getName()}_{$property}",
                  ],
                ];
              }
              foreach ($bundle_entities as $idx => $bundle_entity) {
                $data = $bundle_entity->get($field->getName());
                if ($data->isEmpty()) {
                  // We still need it tho.
                  foreach ($properties_to_migrate as $property) {
                    $rows[$bundle_entity->uuid()][] = NULL;
                  }
                  continue;
                }
                foreach ($data as $item) {
                  foreach ($properties_to_migrate as $property) {
                    $rows[$bundle_entity->uuid()][] = $item->{$property};
                  }
                }
              }
            }

            if (count($properties_to_migrate) === 1 && $cardinality != 1) {
              $generated_csv_headers[] = $field->getName();
              $yaml['process'][$field->getName()] = [
                [
                  'plugin' => 'explode',
                  'source' => $field->getName(),
                  'delimiter' => '|',
                ],
                [
                  'plugin' => 'get',
                ],
              ];
              foreach ($bundle_entities as $idx => $bundle_entity) {
                $data = $bundle_entity->get($field->getName());
                $values = [];
                foreach ($data as $item) {
                  $field_values = [];
                  foreach ($properties_to_migrate as $property) {
                    $field_values[] = $item->{$property};
                  }
                  /** @var \Drupal\Core\Field\FieldItemBase $item */
                  $values[] = implode('***', $field_values);
                }
                $rows[$bundle_entity->uuid()][] = implode('|', $values);
              }
            }

            if (count($properties_to_migrate) === 1 && $cardinality === 1) {
              $generated_csv_headers[] = $field->getName();
              $yaml['process'][$field->getName()] = [
                [
                  'plugin' => 'get',
                  'source' => $field->getName(),
                ],
              ];
              foreach ($bundle_entities as $idx => $bundle_entity) {
                $data = $bundle_entity->get($field->getName());
                $values = [];
                foreach ($data as $item) {
                  $field_values = [];
                  foreach ($properties_to_migrate as $property) {
                    $field_values[] = $item->{$property};
                  }
                  /** @var \Drupal\Core\Field\FieldItemBase $item */
                  $values[] = implode('***', $field_values);
                }
                $rows[$bundle_entity->uuid()][] = implode('|', $values);
              }
            }
            break;
        }
      }

      // Check for menu links
      $menu_links = $this->checkForMenuLinks($bundle_entities);
      if ($menu_links) {
        $this->generateMigrateForEntityType('menu_link_content', [], $menu_links);
      }

      $created_migrates[] = $yaml;
      $this->created_migrates[$yaml['id']] = $yaml['id'];

      $string = Yaml::dump($yaml, 50000, 2, Yaml::DUMP_OBJECT_AS_MAP);
      $filename = "config/install/migrate_plus.migration.$id.yml";

      $this->generated_files[$filename] = $string;


      $file = "assets/csv/$id.csv";

      // Only add headers if this is the first time creating this file
      // else merge data, keys by uuid so should avoid duplicate data.
      if (!isset($this->generated_files[$file])) {
        $this->generated_files[$file] = [];
        array_unshift($rows, $generated_csv_headers);
      }
      $this->generated_files[$file] += $rows;

    }

    return $created_migrates;
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface[] t$bundle_entities
   *
   * @return \Drupal\menu_link_content\Entity\MenuLinkContent[]
   */
  public function checkForMenuLinks(array $bundle_entities) {
    $list = [];
    foreach ($bundle_entities as $entity) {
      // Early opt-out.
      if (!$entity->hasLinkTemplate('canonical')) {
        continue;
      }

      $entity_type_id = $entity->getEntityTypeID();
      $canonical_url = $entity->toUrl();
      $link_uris_to_search_for = [
        "internal:/{$canonical_url->getInternalPath()}",
        $canonical_url->toUriString(),
        "entity:{$entity_type_id}/{$entity->id()}",
      ];

      $results = $this->menuLinkContentStorage->getQuery()
        ->condition('link.uri', $link_uris_to_search_for, 'IN')
        ->accessCheck(FALSE)
        ->execute();

      $list = array_merge($list, array_values($results));
    }

    return $list;
  }

  /**
   * @param array $ids
   *
   * @return array
   */
  public function generateFileMigrate($ids = []) {

    $entities = [];
    if (!empty($ids)) {
      /** @var \Drupal\file\Entity\File[] $entities */
      $entities = \Drupal\file\Entity\File::loadMultiple($ids);
    }
    $rows = [];
    foreach ($entities as $idx => $entity) {
      $rows[$entity->uuid()][] = $entity->uuid();
      $rows[$entity->uuid()][] = $entity->getFilename();
      $this->generated_files['assets/files/' . $entity->getFilename()] = file_get_contents($entity->getFileUri());
    }

    $id = "{$this->module_name}_file_migrate";
    $yaml = [
      'id' => $id,
      'migration_group' => $this->module_name . '_group',
      'source' => [
        'plugin' => 'csv',
        'path' => "/assets/csv/$id.csv",
        'header_offset' => 0,
        'ids' => [
          'uuid',
        ],
        'constants' => [
          'source_base_path' => '/assets/files/',
          'target_base_path' => 'public://migrated_files/',
        ],
      ],
      'process' => [
        'uuid' => 'uuid',
        '_source_full_path' => [
          'plugin' => 'concat',
          'source' => [
            'constants/source_base_path',
            'filename',
          ],
        ],
        '_destination_full_path' => [
          'plugin' => 'concat',
          'source' => [
            'constants/target_base_path',
            'filename',
          ],
        ],
        'uri' => [
          'plugin' => 'file_copy',
          'source' => [
            '@_source_full_path',
            '@_destination_full_path',
          ],
          'file_exists' => 'rename',
        ],
      ],
      'destination' => [
        'plugin' => "entity:file",
      ],
      'migration_dependencies' => [
        'required' => [],
        'optional' => [],
      ],
    ];

    $string = Yaml::dump($yaml, 50000, 2, Yaml::DUMP_OBJECT_AS_MAP);
    $filename = "config/install/migrate_plus.migration.$id.yml";
    $this->generated_files[$filename] = $string;
    $this->created_migrates[$yaml['id']] = $yaml['id'];

    $generated_csv_headers = ['uuid', 'filename'];

    $file = "assets/csv/$id.csv";

    if (!isset($this->generated_files[$file])) {
      $this->generated_files[$file] = [];
      array_unshift($rows, $generated_csv_headers);
    }
    $this->generated_files[$file] += $rows;

    return $yaml;
  }

}