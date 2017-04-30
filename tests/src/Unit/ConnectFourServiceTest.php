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
  public function testGetMaximumMovesInline($data, $expected) {

    /** @var Game|ProphecyInterface $game */
    $game = $this->prophesize(Game::class);

    foreach ($data as $dataPerMove) {
      /** @var Move|ProphecyInterface $move */
      $move = $this->prophesize(Move::class);
      $move->getX()->willReturn($dataPerMove['x']);
      $move->getY()->willReturn($dataPerMove['y']);
      $move->getGame()->willReturn($game);

      /** @var AccountInterface|ProphecyInterface $owner */
      $owner = $this->prophesize(AccountInterface::class);
      $owner->id()->willReturn($dataPerMove['user_id']);

      $move->getOwner()->willReturn($owner->reveal());
      $moves[] = $move->reveal();
    }
    $lastMove = end($moves);

    $game->getMoves()->willReturn($moves);
    $this->assertEquals($expected, count($this->sut->getMaximumMovesInline($lastMove)));
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
    return Yaml::decode(file_get_contents(__DIR__.'/../movesScenarios.yml'));
  }


}