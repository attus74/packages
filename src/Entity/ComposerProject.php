<?php

namespace Drupal\packages\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\file\FileInterface;
use Drupal\user\UserInterface;
use Drupal\packages\ComposerProjectInterface;

/**
 * Entity Type Composer Project
 *
 * @author Attila Németh
 * 01.12.2020
 *
 * @ContentEntityType(
 *   id = "composer_project",
 *   label = @Translation("Composer Project"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\packages\Controller\ComposerProjectList",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\packages\Form\ComposerProjectForm",
 *       "edit" = "Drupal\packages\Form\ComposerProjectForm",
 *       "delete" = "Drupal\packages\Form\ComposerProjectDeleteForm",
 *     },
 *     "access" = "Drupal\packages\ComposerProjectAccess",
 *   },
 *   base_table = "composer_project",
 *   admin_permission = "administer composer_project",

 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",

 *   },
 *   links = {
 *     "canonical" = "/composer/project/{composer_project}",
 *     "add-form" = "/composer/project/add",
 *     "edit-form" = "/composer/project/{composer_project}/edit",
 *     "delete-form" = "/composer/project/{composer_project}/delete",
 *     "collection" = "/composer/project",
 *   },
 *   field_ui_base_route = "composer_project.settings",
 * )
 */
class ComposerProject extends ContentEntityBase implements ComposerProjectInterface {

  use EntityChangedTrait; // Implements methods defined by EntityChangedInterface.

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
   parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * This method may need a manual edit
   *
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('Entity Id'))
      ->setReadOnly(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('Entity UUID'))
      ->setReadOnly(TRUE);
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('Composer Project label'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -19,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -19,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setReadOnly(TRUE);
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('Create Date'));
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('Change Date'));

    // You can add additional fields here

    return $fields;
  }
  
  /**
   * Possible new versions
   */
  public function getNewVersionOptions(): array
  {
    
    opcache_reset();
    
    $versions = [];
    if ($this->get('project_packages')->isEmpty()) {
      $versions['1.0.0'] = '1.0.0';
      $versions['1.0-dev'] = '1.0-dev';
    }
    else {
      $existing = [];
      foreach($this->get('project_packages') as $item) {
        $v = $item->entity->get('version')->first()->get('value')->getvalue();
        if (preg_match('/^([0-9]+)\.([0-9]+)(\.|\-)(.*?)$/', $v, $matches)) {
          $existing[$matches[1]][$matches[2]][$matches[4]] = TRUE;
        }
      }
      ksort($existing);
      $maxMajor = 0;
      foreach($existing as $major => $minors) {
        if ($major > $maxMajor) {
          $maxMajor  = $major;
        }
        $maxMinor = 0;
        $maxPatch = 0;
        ksort($minors);
        foreach($minors as $minor => $patches) {
          if ($minor > $maxMinor) {
            $maxMinor = $minor;
          }
          foreach(array_keys($patches) as $patch) {
            if (is_numeric($patch) && $patch > $maxPatch) {
              $maxPatch = $patch;
            }
          }
        }
        $versions[$major . '.' . ($maxMinor + 1) . '.0'] = $major . '.' . ($maxMinor + 1) . '.0';
        $versions[$major . '.' . ($maxMinor) . '-dev'] = $major . '.' . ($maxMinor) . '-dev';
        $versions[$major . '.' . ($maxMinor) . '-alpha'] = $major . '.' . ($maxMinor) . '-alpha';
        $versions[$major . '.' . ($maxMinor) . '.' . ($maxPatch + 1)] = $major . '.' . ($maxMinor) . '.' . ($maxPatch + 1);
        $versions[$major . '.' . ($maxMinor + 1) . '-dev'] = $major . '.' . ($maxMinor + 1) . '-dev';
        $versions[$major . '.' . ($maxMinor + 1) . '-alpha'] = $major . '.' . ($maxMinor + 1) . '-alpha';
      }
      $versions[($maxMajor + 1) . '.0.0'] = ($maxMajor + 1) . '.0.0';
      $versions[($maxMajor + 1) . '.0-dev'] = ($maxMajor + 1) . '.0-dev';
      $versions[($maxMajor + 1) . '.0-alpha'] = ($maxMajor + 1) . '.0-alpha';
    }
    ksort($versions);
    return $versions;
  }
  
  /**
   * {@inheritdoc}
   */
  public function addNewPackage(string $version, FileInterface $file, array $description): void
  {
    $service = \Drupal::service('packages.manager');
    $service->unzipFile($file);
    $service->applyVersion($version);
    $values = [
      'version' => $version,
      'package_description' => $description,
      'package_composer' => $service->getComposer(),
    ];
    $package = \Drupal::entityTypeManager()->getStorage('composer_package')->create($values);
    $package->save();
    $file->setPermanent();
    $file->save();
    $packages = [];
    foreach($this->get('project_packages') as $item) {
      $packages[] = [
        'entity' => $item->entity,
      ];
    }
    $packages[] = [
      'entity' => $package,
    ];
    $this->set('project_packages', $packages);
  }
  
}