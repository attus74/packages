<?php

namespace Drupal\packages;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Composer Project Interface
 */
interface ComposerProjectInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {
}