<?php

namespace Drupal\connect_four;
use Drupal\connect_four\Entity\Game;
use Drupal\connect_four\Entity\Move;
use Drupal\user\Entity\User;

/**
 * Interface ConnectFourServiceInterface.
 *
 * @package Drupal\connect_four
 */
interface ConnectFourServiceInterface {

  /**
   * Create a game and return it.
   *
   * @param \Drupal\user\Entity\User $homeUser
   * @param \Drupal\user\Entity\User $awayUser
   *
   * @return \Drupal\connect_four\Entity\Game
   */
  public function startGame(User $homeUser, User $awayUser);

  /**
   * @param \Drupal\connect_four\Entity\Move $move
   * @return Game
   */
  public function processMove(Move $move);

  /**
   * Count the total connected moves.
   *
   * @param \Drupal\connect_four\Entity\Move $move
   * @return integer
   */
  public function countConnections(Move $move);

  /**
   * @param \Drupal\user\Entity\User $winner
   * @param \Drupal\connect_four\Entity\Game $game
   * @return mixed
   */
  public function declareWinner(User $winner, Game $game);
}
