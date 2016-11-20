<?php
/**
 * @file
 * Contains \Drupal\connect_four\Exception\ConnectFourException
 */

namespace Drupal\connect_four\Exception;

/**
 * Class ConnectFourException
 * @package Drupal\connect_four\Exception
 */
class ConnectFourException extends \Exception  {

  public function __construct($class) {
    parent::__construct($class);
  }
}