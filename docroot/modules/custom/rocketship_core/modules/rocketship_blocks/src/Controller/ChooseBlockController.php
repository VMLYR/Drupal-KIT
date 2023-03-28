<?php

namespace Drupal\rocketship_blocks\Controller;

use Drupal\block_content\Entity\BlockContentType;
use Drupal\Core\Url;
use Drupal\layout_builder_restrictions\Controller\ChooseBlockController as ChooseBlockControllerCore;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Class ChooseBlockController
 *
 * @package Drupal\rocketship_blocks\Controller
 */
class ChooseBlockController extends ChooseBlockControllerCore {

  /**
   * {@inheritdoc}
   */
  public function build(SectionStorageInterface $section_storage, $delta, $region) {
    $build = parent::build($section_storage, $delta, $region);
    if (isset($build['add_block'])) {
      unset($build['add_block']);
    }
    $b = $this->inlineBlockList($section_storage, $delta, $region);
    unset($b['back_button']);
    $category = (string) $this->t('Inline blocks');

    $build['block_categories'][$category]['#type'] = 'details';
    $build['block_categories'][$category]['#attributes']['class'][] = 'js-layout-builder-category';
    $build['block_categories'][$category]['#open'] = TRUE;
    $build['block_categories'][$category]['#title'] = $category;
    $build['block_categories'][$category]['links'] = $b;

    $inline_blocks_category = (string) $this->t('Inline blocks');

    foreach ($build['block_categories'] as $key => &$form_category) {
      if (!is_array($form_category)) {
        continue;
      }
      if ($key === $inline_blocks_category) {
        continue;
      }
      $form_category['#open'] = FALSE;
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * todo: rework some of this markup shit. Figure out what to do with
   * these default icons. Per category? Something else?
   */
  protected function getBlockLinks(SectionStorageInterface $section_storage, int $delta, $region, array $blocks) {
    $links = [];
    $icons_folder_path = drupal_get_path('module', 'rocketship_blocks') . '/assets/images/';
    $default_icon_path = file_create_url($icons_folder_path . 'default-block.svg');
    foreach ($blocks as $block_id => $block) {
      $description = '';
      $icon_path = $default_icon_path;
      if ($block['category'] == (string) $this->t('Inline blocks')) {
        /** @var \Drupal\block_content\BlockContentTypeInterface $block_type */
        $block_type = BlockContentType::load(str_replace('inline_block:', '', $block_id));
        if ($block_type) {
          if ($block_type->getThirdPartySetting('rocketship_blocks', 'icon_path', NULL)) {
            $icon_path = $block_type->getThirdPartySetting('rocketship_blocks', 'icon_path', NULL);
          }
          $description = '<p>' . $block_type->getDescription() . '</p>';
        }
      }
      if ($block['category'] == (string) $this->t('Custom')) {
        $l = $this->entityTypeManager->getStorage('block_content')
          ->loadByProperties(['uuid' => str_replace('block_content:', '', $block_id)]);
        $loaded_block = reset($l);
        if ($loaded_block) {
          $block_type = BlockContentType::load($loaded_block->bundle());
          if ($block_type) {
            if ($block_type->getThirdPartySetting('rocketship_blocks', 'icon_path', NULL)) {
              $icon_path = $block_type->getThirdPartySetting('rocketship_blocks', 'icon_path', NULL);
            }
            $description = '<p>' . $block_type->getDescription() . '</p>';
          }
        }
      }
      $attributes = $this->getAjaxAttributes();
      $attributes['class'][] = 'js-layout-builder-block-link';
      $link = [
        'title' => [
          'icon' => [
            '#theme' => 'image',
            '#uri' => $icon_path,
            '#alt' => $block['admin_label'],
          ],
          'title' => [
            '#markup' => '<div class="inline-block-list__item__title">' . $block['admin_label'] . '</div>',
          ],
          'description' => [
            '#markup' => '<div class="inline-block-list__item__descr">' . $description . '</div>',
          ],

        ],
        'url' => Url::fromRoute('layout_builder.add_block',
          [
            'section_storage_type' => $section_storage->getStorageType(),
            'section_storage' => $section_storage->getStorageId(),
            'delta' => $delta,
            'region' => $region,
            'plugin_id' => $block_id,
          ]
        ),
        'attributes' => $attributes,
      ];

      $links[] = $link;
    }
    return [
      '#theme' => 'links',
      '#links' => $links,
    ];
  }

}
