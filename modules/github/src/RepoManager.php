<?php

namespace Drupal\packages_github;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Component\Serialization\Json;
use Drupal\packages\ComposerPackageInterface;

/**
 * Github Repository Manager
 *
 * @author Attila Németh
 * 08.12.2020
 */
class RepoManager {
  
  // Packages Service
  private     $_packagesService;

  // Root API URL
  private     $_apiRoot             = 'https://api.github.com';

  // Repository Name
  private     $_repository;
  
  // Repository Name (without vendor)
  private     $_name;
  
  // API URL for Releases
  private     $_releasesUrl;

  // Temporary release directory
  private     $_tempDir;
  
  // Release Info
  private     $_release;
  
  public function __construct(string $repository) {
    $this->_repository = $repository;
    $this->_packagesService = \Drupal::service('packages.manager');
  }
  
  /**
   * Pull the last release to a temporary folder
   */
  public function pullLast(): ComposerPackageInterface
  {
    $this->_setReleasesUrl();
    $this->_pullLastRelease();
    $this->_packagesService->applyVersion($this->_release['tag_name']);
    $handle = opendir($this->_tempDir);
    while (false !== ($entry = readdir($handle))) {
      if (!preg_match('/^\./', $entry) && is_dir($this->_tempDir. '/' . $entry)) {
        rename($this->_tempDir. '/' . $entry, $this->_tempDir. '/' . $this->_name);
      }
    }
    $this->_packagesService->createZipFile();
    $values = [
      'version' => $this->_release['tag_name'],
      'package_description' => $this->_release['name'],
      'package_composer' => $this->_packagesService->getComposer(),
    ];
    $package = \Drupal::entityTypeManager()->getStorage('composer_package')->create($values);
    $package->save();
    return $package;
  }
  
  /**
   * API Releases URL
   */
  private function _setReleasesUrl(): void
  {
    $response = \Drupal::httpClient()->get($this->_apiRoot . '/repos/' . $this->_repository);
    $data = Json::decode((string)$response->getBody());
    $this->_name = $data['name'];
    $this->_releasesUrl = preg_replace('/\{\/id\}$/', '', $data['releases_url']);
  }
  
  private function _pullLastRelease(): void
  {
    $response = \Drupal::httpClient()->get($this->_releasesUrl);
    $data = Json::decode((string)$response->getBody());
    if (is_array($data) && count($data)) {
      $this->_release = $data[0];
      $this->_setTempDir();
      $zipResponse = \Drupal::httpClient()->get($this->_release['zipball_url']);
      $zipContent = (string)$zipResponse->getBody();
      file_put_contents($this->_tempDir . '/github.zip', $zipContent);
      $zip = new \ZipArchive;
      $zip->open($this->_getRealTempDir() . '/github.zip');
      $zip->extractTo($this->_getRealTempDir());
      $zip->close();
      unlink($this->_getRealTempDir() . '/github.zip');
    }
    else {
      throw new \Exception('No available releases');
    }
  }
  
  /**
   * Set Temporary Directory
   */
  private function _setTempDir(): void
  {
    $this->_tempDir = 'temporary://packages/github/' . $this->_release['id'];
    \Drupal::service('file_system')
            ->prepareDirectory($this->_tempDir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $this->_packagesService->setTempDir($this->_tempDir);
  }
  
  /**
   * Absolute Path of Temporary Directory
   * @return string
   */
  private function _getRealTempDir(): string
  {
    $dir = $this->_tempDir;
    $result = \Drupal::service('file_system')
            ->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $real = \Drupal::service('file_system')->realpath($dir);
    return $real;
  }
  
}
