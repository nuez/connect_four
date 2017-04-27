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
   * Tests polling of the opponents move.
   */
  public function testPolling(){
    $this->drupalLogin($this->awayUser);
    $this->drupalGet('connect-four');

    // The Away user should not be able to play right away but has to wait
    // for the Home user to play first. Then the 'play buttons' will appear
    // automatically through an ajax call.
    $this->assertSession()->elementNotExists('css','input[type="submit"]');
    $this->createScreenshot('public://screenshot.jpg');

    // The Home User plays the move (using code). Keep the
    // Away user logged in and on the same page.
    $this->connectFourService->playMove($this->game, 0, $this->homeUser);

    // Ajax should pick this move up.
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->createScreenshot('public://screenshot_after.jpg');
    $this->assertSession()->elementExists('css', 'input[type="submit"]');

  }
}

