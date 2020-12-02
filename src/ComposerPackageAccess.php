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
 * @date 02.12.2020
 */
class ComposerPackageAccess extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view composer_package');
      case 'edit':
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit composer_package');
      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete composer_package');
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
    return AccessResult::allowedIfHasPermission($account, 'create composer_package');
  }

}