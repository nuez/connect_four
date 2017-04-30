<?php
/**
 * @file
 * Contains \Drupal\Tests\connect_four\Kernel\ConnectFourKernelTest
 */

namespace Drupal\Tests\connect_four\Kernel;

use Drupal\connect_four\ConnectFourService;
use Drupal\connect_four\Entity\Game;
use Drupal\connect_four\Entity\Move;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Serialization\Yaml;
use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\User;


/**
 * @coversDefaultClass \Drupal\connect_four\ConnectFourService
 *
 * @group connect_four
 */
class ConnectFourKernelTest extends KernelTestBase {

  /**
   * @var array $modules
   */
  public static $modules = [
    'system',
    'connect_four',
    'user',
    'options',
    'field'
  ];

  /**
   * @var ConnectFourService $connectFourService
   */
  protected $connectFourService;

  /**
   * @var User
   */
  protected $homeUser;

  /**
   * @var User
   */
  protected $awayUser;

  /**
   * @var Game
   */
  protected $game;


  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('connect_four_move');
    $this->installEntitySchema('connect_four_game');
    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');

    $this->connectFourService = \Drupal::getContainer()
      ->get('connect_four.service');

    $this->homeUser = User::create([
      'name' => 'home',
      'email' => 'home@home.home',
    ]);
    $this->homeUser->save();
    $this->awayUser = User::create([
      'name' => 'away',
      'email' => 'away@away.away',
    ]);
    $this->awayUser->save();
    $this->game = Game::create([
      'home' => $this->homeUser,
      'away' => $this->awayUser,
      'created' => REQUEST_TIME,
    ]);
    $this->game->save();

  }

  /**
   * Test the result when the size of the board changes.
   */
  public function testDifferentSizesForGame() {
    $this->markTestIncomplete('This test hasnt been implemented yet');
  }

  /**
   * Test that the Home user wins.
   */
  public function testHomeUserWins() {
    $this->connectFourService->playMove($this->game, 0, $this->homeUser);
    $this->connectFourService->playMove($this->game, 1, $this->awayUser);
    $this->connectFourService->playMove($this->game, 0, $this->homeUser);
    $this->connectFourService->playMove($this->game, 1, $this->awayUser);
    $this->connectFourService->playMove($this->game, 0, $this->homeUser);
    $this->connectFourService->playMove($this->game, 1, $this->awayUser);
    $this->connectFourService->playMove($this->game, 0, $this->homeUser);
    $this->assertTrue($this->game->getWinner()->id() == $this->homeUser->id(), 'The home user is winner');
  }

  /**
   * Test that the Home user wins.
   */
  public function testAwayUserWins() {
    $this->connectFourService->playMove($this->game, 0, $this->homeUser);
    $this->connectFourService->playMove($this->game, 1, $this->awayUser);
    $this->connectFourService->playMove($this->game, 3, $this->homeUser);
    $this->connectFourService->playMove($this->game, 1, $this->awayUser);
    $this->connectFourService->playMove($this->game, 0, $this->homeUser);
    $this->connectFourService->playMove($this->game, 1, $this->awayUser);
    $this->connectFourService->playMove($this->game, 0, $this->homeUser);
    $this->connectFourService->playMove($this->game, 1, $this->awayUser);
    $this->assertTrue($this->game->getWinner()->id() == $this->awayUser->id(), 'The away user is winner');
  }


  /**
   * Tests the maximum amount of moves in a single line.
   *
   * @covers ::getMaximumMovesInline
   *
   * @param array $movesData
   *   Array of data for specific moves.
   * @param int $expected
   *   The expected amount of moves in one line.
   *
   * @dataProvider movesDataProvider
   */
  public function testGetMaximumMovesInline($movesData, $expected) {
    // Create moves using the data passed by the data provider.
    foreach ($movesData as $data) {
      $move = Move::create([
        'x' => $data['x'],
        'y' => $data['y'],
        'game' => $this->game->id(),
        'user_id' => $data['user_id'],
        'created' => REQUEST_TIME,
      ]);
      $move->save();
      $moves[] = $move;
    }

    // Get the maximum amount of moves in one line based on the last
    // move that was passed by the data provider.
    $lastMove = end($moves);
    $totalMovesInline = $this->connectFourService->getMaximumMovesInline($lastMove);

    // Assert that the sum of the returned moves in one line is equal to
    // what we expect.
    $this->assertEquals($expected, count($totalMovesInline));
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