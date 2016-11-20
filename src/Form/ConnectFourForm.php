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
   * ConnectFourForm constructor.
   * @param \Drupal\connect_four\ConnectFourService $connect_four_service
   */
  public function __construct(ConnectFourService $connect_four_service, AccountProxy $account_proxy) {
    $this->connectFourService = $connect_four_service;
    $this->accountProxy = $account_proxy;
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
    /** @var Game $game */

    $this->game = $this->connectFourService->getLastGame();


    if (!$this->game) {
      return [
        '#type' => '#markup',
        '#markup' => $this->t('Please create a game first.'),
      ];
    }
    if($this->game->hasFinished()){
      return [
        '#type' => '#markup',
        '#markup' => t('And the winner is: @winner', ['@winner' => $this->game->getWinner()->getDisplayName()])
      ];
    }
    $board = [
      '#theme' => 'connect_four',
      '#attached' => [
        'library' => [
          'connect_four/connect_four_styles',
        ]
      ],
    ];
    for ($x = 0; $x < Game::WIDTH; $x++) {
      if ($this->connectFourService->canPlayMove($this->game, $x, $this->accountProxy->getAccount())) {
        $board['headers'][$x]['play'] = [
          '#type' => 'submit',
          '#value' => $this->t('Play'),
          '#column' => $x,
          '#name' => $x
        ];
      } else{
        $board['headers'][$x]['play'] = [
          '#type' => 'markup',
          '#markup' => '',
        ];
      }
      for ($y = GAME::HEIGHT -1; $y >= 0; $y--) {
        $coordinates = new Coordinates($x, $y);
        $move = $this->connectFourService->getMoveByCoordinates($this->game, $coordinates);
         if ($move) {
          $board['rows'][$y]['columns'][$x]['#class'] = $move->isHome() ? 'home' : 'away';
        } else{
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
    $column = $form_state->getTriggeringElement()['#column'];
    $move = $this->connectFourService->playMove($this->game, $column, $this->accountProxy->getAccount());
    $movesInLine = $this->connectFourService->getMaximumMovesInLine($move);
    if(count($movesInLine) == Game::CONSECUTIVE){
      $this->connectFourService->declareWinner($this->game, $this->accountProxy->getAccount());
    }
  }
}