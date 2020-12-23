<?php

namespace Drupal\packages;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Composer Package Interface
 */
interface ComposerPackageInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {
  
  /**
   * Composer Data of this Package
   * @return array
   */
  public function getComposer(): array;
  
}