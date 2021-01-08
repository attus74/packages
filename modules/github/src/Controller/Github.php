<?php

namespace Drupal\packages_github\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\packages\ComposerProjectInterface;
use Drupal\packages_github\RepoManager;

/**
 * Github
 *
 * @author Attila Németh
 * 08.12.2020
 */
class Github extends ControllerBase {
  
  /**
   * Pull the latest release and create a package of that
   * @param ComposerProjectInterface $composer_project
   * @return RedirectResponse
   */
  public function pull(ComposerProjectInterface $composer_project): RedirectResponse
  {
    $repository = $composer_project->get('project_github')->first()->get('value')->getValue();
    $manager = new RepoManager($repository);
    $package = $manager->pullLast();
    $packages = [];
    foreach($composer_project->get('project_packages') as $item) {
      $packages[] = [
        'entity' => $item->entity,
      ];
    }
    $packages[] = [
      'entity' => $package,
    ];
    $composer_project->set('project_packages', $packages);
    $composer_project->save();
    return $this->redirect('entity.composer_project.canonical', [
      'composer_project' => $composer_project->id(),
    ]);
  }
  
  /**
   * Access Control
   * @param ComposerProjectInterface $composer_project
   * @return AccessResultInterface
   */
  public function pullAccess(ComposerProjectInterface $composer_project): AccessResultInterface
  {
    if ($composer_project->access('edit') && !$composer_project->get('project_github')->isEmpty()) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }
  
}
