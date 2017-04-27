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
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Tests\UnitTestCase;
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
   * Tests the maximum amount of moves in a single line.
   *
   * @covers ::getMaximumMovesInline
   *
   * @dataProvider movesDataProvider
   */
  public function testGetMaximumMovesInline($movesData, $max) {

    /** @var Move|ProphecyInterface $lastMove */
    $lastMove = $this->prophesize(Move::class);
    $lastMove->getX()->willReturn(end($movesData)['x']);
    $lastMove->getY()->willReturn(end($movesData)['y']);

    /** @var AccountInterface|ProphecyInterface $lastMoveOwner */
    $lastMoveOwner = $this->prophesize(AccountInterface::class);
    $lastMoveOwner->id()->willReturn(end($movesData)['user_id']);

    $lastMove->getOwner()->willReturn($lastMoveOwner->reveal());
    $lastMove->getOwnerId()->willReturn(end($movesData)['user_id']);

    /** @var Game|ProphecyInterface $game */
    $game = $this->prophesize(Game::class);
    $lastMove->getGame()->willReturn($game->reveal());

    foreach ($movesData as $data) {
      /** @var Move|ProphecyInterface $move */
      $move = $this->prophesize(Move::class);
      $move->getX()->willReturn($data['x']);
      $move->getY()->willReturn($data['y']);
      $move->getOwnerId()->willReturn($data['user_id']);
      $move->getGame()->willReturn($game);
      $owner = $this->prophesize(AccountInterface::class);
      $owner->id()->willReturn($data['user_id']);
      $move->getOwner()->willReturn($owner->reveal());
      $move->getOwner()->willReturn($owner);
      $movesCollection[] = $move->reveal();
    }

    $game->getMoves()->willReturn($movesCollection);
    $this->assertEquals($max, count($this->sut->getMaximumMovesInline($lastMove->reveal())));
  }

  /**
   * Dataprovider for ::getMaximumMovesInline.
   *
   * Returns different scenarios for moves played and maximum amount of discs
   * in one line.
   *
   * @return array
   */
  public function movesDataProvider() {
    return Yaml::decode(file_get_contents('../movesScenarios.yml'));
  }


}