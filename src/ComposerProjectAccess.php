<?php

namespace Drupal\packages;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Zugriffskontrolle
 *
 * @author Attila Németh
 * @date 01.12.2020
 */
class ComposerProjectAccess extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view composer_project');
      case 'edit':
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit composer_project');
      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete composer_project');
      default:
        throw new \Exception(t('Unknown Operation: @op', [
          '@op' => $op,
        ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create composer_project');
  }

}