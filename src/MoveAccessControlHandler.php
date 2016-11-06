<?php

namespace Drupal\connect_four;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Move entity.
 *
 * @see \Drupal\connect_four\Entity\Move.
 */
class MoveAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\connect_four\MoveInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished move entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published move entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit move entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete move entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add move entities');
  }

}
