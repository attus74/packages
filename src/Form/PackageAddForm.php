<?php

namespace Drupal\packages\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\packages\ComposerProjectInterface;

/**
 * Add new package Form
 * 
 * @author Attila Németh, UBG
 * @date 02.12.2020
 */
class PackageAddForm extends FormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'packages_add_package_form';
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ComposerProjectInterface $composer_project = NULL)
  {
    
    opcache_reset();
    
    $form_state->set('project', $composer_project);
    $options = $composer_project->getNewVersionOptions();
    $options['other'] = t('Other');
    $form['version'] = [
      '#type' => 'select',
      '#title' => t('Version'),
      '#required' => TRUE,
      '#empty option' => t('Please Select'),
      '#options' => $options,
    ];
    $form['other'] = [
      '#type' => 'textfield',
      '#title' => t('Version'),
      '#states' => [
        'visible' => [
          ':input[name="version"]' => ['value' => 'other'],
        ],
        'required' => [
          ':input[name="version"]' => ['value' => 'other'],
        ],
      ],
    ];
    $validators = array(
      'file_validate_extensions' => array('zip'),
    );
    $form['zip'] = array(
      '#type' => 'managed_file',
      '#name' => 'zip',
      '#title' => t('Package'),
      '#description' => t('The Package, zipped'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://packages/' . $composer_project->id()  .'/',
      '#required' => TRUE,
    );
    $form['description'] = [
      '#type' => 'text_format',
      '#title' => t('Description'),
      '#required' => FALSE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add new package'),
    ];
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    if ($form_state->getValue('version') === 'other') {
      $version = $form_state->getValue('other');
    }
    else {
      $version = $form_state->getValue('version');
    }
    $zipFileId = $form_state->getValue(['zip', 0]);
    $zipFile = \Drupal::entityTypeManager()->getStorage('file')->load($zipFileId);
    $description = $form_state->getValue('description');
    $form_state->get('project')->addnewPackage($version, $zipFile, $description);
    $form_state->get('project')->save();
    \Drupal::messenger()->addStatus(t('The Package is added'));
    $form_state->setRedirect('entity.composer_project.canonical', [
      'composer_project' => $form_state->get('project')->id(),
    ]);
  }
  
}
