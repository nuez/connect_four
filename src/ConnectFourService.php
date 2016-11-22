<?php

namespace Drupal\connect_four;

use Drupal\connect_four\Entity\Game;
use Drupal\connect_four\Entity\GameEntity;
use Drupal\connect_four\Entity\Move;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxy;
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
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $accountProxy;

  /**
   * @var Move[]
   */
  private $adjacentMoves;

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
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   * @param \Drupal\Core\Session\AccountProxy $account_proxy
   */
  public function __construct(EntityTypeManager $entity_type_manager, QueryFactory $query_factory, AccountProxy $account_proxy) {
    $this->entityTypeManager = $entity_type_manager;
    $this->queryFactory = $query_factory;
    $this->accountProxy = $account_proxy;
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
        $oppositeCoordinates = new Coordinates($playedMove->getX() + $oppositeDirection->getX(), $playedMove->getY() + $oppositeDirection->getY());
        $oppositeMove = $this->getMoveByCoordinates($game, $oppositeCoordinates);
        if ($oppositeMove && $oppositeMove->getOwnerId() == $oppositeMove->getOwnerId()) {
          $this->adjacentMoves[] = $oppositeMove;
          $this->iterateMoves($oppositeMove, $oppositeDirection);
        }

        $adjacentMoves = $this->adjacentMoves > $adjacentMoves ? $this->adjacentMoves : $adjacentMoves;
      }
    }
    $totalMoves = array_merge($adjacentMoves, [$playedMove]);
    return $totalMoves;
  }

  /**
   * Get Move by Coordinates.
   *
   * @param \Drupal\connect_four\Entity\Game $game
   * @param \Drupal\connect_four\Coordinates $direction
   *
   * @return \Drupal\connect_four\Entity\Move|boolean
   */
  public function getMoveByCoordinates(Game $game, Coordinates $direction) {
    $moves = $game->getMoves();
    foreach ($moves as $move) {
      $x = $move->getX();
      $y = $move->getY();
      if ($x == $direction->getX() && $y == $direction->getY()) {
        return $move;
      }
    }
    return FALSE;
  }

  /**
   * Get the last game available.
   *
   * {@inheritdoc}
   */
  public function getLastGame() {
    $games = $this->queryFactory->get('connect_four_game')
      ->sort('created', 'DESC')
      ->range(0, 1)
      ->execute();

    if (!empty($games)) {
      return $this->entityTypeManager->getStorage('connect_four_game')
        ->load(end($games));
    }
    else {
      return FALSE;
    }
  }

  /**
   * Checks if the user can Play a certain move.
   *
   * @param \Drupal\connect_four\Entity\Game $game
   * @param int $x
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return bool Whether the user can play this move or not.
   */
  public function canPlayMove(Game $game, $x, AccountInterface $account) {
    // Grant access to home user if total moves is uneven.
    if(!$game->hasFinished()) {
      if ($account->id() == $game->getHomeUser()->id()) {
        if (count($game->getMoves()) % 2 == 0) {
          if (count($game->getMovesByX($x)) < Game::HEIGHT) {
            return TRUE;
          }
        }
      }
      // Grant access to away user if total moves is uneven.
      elseif ($account->id() == $game->getAwayUser()->id()) {
        if (count($game->getMoves()) % 2 != 0) {
          if (count($game->getMovesByX($x)) < Game::HEIGHT) {
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Creates a move for the indicated column.
   *
   * @param \Drupal\connect_four\Entity\Game $game
   * @param $x
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return Move
   */
  public function playMove(Game $game, $x, AccountInterface $account) {
    $y = count($game->getMovesByX($x));
    $move = Move::create([
      'x' => $x,
      'y' => $y,
      'game' => $game->id(),
      'user_id' => $account->id(),
      'created' => REQUEST_TIME,
    ]);
    $move->save();
    $movesInLine = $this->getMaximumMovesInLine($move);
    if(count($movesInLine) == Game::CONSECUTIVE){
      $this->declareWinner($game, $account);
    }
    return $move;
  }

  /**
   * set Winner to a game.
   *
   * @param \Drupal\connect_four\Entity\Game $game
   * @param \Drupal\Core\Session\AccountInterface $account
   */
  public function declareWinner(Game $game, AccountInterface $account){
    $game->set('winner', $account);
    $game->set('game_status', GAME::FINISHED);
    $game->save();

    return $game;
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
}
