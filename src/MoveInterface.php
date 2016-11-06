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
  // Add get/set methods for your configuration properties here.
  /**
   * Gets the Move name.
   *
   * @return string
   *   Name of the Move.
   */
  public function getName();

  /**
   * Sets the Move name.
   *
   * @param string $name
   *   The Move name.
   *
   * @return \Drupal\connect_four\MoveInterface
   *   The called Move entity.
   */
  public function setName($name);

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
   * Returns the Move published status indicator.
   *
   * Unpublished Move are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Move is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Move.
   *
   * @param bool $published
   *   TRUE to set this Move to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\connect_four\MoveInterface
   *   The called Move entity.
   */
  public function setPublished($published);

}
