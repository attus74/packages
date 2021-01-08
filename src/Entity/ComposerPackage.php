<?php

namespace Drupal\packages\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\user\UserInterface;
use Drupal\packages\ComposerPackageInterface;
use Drupal\Component\Serialization\Json;

/**
 * Entity Type Composer Package
 *
 * @author Attila Németh
 * 02.12.2020
 *
 * @ContentEntityType(
 *   id = "composer_package",
 *   label = @Translation("Composer Package"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\packages\Controller\ComposerPackageList",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\packages\Form\ComposerPackageForm",
 *       "edit" = "Drupal\packages\Form\ComposerPackageForm",
 *       "delete" = "Drupal\packages\Form\ComposerPackageDeleteForm",
 *     },
 *     "access" = "Drupal\packages\ComposerPackageAccess",
 *   },
 *   base_table = "composer_package",
 *   admin_permission = "administer composer_package",

 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "version",
 *     "uuid" = "uuid",

 *   },
 *   links = {
 *     "canonical" = "/composer/package/{composer_package}",
 *     "add-form" = "/composer/package/add",
 *     "edit-form" = "/composer/package/{composer_package}/edit",
 *     "delete-form" = "/composer/package/{composer_package}/delete",
 *     "collection" = "/composer/package",
 *   },
 *   field_ui_base_route = "composer_package.settings",
 * )
 */
class ComposerPackage extends ContentEntityBase implements ComposerPackageInterface {

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
    $fields['version'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Version'))
      ->setDescription(t('Composer Package Version'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 12,
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
// No Bundles
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setReadOnly(TRUE);
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('Create Date'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -18,
      ])
      ->setDisplayConfigurable('view', TRUE);
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('Change Date'));

    // You can add additional fields here

    return $fields;
  }
  
  /**
   * Composer Data of this Package
   * @return array
   */
  public function getComposer(): array
  {
    if ($this->get('package_composer')->isEmpty()) {
      return [];
    }
    else {
      $json = $this->get('package_composer')->first()->get('value')->getValue();
      return Json::decode($json);
    }
  }

}
