<?php

namespace Drupal\connect_four;

use Drupal\connect_four\Entity\Game;
use Drupal\connect_four\Entity\Move;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;

/**
 * Interface ConnectFourServiceInterface.
 *
 * @package Drupal\connect_four
 */
interface ConnectFourServiceInterface {

  /**
   * Count the total connected moves.
   *
   * @param \Drupal\connect_four\Entity\Move $move
   * @return Move[]
   */
  public function getMaximumMovesInLine(Move $move);

  /**
   * Get Move by Coordinates.
   *
   * @param \Drupal\connect_four\Entity\Game $game
   * @param \Drupal\connect_four\Coordinates $direction
   *
   * @return \Drupal\connect_four\Entity\Move|boolean
   */
  public function getMoveByCoordinates(Game $game, Coordinates $direction);

  /**
   * @param \Drupal\connect_four\Entity\Game $game
   * @return Move[]
   */
  public function getMoves(Game $game);

  /**
   * Get the last open game.
   *
   * @return Game|boolean
   */
  public function getLastGame();

  /**
   * Checks if a user has the permission to play a move.
   *
   * @param \Drupal\connect_four\Entity\Game $game
   * @param int $x
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool TRUE if allowed, FALSE if not allowed.
   * TRUE if allowed, FALSE if not allowed.
   */
  public function canPlayMove(Game $game, $x, AccountInterface $account);

  /**
   * @param \Drupal\connect_four\Entity\Game $game
   * @param $x
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return Move
   */
  public function playMove(Game $game, $x, AccountInterface $account);

  /**
   * @param \Drupal\connect_four\Entity\Game $game
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return void
   */
  public function declareWinner(Game $game, AccountInterface $account);

}
