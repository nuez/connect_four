<?php
/**
 * @file
 * Contains \Drupal\connect_four\Tests\ConnectFourServiceTests
 */

namespace Drupal\Tests\connect_four\Unit\ConnectFourServiceTests;

use Drupal\connect_four\ConnectFourService;
use Drupal\connect_four\Entity\Game;
use Drupal\connect_four\Entity\Move;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountProxy;
use Drupal\Tests\UnitTestCase;
use Drupal\user\Entity\User;
use Prophecy\Prophecy\ProphecyInterface;

/**
 * @coversDefaultClass \Drupal\connect_four\ConnectFourService
 *
 * @group connect_four
 */
class ConnectFourServiceTest extends UnitTestCase {

  /**
   * The Subject Under Test.
   *
   * @var ConnectFourService $sut ;
   */
  protected $sut;

  /**
   * @var EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @var QueryFactory
   */
  protected $queryFactory;

  /**
   * @var AccountProxy
   */
  protected $accountProxy;


  /**
   * Setup the test.
   */
  public function setUp() {
    parent::setUp();
    $this->entityTypeManager = $this->prophesize(EntityTypeManager::class);
    $this->queryFactory = $this->prophesize(QueryFactory::class);
    $this->accountProxy = $this->prophesize(AccountProxy::class);
    $this->sut = new ConnectFourService($this->entityTypeManager->reveal(), $this->queryFactory->reveal(), $this->accountProxy->reveal());
  }

  /**
   * Tests if it's the users' turn to play and if the column hasn't been filled yet.
   *
   * @covers ::canPlayMove
   */
  public function testTurnUsers() {
    // The Home Player should only be able to play when the amount of
    // played moves is even.
    /** @var User|ProphecyInterface $homeUser */
    $homeUser = $this->prophesize(User::class);
    $homeUser->id()->willReturn(1);

    /** @var User|ProphecyInterface $awayUser */
    $awayUser = $this->prophesize(User::class);
    $awayUser->id()->willReturn(2);

    /** @var Game|ProphecyInterface $game */
    $game = $this->prophesize(Game::class);
    $game->getHomeUser()->willReturn($homeUser);
    $game->getAwayUser()->willReturn($awayUser);
    $game->hasFinished()->willReturn(FALSE);

    $game->getMoves()->willReturn([
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
    ]);

    // The first Column will be half filled and will still have room to play.
    $game->getMovesByX(0)->willReturn([
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
    ]);

    // The second Column Will be filled and will not have room left to play.
    $game->getMovesByX(1)->willReturn([
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
    ]);

    // The third column will be filled apart from one.
    $game->getMovesByX(2)->willReturn([
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
    ]);

    // The Home Player can play this move in the first column.
    $this->assertTrue($this->sut->canPlayMove($game->reveal(), 0, $homeUser->reveal()));

    // The Away player cannot play this move as it isn't his turn.
    $this->assertFalse($this->sut->canPlayMove($game->reveal(), 0, $awayUser->reveal()));

    // The Home player cannot play this move in the second column as it's full.
    $this->assertFalse($this->sut->canPlayMove($game->reveal(), 1, $homeUser->reveal()));

    // The Home player can play this move in the second column as it isnt full yet.
    $this->assertTrue($this->sut->canPlayMove($game->reveal(), 2, $homeUser->reveal()));

    // Test if Away has permission when total moves are uneven.
    $game->getMoves()->willReturn([
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
      $this->prophesize(Move::class)->reveal(),
    ]);

    // The Home player cannot play this move in the second column as it's full.
    $this->assertFalse($this->sut->canPlayMove($game->reveal(), 0, $homeUser->reveal()));

    // The Home player can play this move in the second column as it isnt full yet.
    $this->assertTrue($this->sut->canPlayMove($game->reveal(), 0, $awayUser->reveal()));

  }


  /**
   * Tests getting the maximum amount of moves in a single line.
   *
   * @covers ::getMaximumMovesInline
   *
   * @dataProvider movesDataProvider
   */
  public function testGetMaximumMovesInLine($movesData, $max) {
    $moves = [];

    /** @var Game|ProphecyInterface $game */
    $game = $this->prophesize(Game::class);
    $game->id()->willReturn(1);

    foreach ($movesData as $data) {

      /** @var User|ProphecyInterface $owner */
      $owner = $this->prophesize(User::class);
      $owner->id()->willReturn($data['user_id']);

      /** @var Move|ProphecyInterface $move */
      $move = $this->prophesize(Move::class);

      // Get the X and Y coordinates from the dataprovider.
      $move->getX()->willReturn($data['x']);
      $move->getY()->willReturn($data['y']);
      $move->getOwnerId()->willReturn($data['user_id']);

      $moves[] = $move->reveal();
      $move->getGame()->willReturn($game->reveal());
    }
    $game->getMoves()->willReturn($moves);
    $this->assertEquals($max, count($this->sut->getMaximumMovesInLine(end($moves))));
  }

  /**
   * Dataprovider for ::getMaximumMovesInline.
   *
   * @return array
   */
  public function movesDataProvider() {

    /*
     *  Returns an array with the moves and the expected count of maxmimum
     *  amount of moves in one line.
     *
     *  The last item in 'moves' is considered the last Move, and is the
     *  one used to check lines.
     *
     *  1. 4 in a row vertically.
     *  2. 4 in a row horizontally.
     *  3. A diagonal row.
     *     Assume that the last disc that was played was in the middle X=2,Y=2
     *     | | | |A|A|B|
     *     | | | |A|A|B|
     *     | |A|A|B|A|B|
     *     |A|A|A|A|A|B|
     *     |A|B|B|B|A|A|
     */
    return [
      [
        'moves' => [
          [
            'x' => 0,
            'y' => 1,
            'user_id' => 1,
          ],
          [
            'x' => 0,
            'y' => 2,
            'user_id' => 1,
          ],
          [
            'x' => 0,
            'y' => 3,
            'user_id' => 1,
          ],
        ],
        'max' => 3,
      ],
      [
        'moves' => [
          [
            'x' => 0,
            'y' => 1,
            'user_id' => 1,
          ],
          [
            'x' => 0,
            'y' => 2,
            'user_id' => 1,
          ],
          [
            'x' => 0,
            'y' => 3,
            'user_id' => 1,
          ],
          [
            'x' => 0,
            'y' => 4,
            'user_id' => 1,
          ],
        ],
        'max' => 4,
      ],
      [
        'moves' => [
          [
            'x' => 0,
            'y' => 0,
            'user_id' => 1,
          ],
          [
            'x' => 1,
            'y' => 0,
            'user_id' => 2,
          ],
          [
            'x' => 2,
            'y' => 0,
            'user_id' => 2,
          ],
          [
            'x' => 3,
            'y' => 0,
            'user_id' => 2,
          ],
          [
            'x' => 4,
            'y' => 0,
            'user_id' => 1,
          ],
          [
            'x' => 5,
            'y' => 0,
            'user_id' => 1,
          ],
          [
            'x' => 0,
            'y' => 1,
            'user_id' => 1,
          ],
          [
            'x' => 1,
            'y' => 1,
            'user_id' => 1,
          ],
          [
            'x' => 2,
            'y' => 1,
            'user_id' => 1,
          ],
          [
            'x' => 3,
            'y' => 1,
            'user_id' => 1,
          ],
          [
            'x' => 4,
            'y' => 1,
            'user_id' => 1,
          ],
          [
            'x' => 5,
            'y' => 1,
            'user_id' => 2,
          ],
          [
            'x' => 1,
            'y' => 2,
            'user_id' => 1,
          ],
          [
            'x' => 3,
            'y' => 2,
            'user_id' => 2,
          ],
          [
            'x' => 4,
            'y' => 2,
            'user_id' => 1,
          ],
          [
            'x' => 5,
            'y' => 2,
            'user_id' => 2,
          ],
          [
            'x' => 3,
            'y' => 3,
            'user_id' => 1,
          ],
          [
            'x' => 4,
            'y' => 3,
            'user_id' => 1,
          ],
          [
            'x' => 5,
            'y' => 3,
            'user_id' => 2,
          ],
          [
            'x' => 3,
            'y' => 4,
            'user_id' => 1,
          ],

          [
            'x' => 5,
            'y' => 4,
            'user_id' => 2,
          ],
          [
            'x' => 4,
            'y' => 4,
            'user_id' => 1,
          ],
          [
            'x' => 2,
            'y' => 2,
            'user_id' => 1,
          ],
        ],
        'max' => 5,
      ]
    ];
  }


}