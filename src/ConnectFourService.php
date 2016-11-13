<?php

namespace Drupal\connect_four;

use Drupal\connect_four\Entity\Game;
use Drupal\connect_four\Entity\GameEntity;
use Drupal\connect_four\Entity\Move;
use Drupal\Core\Config\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\user\Entity\User;

/**
 * Class ConnectFourService.
 *
 * @package Drupal\connect_four
 */
class ConnectFourService implements ConnectFourServiceInterface {

  /**
   * @var EntityTypeManager $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @var QueryFactory $queryFactory
   */
  protected $queryFactory;

  /**
   * An array of all possible directions to check (so excluding 'above')
   *
   * @return Position[]
   */
  protected function getDirections() {
    return [
      new Coordinates(1, 1),
      new Coordinates(1, 0),
      new Coordinates(1, -1),
      new Coordinates(0, -1),
      new Coordinates(-1, -1),
      new Coordinates(-1, 0),
      new Coordinates(-1, 1)
    ];
  }

  /**
   * @var Move[]
   */
  private $adjacentMoves;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   */
  public function __construct(EntityTypeManager $entity_type_manager, QueryFactory $query_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->queryFactory = $query_factory;
  }


  /**
   * Creates a game and returns it.
   *
   * @param \Drupal\user\Entity\User $homeUser
   * @param \Drupal\user\Entity\User $awayUser
   *
   * @return \Drupal\connect_four\Entity\Game
   */
  public function startGame(User $homeUser, User $awayUser) {
    // TODO: Implement startGame() method.
  }

  /**
   * Detects the corresponding Y position and saves it as a move.
   *
   * @param int $x
   * @return Move
   */
  public function processMoveInput($x) {
    // TODO: Implement processMoveInput() method.
  }

  /**
   * Processes the move and sees if it leads to victory.
   *
   * @param \Drupal\connect_four\Entity\Move $move
   * @return Game
   */
  public function processMove(Move $move) {
    // TODO: Implement processMove() method.
  }

  /**
   * Iterates over all possible adjacent moves in all possible directions.
   *
   * Valid adjacent moves are placed in $this->adjacentMoves.
   * Together with the played Move they
   *
   * @param \Drupal\connect_four\Entity\Move $playedMove
   *
   * @return Move[]
   */
  public function getMaximumMovesInLine(Move $playedMove) {

    $x = $playedMove->getX();
    $y = $playedMove->getY();
    $game = $playedMove->getGame();
    $adjacentMoves = [];

    foreach ($this->getDirections() as $direction) {

      /** @var Coordinates $direction */
      $adjacentX = $x + $direction->getX();
      $adjacentY = $y + $direction->getY();
      $adjacentMove = $this->getMoveByCoordinates($game, new Coordinates($adjacentX, $adjacentY));

      /*
       * Check if there is a disc of the same colour in any of the directions
       * and if so, follow that direction AND the opposite direction.
       */
      if ($adjacentMove && $adjacentMove->getOwnerId() == $playedMove->getOwnerId()) {

        $this->adjacentMoves = [$adjacentMove];

        $this->iterateMoves($adjacentMove, $direction);

        $oppositeDirection = new Coordinates($direction->getX() * -1, $direction->getY() * -1);
        $oppositeCoordinates = new Coordinates($playedMove->getX()+$oppositeDirection->getX(),$playedMove->getY() + $oppositeDirection->getY());
        $oppositeMove = $this->getMoveByCoordinates($game, $oppositeCoordinates);
        if ($oppositeMove && $oppositeMove->getOwnerId() == $oppositeMove->getOwnerId()) {
          $this->adjacentMoves[] = $oppositeMove;
          $this->iterateMoves($oppositeMove, $oppositeDirection);
        }

        $adjacentMoves = $this->adjacentMoves > $adjacentMoves ? $this->adjacentMoves : $adjacentMoves;
      }
    }
    $totalMoves = array_merge($adjacentMoves,[$playedMove]);
    return $totalMoves;
  }

  /**
   * See if there are subsequent discs in a certain direction.
   *
   * @param \Drupal\connect_four\Entity\Move $move
   * @param \Drupal\connect_four\Coordinates $direction
   */
  private function iterateMoves(Move $move, Coordinates $direction) {

    $adjacentMove = $this->getMoveByCoordinates(
      $move->getGame(),
      new Coordinates(
        $move->getX() + $direction->getX(),
        $move->getY() + $direction->getY()
      )
    );

    if ($adjacentMove && $adjacentMove->getOwnerId() == $move->getOwnerId()) {
      $this->adjacentMoves[] = $adjacentMove;
      $this->iterateMoves($adjacentMove, $direction);
    }
  }

  /**
   * Get Move by Coordinates.
   *
   * @param \Drupal\connect_four\Entity\Game $game
   * @param \Drupal\connect_four\Coordinates $direction
   *
   * @return \Drupal\connect_four\Entity\Move
   */
  protected function getMoveByCoordinates(Game $game, Coordinates $direction) {
    $moves = $this->getMoves($game);
    foreach ($moves as $move) {
      $x = $move->getX();
      $y = $move->getY();
      if ($x == $direction->getX() && $y == $direction->getY()) {
        return $move;
      }
    }
  }


  /**
   * Process the Match so it is closed and it declares
   * the winner.
   *
   * @param \Drupal\user\Entity\User $winner
   * @param \Drupal\connect_four\Entity\Game $game
   * @return Game
   */
  public function declareWinner(User $winner, Game $game) {
    // TODO: Implement declareWinner() method.
  }

  /**
   * @param \Drupal\connect_four\Entity\Game $game
   * @return Move[]
   */
  public function getMoves(Game $game) {
    if ($game->getMoves()) {
      return $game->getMoves();
    }
    $moves = $this->queryFactory->get('connect_four_move')
      ->condition('game', $game->id())
      ->execute();
    $game->setMoves($moves);
    return $moves;
  }
}
