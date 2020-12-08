<?php

namespace Drupal\packages\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;

/**
 * Packages Controller
 * 
 * @author Attila Németh, UBG
 * @date 02.12.2020
 */
class Packages extends ControllerBase {
  
  /**
   * Packages.json
   */
  public function json(): JsonResponse
  {
    $packages = [];
    $projects = \Drupal::entityTypeManager()->getStorage('composer_project')->loadMultiple();
    foreach($projects as $project) {
      foreach($project->get('project_packages') as $item) {
        $package = $item->entity;
        $composer = $package->get('package_composer')->first()->get('value')->getValue();
        $info = Json::decode($composer);
        if (!array_key_exists($info['name'], $packages)) {
          $packages[$info['name']] = [];
        }
        $packages[$info['name']][$package->uuid()] = $info;
      }
    }
    return new JsonResponse([
      'packages' => $packages,
    ]);
  }
  
}
