<?php
/**
 * @file
 * Contains \Drupal\Tests\connect_four\Kernel\ConnectFourKernelTest
 */

namespace Drupal\Tests\connect_four\Kernel;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\KernelTests\KernelTestBase;

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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('connect_four_move');
    $this->installEntitySchema('connect_four_game');
    $this->installEntitySchema('user');

    $this->entityTypeManager = \Drupal::getContainer()
      ->get('entity_type.manager');
  }

  public function testKernelTest() {
    $this->markTestIncomplete('This test hasnt been implemented yet');
  }
}