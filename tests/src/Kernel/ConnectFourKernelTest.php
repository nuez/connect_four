<?php
/**
 * @file
 * Contains \Drupal\Tests\connect_four\Kernel\ConnectFourKernelTest
 */

namespace Drupal\Tests\connect_four\Kernel;

use Drupal\connect_four\ConnectFourService;
use Drupal\connect_four\Entity\Game;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\User;

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
   * @var EntityTypeManager $entityTypeManager ;
   */
  protected $entityTypeManager;

  /**
   * @var ConnectFourService $connectFourService
   */
  protected $connectFourService;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('connect_four_move');
    $this->installEntitySchema('connect_four_game');
    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');

    $this->entityTypeManager = \Drupal::getContainer()
      ->get('entity_type.manager');
    $this->connectFourService = \Drupal::getContainer()
      ->get('connect_four.service');
  }

  /**
   * Mark a test as incomplete.
   */
  public function testIncompleteKernelTest() {
    $this->markTestIncomplete('This test hasnt been implemented yet');
  }

  /**
   * Test that the Home user wins.
   */
  public function testHomeUserWins() {

    $homeUser = User::create([
      'name' => 'home',
      'email' => 'home@home.home',
    ]);
    $homeUser->save();
    $awayUser = User::create([
      'name' => 'away',
      'email' => 'away@away.away',
    ]);
    $awayUser->save();
    $game = Game::create([
      'home' => $homeUser,
      'away' => $awayUser,
      'created' => REQUEST_TIME,
    ]);
    $game->save();

    $this->connectFourService->playMove($game, 0, $homeUser);

    $this->connectFourService->playMove($game, 1, $awayUser);

    $this->connectFourService->playMove($game, 0, $homeUser);

    $this->connectFourService->playMove($game, 1, $awayUser);
    $this->connectFourService->playMove($game, 0, $homeUser);

    $this->connectFourService->playMove($game, 1, $awayUser);

    $this->connectFourService->playMove($game, 0, $homeUser);

    $this->assertTrue($game->getWinner()->id() == $homeUser->id(), 'The home user is winner');
  }

  /**
   * Test that the Home user wins.
   */
  public function testAwayUserWins() {
    $homeUser = User::create([
      'name' => 'home',
      'email' => 'home@home.home',
    ]);
    $homeUser->save();
    $awayUser = User::create([
      'name' => 'away',
      'email' => 'away@away.away',
    ]);
    $awayUser->save();
    $game = Game::create([
      'home' => $homeUser,
      'away' => $awayUser,
      'created' => REQUEST_TIME,
    ]);
    $game->save();

    $this->connectFourService->playMove($game, 0, $homeUser);

    $this->connectFourService->playMove($game, 1, $awayUser);

    $this->connectFourService->playMove($game, 3, $homeUser);

    $this->connectFourService->playMove($game, 1, $awayUser);
    $this->connectFourService->playMove($game, 0, $homeUser);

    $this->connectFourService->playMove($game, 1, $awayUser);

    $this->connectFourService->playMove($game, 0, $homeUser);

    $this->connectFourService->playMove($game, 1, $awayUser);

    $this->assertTrue($game->getWinner()->id() == $awayUser->id(), 'The away user is winner');
  }

}