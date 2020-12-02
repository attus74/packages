<?php

namespace Drupal\packages\Form;

use Drupal\Core\Form\FormBase;

/**
 * @file Settings
 *
 * @author Attila Németh
 * @date 02.12.2020
 */
class ComposerPackageSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'composer_package_settings_form';
  }

  /**
  * {@inheritdoc}
  *
  * If you have custom settings you can build a form here.
  */
  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form = [
      '#markup' => t('Fields can be configured here'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    return parent::submitForm($form, $form_state);
  }

}