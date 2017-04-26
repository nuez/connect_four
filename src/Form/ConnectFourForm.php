<?php
/**
 * @file
 * Contains \Drupal\connect_four\Forms\ConnectFourForm.php
 */

namespace Drupal\connect_four\Form;

use Drupal\connect_four\ConnectFourService;
use Drupal\connect_four\Coordinates;
use Drupal\connect_four\Entity\Game;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxy;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConnectFourForm extends FormBase implements FormInterface {

  /**
   * @var \Drupal\connect_four\ConnectFourService
   */
  protected $connectFourService;

  /**
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $accountProxy;

  /**
   * @var Game
   */
  protected $game;

  /**
   * @var bool
   */
  protected $isTurn;

  /**
   * ConnectFourForm constructor.
   * @param \Drupal\connect_four\ConnectFourService $connect_four_service
   * @param \Drupal\Core\Session\AccountProxy $account_proxy
   */
  public function __construct(ConnectFourService $connect_four_service, AccountProxy $account_proxy) {
    $this->connectFourService = $connect_four_service;
    $this->accountProxy = $account_proxy;
    $this->game = $this->connectFourService->getLastGame();
    $this->isTurn = $this->connectFourService->isTurn($this->game, $this->accountProxy->getAccount());
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('connect_four.service'),
      $container->get('current_user')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'connect_four_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (!$this->game) {
      return [
        '#type' => '#markup',
        '#markup' => $this->t('Please create a game first.'),
      ];
    }
    if ($this->game->isFinished()) {
      return [
        '#type' => '#markup',
        '#markup' => t('And the winner is: @winner', [
          '@winner' => $this->game->getWinner()
            ->getDisplayName()
        ])
      ];
    }
    $board = [
      '#theme' => 'connect_four',
      '#attributes' => ['id' => 'connect-four-table'],
      '#attached' => [
        'library' => [
          'connect_four/connect_four_styles',
          'connect_four/connect_four_polling'
        ],
        'drupalSettings' => [
          'turn' => $this->isTurn,
        ]
      ],
      '#isTurn' =>  $this->isTurn,
    ];

    for ($x = 0; $x < Game::WIDTH; $x++) {
      if ($this->connectFourService->canPlayMove($this->game, $x, $this->accountProxy->getAccount())) {
        $board['headers'][$x]['play'] = [
          '#type' => 'submit',
          '#value' => $this->t('Play'),
          '#column' => $x,
          '#name' => $x
        ];
      }
      else {
        $board['headers'][$x]['play'] = [
          '#type' => 'markup',
          '#markup' => '',
        ];
      }
      for ($y = GAME::HEIGHT - 1; $y >= 0; $y--) {
        $coordinates = new Coordinates($x, $y);
        $move = $this->connectFourService->getMoveByCoordinates($this->game, $coordinates);
        if ($move) {
          $board['rows'][$y]['columns'][$x]['#class'] = $move->isHome() ? 'home' : 'away';
        }
        else {
          $board['rows'][$y]['columns'][$x]['#class'] = 'empty';
        }
      }
    }
    $form['board'] = $board;

    return $form;
  }

  /**
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement validateForm() method.
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $triggeringElement = $form_state->getTriggeringElement();
    if (isset($triggeringElement['#column'])) {
      $this->connectFourService->playMove($this->game, $triggeringElement['#column'], $this->accountProxy->getAccount());
    }
  }
}