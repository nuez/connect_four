<?php
/**
 * @file
 * Contains \Drupal\connect_four\Direction.php
 */
namespace Drupal\connect_four;

class Coordinates {
  /**
   * @var int $x;
   */
  private $x;

  /**
   * @var int $y;
   */
  private $y;

  /**
   * Position constructor.
   *
   * @param $x
   * @param $y
   */
  public function __construct($x, $y) {
    $this->x = $x;
    $this->y = $y;
  }

  /**
   * @return int
   */
  public function getX(){
    return $this->x;
  }

  public function getY(){
    return $this->y;
  }

}