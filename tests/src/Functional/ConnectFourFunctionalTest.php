<?php
/**
 * @file
 * Contains \Drupal\Tests\connect_four\Functional\ConnectFourFunctionalTest.php
 */

namespace Drupal\Tests\connect_four\Functional;

use Drupal\connect_four\Entity\Game;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;

/**
 * Class ConnectFourFunctionalTest
 * @package Drupal\Tests\connect_four\Functional
 *
 * @group connect_four
 */
class ConnectFourFunctionalTest extends BrowserTestBase  {

  public static $modules = [
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
  }

  /**
   * Test if the 'connect-four' page returns a valid response.
   */
  public function testGameStart(){
    // Login the Home user.
    $this->drupalLogin($this->homeUser);
    $this->drupalGet('connect-four');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test with a user that doesn't have the 'view published game entities'
   * permission.
   */
  public function testNoPermission(){
    $noAccessUser = $this->drupalCreateUser([]);
    $this->drupalLogin($noAccessUser);
    $this->drupalGet('connect-four');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Test if Home user can see buttons and Away user can't see buttons.
   */
  public function testSeeButtons(){
    $this->drupalLogin($this->homeUser);
    $this->drupalGet('connect-four');
    $this->assertSession()->buttonNotExists('Play');

    $this->click('input[type="submit"]:first-child');
    $this->assertSession()->buttonNotExists('Play');

    $this->drupalLogin($this->awayUser);
    $this->drupalGet('connect-four');
    $this->assertSession()->buttonExists('Play');
  }

  /**
   * Tests if Home user wins after playing four in one column.
   */
  public function testHomeWins(){
    $this->drupalLogin($this->homeUser);
    $this->drupalGet('connect-four');
    $this->click('#connect-four-form th:nth-child(1) input[type="submit"]');
    $this->drupalLogin($this->awayUser);
    $this->drupalGet('connect-four');
    $this->click('#connect-four-form th:nth-child(2) input[type="submit"]');
    $this->drupalLogin($this->homeUser);
    $this->drupalGet('connect-four');
    $this->click('#connect-four-form th:nth-child(1) input[type="submit"]');
    $this->drupalLogin($this->awayUser);
    $this->drupalGet('connect-four');
    $this->click('#connect-four-form th:nth-child(2) input[type="submit"]');
    $this->drupalLogin($this->homeUser);
    $this->drupalGet('connect-four');
    $this->click('#connect-four-form th:nth-child(1) input[type="submit"]');
    $this->drupalLogin($this->awayUser);
    $this->drupalGet('connect-four');
    $this->click('#connect-four-form th:nth-child(2) input[type="submit"]');
    $this->drupalLogin($this->homeUser);
    $this->drupalGet('connect-four');
    $this->click('#connect-four-form th:nth-child(1) input[type="submit"]');
    $this->assertSession()->elementTextContains('css', '#connect-four-form', 'winner');
    $this->assertSession()->elementTextContains('css', '#connect-four-form', 'home');
  }
}

