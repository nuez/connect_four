<?php
/**
 * @file
 * Contains \Drupal\Tests\connect_four\Functional\ConnectFourFunctionalTest.php
 */

namespace Drupal\Tests\connect_four\Javascript;

use Drupal\connect_four\ConnectFourService;
use Drupal\connect_four\Entity\Game;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\user\Entity\User;

/**
 * Class ConnectFourJavascriptTest
 * @package Drupal\Tests\connect_four\Javascript
 *
 * @group connect_four
 */
class ConnectFourJavascriptTest extends JavascriptTestBase  {

  public static $modules = [
    'block',
    'system',
    'connect_four',
    'user',
    'options',
    'field',
  ];

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
   * @var ConnectFourService
   */
  protected $connectFourService;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->homeUser = $this->drupalCreateUser(['view published game entities'], 'home');
    $this->awayUser = $this->drupalCreateUser(['view published game entities'], 'away');
    $this->game = Game::create([
      'home' => $this->homeUser,
      'away' => $this->awayUser,
      'game_status' => GAME::STARTED,
      'status' => TRUE,
      'created' => REQUEST_TIME,
      'updated' => REQUEST_TIME,
    ]);
    $this->game->save();

    $this->connectFourService = \Drupal::service('connect_four.service');

  }


  /**
   * Tests if Polling works.
   */
  public function testHomeWins(){
    $this->drupalLogin($this->awayUser);
    $this->connectFourService->playMove($this->game, 0, $this->homeUser);
    $this->drupalGet('connect-four');
    $this->assertSession()->assertVisibleInViewport('css', '#edit-play');
    $this->assertSession()->assertWaitOnAjaxRequest();
  }
}

