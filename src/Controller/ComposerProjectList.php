<?php

namespace Drupal\packages\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Composer Project List
 *
 * @author Attila Németh
 * 01.12.2020
 */
class ComposerProjectList extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      t('Title')
    ];
    // You can add custom header elements, e.g.:
    // $header['fieldName'] = t('Field Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [
      $entity->toLink()->toString(),
    ];
    // You can add custom row elements, e.g.:
    // $row['fieldName'] = $entity->get('fieldName')->get(0)->get('value')->getValue();
    return $row + parent::buildRow($entity);
  }

}