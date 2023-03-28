<?php

namespace Drupal\rocketship_social_widgets\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rocketship_social_widgets\SocialShareLinkManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Form handler for the Social Share Link add and edit forms.
 */
class SocialShareLinkForm extends EntityForm {

  private $socialShareLinkManager;

  /**
   * Constructs an SocialShareLinkForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManager $entityTypeManager, SocialShareLinkManager $socialShareLinkManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->socialShareLinkManager = $socialShareLinkManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('rocketship_social_widgets.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $link = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $link->label(),
      '#description' => $this->t("The name that will be shown to the visitor."),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $link->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$link->isNew(),
    ];
    $form['sharer_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sharer URL'),
      '#default_value' => $link->get('sharer_url'),
      '#description' => $this->t("The sharing URL for this provider."),
      '#required' => TRUE,
    ];
    $form['onclick'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Onclick attribute'),
      '#default_value' => $link->get('onclick'),
      '#description' => $this->t("Enter any Javascript to be put in to the onclick attribute of the share link."),
      '#required' => FALSE,
    ];
    $form['target'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link target attribute'),
      '#required' => TRUE,
      '#default_value' => $link->get('target') ?? '_blank',
    ];
    $form['token_tree'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['current-page'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $link = $this->entity;
    $status = $link->save();

    if ($status) {
      $this->messenger()->addStatus($this->t('Saved the %label Social Share Link.', [
        '%label' => $link->label(),
      ]));
    }
    else {
      $this->messenger()->addStatus($this->t('The %label Social Share Link was not saved.', [
        '%label' => $link->label(),
      ]));
    }

    $form_state->setRedirect('entity.social_share_link.overview');
  }

  /**
   * Helper function to check whether an Social Share Link configuration entity
   * exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('social_share_link')
      ->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    parent::copyFormValuesToEntity($entity, $form, $form_state);
    // There is no weight on the edit form. Fetch all configurable Social Share Links
    // ordered by weight and set the new link to be placed after them.
    if (empty($entity->getWeight())) {
      $this->socialShareLinkManager->getSocialShareLinks();
      $entity->setWeight($this->socialShareLinkManager->getSocialShareLinkNextWeight());
    }
  }
}
