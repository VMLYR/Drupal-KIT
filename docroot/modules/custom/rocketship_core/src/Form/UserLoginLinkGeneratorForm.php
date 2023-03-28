<?php

namespace Drupal\rocketship_core\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserLoginLinkGeneratorForm.
 */
class UserLoginLinkGeneratorForm extends FormBase {

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->messenger = $container->get('messenger');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->dateTime = $container->get('datetime.time');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_login_link_generator_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['user'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('User'),
      '#description' => $this->t('Select the user to generate a login link for'),
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!$this->loadUser($form_state->getValue('user')) instanceof UserInterface) {
      $form_state->setError($form['user'], t('Invalid user selected'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $account = $this->loadUser($form_state->getValue('user'));

    $request_time = $this->dateTime->getRequestTime();
    $url = Url::fromRoute('user.reset',
      [
        'uid' => $account->id(),
        'timestamp' => $request_time,
        'hash' => user_pass_rehash($account, $request_time),
      ],
      [
        'absolute' => TRUE,
      ]
    );

    $link = Link::fromTextAndUrl($url->toString(), $url);

    $this->messenger->addStatus(new FormattableMarkup($link->toString(), []));
  }

  /**
   * @param $uid
   *
   * @return \Drupal\user\UserInterface|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function loadUser($uid) {
    return $this->entityTypeManager
      ->getStorage('user')
      ->load($uid);
  }

}
