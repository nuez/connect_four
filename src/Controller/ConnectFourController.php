<?php

/**
 * @file
 *
 */

namespace Drupal\connect_four\Controller;

use Drupal\connect_four\ConnectFourService;
use Drupal\connect_four\Form\ConnectFourForm;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConnectFourController extends ControllerBase {

  /**
   * @var \Drupal\connect_four\ConnectFourService
   */
  protected $connectFourService;

  /**
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * ConnectFourController constructor.
   *
   * @param \Drupal\connect_four\ConnectFourService $connect_four_service
   *    The Connect Four Service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *    The current user.
   * @param \Drupal\Core\Form\FormBuilder $form_builder
   *    The Form Builder Service
   */
  public function __construct(ConnectFourService $connect_four_service, AccountInterface $current_user, FormBuilder $form_builder) {
    $this->connectFourService = $connect_four_service;
    $this->currentUser = $current_user;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('connect_four.service'),
      $container->get('current_user')->getAccount(),
      $container->get('form_builder')
    );
  }

  /**
   * Check if the user was awaiting it's turn.
   *
   * @param bool $status
   *   Whether unupdated status of the user's turn is TRUE or FALSE.
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function polling($status) {
    $response = new AjaxResponse();
    if ($status == "false") {
      if ($this->connectFourService->isTurn($this->connectFourService->getLastGame(), $this->currentUser)) {
        $form = $this->formBuilder->getForm('Drupal\connect_four\Form\ConnectFourForm');
        $response->addCommand(new ReplaceCommand(
          '#connect-four-wrapper', $form['board']
        ));
      }
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   *
   * Allow access if the current user is a participant.
   */
  public function access($status) {
    if($status == "false" || $status == "true") {
      $currentGame = $this->connectFourService->getLastGame();
      return AccessResult::allowedIf(
        $currentGame->getAwayUser()->id() == $this->getCurrentUser()->id()
        ||
        $currentGame->getHomeUser()->id() == $this->getCurrentUser()->id()
      );
    }
    return AccessResult::forbidden();
  }

  private
  function getCurrentUser() {
    return $this->currentUser;
  }
}