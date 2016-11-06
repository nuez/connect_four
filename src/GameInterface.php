<?php

namespace Drupal\connect_four;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Game entities.
 *
 * @ingroup connect_four
 */
interface GameInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.
  /**
   * Gets the Game name.
   *
   * @return string
   *   Name of the Game.
   */
  public function getName();

  /**
   * Sets the Game name.
   *
   * @param string $name
   *   The Game name.
   *
   * @return \Drupal\connect_four\GameInterface
   *   The called Game entity.
   */
  public function setName($name);

  /**
   * Gets the Game creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Game.
   */
  public function getCreatedTime();

  /**
   * Sets the Game creation timestamp.
   *
   * @param int $timestamp
   *   The Game creation timestamp.
   *
   * @return \Drupal\connect_four\GameInterface
   *   The called Game entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Game published status indicator.
   *
   * Unpublished Game are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Game is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Game.
   *
   * @param bool $published
   *   TRUE to set this Game to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\connect_four\GameInterface
   *   The called Game entity.
   */
  public function setPublished($published);

}
