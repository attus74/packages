<?php

namespace Drupal\packages\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\HtmlCommand;

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
  
  /**
   * Home
   */
  public function home(): array
  {
    $build = [
      'search' => [
        '#prefix' => '<div id="packages-home-search-wrapper">',
        '#suffix' => '</div>',
        '#type' => 'textfield',
        '#placeholder' => t('Search'),
        '#attributes' => [
          'id' => 'packages-home-search',
        ],
        '#attached' => [
          'library' => 'packages/search',
        ],
      ],
      'list' => [
        '#prefix' => '<div id="packages-home-list">',
        '#suffix' => '</div>',
      ],
    ];
    return $build;
  }
  
  /**
   * Search
   * @param string $search
   * @return AjaxResponse
   */
  public function search(string $search): AjaxResponse
  {
    $response = new AjaxResponse();
    $wrapperSelector = '#packages-home-search-wrapper';
    $listSelector = '#packages-home-list';
    if (mb_strlen($search) <= 2) {
      $command = new InvokeCommand($wrapperSelector, 'removeClass', ['active']);
      $response->addCommand($command);
      $command = new HtmlCommand($listSelector, '');
      $response->addCommand($command);
    }
    else {
      $command = new InvokeCommand($wrapperSelector, 'addClass', ['active']);
      $response->addCommand($command);
      $build = [];
      $viewBuilder = \Drupal::entityTypeManager()->getViewBuilder('composer_project');
      foreach($this->_getProjects($search) as $project) {
        $view = $viewBuilder->view($project, 'teaser');
        $build[] = $view;
      }
      $command = new HtmlCommand($listSelector, $build);
      $response->addCommand($command);
    }
    return $response;
  }
  
  /**
   * Projects that match the search string
   * @param string $searchString
   * @return array
   */
  private function _getProjects(string $searchString): array
  {
    $result = [];
    $projects = \Drupal::entityTypeManager()->getStorage('composer_project')->loadMultiple();
    foreach($projects as $project) {
      if (preg_match('/' . mb_strtolower($searchString) . '/', mb_strtolower($project->label()))) {
        $result[] = $project;
      }
      else {
        foreach($project->get('project_packages') as $item) {
          $package = $item->entity;
          $composer = $package->getComposer();
          if (preg_match('/' . mb_strtolower($searchString) . '/', $composer['name'])) {
            $result[] = $project;
            break;
          } 
        }
      }
    }
    return array_slice($result, 0, 12);
  }
  
}
