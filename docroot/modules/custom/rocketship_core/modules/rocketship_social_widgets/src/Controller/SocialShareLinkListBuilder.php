<?php

namespace Drupal\rocketship_social_widgets\Controller;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a listing of Social Share Link.
 */
class SocialShareLinkListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_share_link_overview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
        'sorter' => '',
        'machine_name' => $this->t('Machine name'),
        'label' => $this->t('Link title'),
        'sharer_url' => $this->t('Sharer URL')
      ] + parent::buildHeader();

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['sorter'] = ['#markup' => ''];
    $row['id'] = ['#markup' => $entity->id()];
    $row['label'] = $entity->label();
    $row['sharer_url'] = ['#markup' => $entity->get('sharer_url')];
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['actions']['submit']['#value'] = t('Save configuration');
    return $form;
  }
}