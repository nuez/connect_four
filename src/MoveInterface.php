<?php

namespace Drupal\connect_four;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Move entities.
 *
 * @ingroup connect_four
 */
interface MoveInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Move creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Move.
   */
  public function getCreatedTime();

  /**
   * Sets the Move creation timestamp.
   *
   * @param int $timestamp
   *   The Move creation timestamp.
   *
   * @return \Drupal\connect_four\MoveInterface
   *   The called Move entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * @return int
   */
  public function getX();

  /**
   * @return int
   */
  public function getY();

  /**
   * @return boolean
   */
  public function isHome();


}
