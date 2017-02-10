<?php

namespace Drupal\connect_four;

use Drupal\connect_four\Entity\Game;
use Drupal\connect_four\Entity\GameEntity;
use Drupal\connect_four\Entity\Move;
use Drupal\connect_four\Exception\ConnectFourException;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxy;

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
   * An array of all possible directions to check (so excluding 'above')
   *
   * @return Coordinates[]
   */
  protected function getRelativeCoordinates() {
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
   * Get Move by Coordinates.
   *
   * @param \Drupal\connect_four\Entity\Game $game
   * @param \Drupal\connect_four\Coordinates $direction
   * @param \Drupal\Core\Session\AccountInterface Optional filter by $owner
   *
   * @return bool|\Drupal\connect_four\Entity\Move Returns a Move object or False.
   */
  public function getMoveByCoordinates(Game $game, Coordinates $direction, AccountInterface $owner = NULL) {
    $moves = $game->getMoves();
    foreach ($moves as $move) {
      /** @var Move $move */
      $x = $move->getX();
      $y = $move->getY();
      if ($x == $direction->getX() && $y == $direction->getY()) {
        if (!$owner) {
          return $move;
        }
        else {
          if ($owner->id() == $move->getOwnerId()) {
            return $move;
          }
        }
      }
    }
    return FALSE;
  }


  /**
   * Returns the biggest array of moves in a single line.
   *
   * @param \Drupal\connect_four\Entity\Move $playedMove
   * @return \Drupal\connect_four\Entity\Move[]
   */
  public function getMaximumMovesInline(Move $playedMove) {
    $total = [];
    foreach ($this->getRelativeCoordinates() as $relativeCoordinates) {
      $this->adjacentMoves = [$playedMove];
      $this->countMovesInDirection($playedMove, $relativeCoordinates);
      if (count($this->adjacentMoves) > 1) {
        $oppositeCoordinates = new Coordinates($relativeCoordinates->getX() * -1, $relativeCoordinates->getY() * -1);
        $this->countMovesInDirection($playedMove, $oppositeCoordinates);
      }
      if (count($this->adjacentMoves) > count($total)) {
        $total = $this->adjacentMoves;
      }
    }
    return $total;
  }

  /**
   * Count the moves in a certain direction by adding them to ::adjacentMoves
   *
   * @param \Drupal\connect_four\Entity\Move $move
   * @param \Drupal\connect_four\Coordinates $relativeCoordinates
   */
  private function countMovesInDirection(Move $move, Coordinates $relativeCoordinates) {
    $coordinatesToCheck = new Coordinates($move->getX() + $relativeCoordinates->getX(), $move->getY() + $relativeCoordinates->getY());
    $moveToCheck = $this->getMoveByCoordinates($move->getGame(), $coordinatesToCheck, $move->getOwner());
    if ($moveToCheck) {
      $this->adjacentMoves[] = $moveToCheck;
      $this->countMovesInDirection($moveToCheck, $relativeCoordinates);
    }
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
    if (!$game->hasFinished()) {
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
    if (is_null($movesInLine)) {
      throw new ConnectFourException('The getMaximumMovesInLine did not retur anything');
    }
    if (count($movesInLine) == Game::CONSECUTIVE) {
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
  public function declareWinner(Game $game, AccountInterface $account) {
    $game->set('winner', $account->id());
    $game->set('game_status', GAME::FINISHED);
    $game->save();

    return $game;
  }


}
