<?php

namespace Drupal\packages;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\file\FileInterface;

/**
 * Composer Project Interface
 */
interface ComposerProjectInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {
  
  /**
   * Possible new versions
   */
  public function getNewVersionOptions(): array;
  
  /**
   * Add a new package to the project
   * @param string $version
   *  Version number
   * @param FileInterface $file
   *  ZIP file to download
   * @param array $description
   *  Package description, array like text_format:
   *    - value
   *    - format
   */
  public function addNewPackage(string $version, FileInterface $file, array $description): void;
  
}