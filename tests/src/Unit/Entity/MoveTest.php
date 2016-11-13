<?php
/**
 * @file
 * Contains \Drupal\connect_four\Tests\ConnectFourServiceTests
 */

namespace Drupal\Tests\connect_four\Unit\Entity;

use Drupal\connect_four\Entity\Game;
use Drupal\connect_four\Entity\Move;
use Drupal\Core\Database\Query\Query;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\Query\ConditionInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\Entity\User;
use Prophecy\Prophecy\ProphecyInterface;


define('REQUEST_TIME', (int) $_SERVER['REQUEST_TIME']);


/**
 * @coversDefaultClass \Drupal\connect_four\Entity\Move
 *
 * @group connect_four
 */
class MoveTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests Exception when a move is played outside the board.
   *
   * @covers ::create
   *
   * @expectedException \Drupal\connect_four\Exception\ConnectFourException
   *
   * @expectedExceptionMessage The move cannot be created outside the playing board.
   */
  public function testExceptionMoveExceedBoardX() {

    // Create a game with X one bigger than the width of the board.
    $x = Game::WIDTH;
    $y = 0;

    Move::create([
      'x' => $x,
      'y' => $y,
      'user_id' => $this->prophesize(User::class)->reveal(),
      'game' => $this->prophesize(Game::class)->reveal(),
      'created' => REQUEST_TIME,
    ]);

  }

  /**
   * Tests Exception when a move is played outside the board.
   *
   * @covers ::create
   *
   * @expectedException \Drupal\connect_four\Exception\ConnectFourException
   *
   * @expectedExceptionMessage The move cannot be created outside the playing board.
   */
  public function testExceptionMoveExceedBoardY() {

    // Create a game with X one bigger than the width of the board.
    $x = 0;
    $y = Game::HEIGHT;

    // The following should run into an exception.
    Move::create([
      'x' => $x,
      'y' => $y,
      'user_id' => $this->prophesize(User::class)->reveal(),
      'game' => $this->prophesize(Game::class)->reveal(),
      'created' => REQUEST_TIME,
    ]);

  }



  /**
   * Test Exception when a move is played on top of an already made move.
   *
   * @covers ::create
   *
   * @expectedException \Drupal\connect_four\Exception\ConnectFourException
   *
   * @expectedExceptionMessage The move cannot be created with the same
   * coordinates of an already existing move.
   */
  public function testExceptionDuplicateMove() {

    // Coinciding coordinates.
    $x = 1;
    $y = 1;

    /** @var Move|ProphecyInterface $existingMove */
    $existingMove = $this->prophesize(Move::class);
    $existingMove->getX()->willReturn($x);
    $existingMove->getY()->willReturn($y);

    /** @var Game|ProphecyInterface $game */
    $game = $this->prophesize(Game::class);
    $game->getMoves()->willReturn([
      $existingMove->reveal()
    ]);


    /** @var QueryFactory|ProphecyInterface $queryFactory */
    $queryFactory = $this->prophesize(QueryFactory::class);

    /** @var QueryInterface|ProphecyInterface $query */
    $query = $this->prophesize(QueryInterface::class);

    $game = $this->prophesize(Game::class);
    $query->condition()->willReturn($query->reveal());

    $query->condition("x", $x)->willReturn($query->reveal());
    $query->condition("y", $y)->willReturn($query->reveal());
    $query->condition("game", $game->reveal())->willReturn($query->reveal());

    $query->execute()->willReturn([rand(1,9)]);

    /*
     * Using the mockBuilder to mock the QueryFactory.
     */
    /** @var \PHPUnit_Framework_MockObject_MockObject $queryFactory */
    $queryFactory = $this->getMockBuilder(QueryFactory::class)
      ->disableOriginalConstructor()
      ->getMock();

    $queryFactory->expects($this->any())
      ->method('get')
      ->willReturn($query->reveal());

    $container = new ContainerBuilder();
    $container->set('entity.query', $queryFactory);
    \Drupal::setContainer($container);

    Move::create([
      'x' => $x,
      'y' => $y,
      'user_id' => $this->prophesize(User::class)->reveal(),
      'game' => $game->reveal(),
      'created' => REQUEST_TIME,
    ]);

    /*
     * Using Prophesy to mock the query factory.
     */
    /** @var QueryFactory|ProphecyInterface $queryFactory */
    $queryFactory->get('connect_four_move')->willReturn($query->reveal());

    $container = new ContainerBuilder();
    $container->set('entity.query', $queryFactory->reveal());
    \Drupal::setContainer($container);

    // The following should run into an Exception.
    Move::create([
      'x' => $x,
      'y' => $y,
      'user_id' => $this->prophesize(User::class)->reveal(),
      'game' => $game->reveal(),
      'created' => REQUEST_TIME,
    ]);

  }

  /**
   * Tests invalid moves of discs that 'float in the air'
   *
   * When a move is played without Y being 0, or without
   * having any discs underneath, it should throw an exception.
   *
   * @covers ::create
   *
   * @expectedException \Drupal\connect_four\Exception\ConnectFourException
   * @expectedExceptionMessage Cannot play a move that floats in the air.
   */
  public function testExceptionFloatingMove() {
    $this->markTestIncomplete('hasnt been implemented yet');
  }
}