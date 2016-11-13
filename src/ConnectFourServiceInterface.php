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
   * Creates a game and returns it.
   *
   * @param \Drupal\user\Entity\User $homeUser
   * @param \Drupal\user\Entity\User $awayUser
   *
   * @return \Drupal\connect_four\Entity\Game
   */
  public function startGame(User $homeUser, User $awayUser);

  /**
   * Detects the corresponding Y position and saves it as a move.
   *
   * @param int $x
   * @return Move
   */
  public function processMoveInput($x);

  /**
   * Processes the move and sees if it leads to victory.
   *
   * @param \Drupal\connect_four\Entity\Move $move
   * @return Game
   */
  public function processMove(Move $move);

  /**
   * Count the total connected moves.
   *
   * @param \Drupal\connect_four\Entity\Move $move
   * @return Move[]
   */
  public function getMaximumMovesInLine(Move $move);

  /**
   * Process the Match so it is closed and it declares
   * the winner.
   *
   * @param \Drupal\user\Entity\User $winner
   * @param \Drupal\connect_four\Entity\Game $game
   * @return Game
   */
  public function declareWinner(User $winner, Game $game);

  /**
   * @param \Drupal\connect_four\Entity\Game $game
   * @return Move[]
   */
  public function getMoves(Game $game);
}
