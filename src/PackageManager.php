<?php

namespace Drupal\packages;

use Drupal\file\FileInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Serialization\Yaml;
use Drupal\packages\Exception\PackageZipException;

/**
 * Package Manager
 * 
 * @author Attila Németh, UBG
 * @date 02.12.2020
 */
class PackageManager {
  
  // Temporary Directory
  private     $_tempDir       = NULL;
  
  // Package Version
  private     $_version;
  
  // Content of composer.json file
  private     $_composerJson  = NULL;
  
  // ZIP File URL
  private     $_distUrl;
  
  /**
   * UNZIP uploaded Package to temporary directory
   * @param FileInterface $file
   * @throws PackageZipException
   */
  public function unzipFile(FileInterface $file): void
  {
    $zip = new \ZipArchive;
    $zipRealPath = \Drupal::service('file_system')->realpath($file->getFileUri());
    $destinationRealPath = $this->_getRealTempDir();
    $res = $zip->open($zipRealPath);
    if ($res === TRUE) {
      $zip->extractTo($destinationRealPath);
      $zip->close();
    }
    else {
      throw new PackageZipException(t('Package may not be unzipped'));
    }
    $this->_distUrl = file_create_url($file->getFileUri());
  }
  
  /**
   * Add/replace version number in composer.json and info.yml files
   * @param string $version
   */
  public function applyVersion(string $version): void
  {
    $this->_version = $version;
    $this->_applyVersionToDir($this->_getTempDir());
  }
  
  /**
   * Content of Composer.json
   * @return string
   */
  public function getComposer(): string
  {
    $composer = $this->_composerJson;
    $composer['dist'] = [
      'type' => 'zip',
      'url' => $this->_distUrl,
    ];
    return Json::encode($composer);
  }

  /**
   * Add/replace version number in composer.json and info.yml, call the same function for subdirectories
   * @param string $dir
   * @param string $version
   */
  private function _applyVersionToDir(string $dir): void
  {
    if ($handle = opendir($dir)) {
      while (false !== ($entry = readdir($handle))) {
        if ($entry === 'composer.json') {
          $this->_applyVersionToComposerJson($dir . '/composer.json');
        }
        if (preg_match('/\.info\.yml$/', $entry)) {
          $this->_applyVersionToInfoYaml($dir . '/' . $entry);
        }
        if (!preg_match('/^\./', $entry) && is_dir($dir . '/' . $entry)) {
          $this->_applyVersionToDir($dir . '/' . $entry);
        }
      }
      closedir($handle);
    }
  }
  
  /**
   * Add/Replace version in info.yml file
   * @param string $uri
   */
  private function _applyVersionToInfoYaml(string $uri): void
  {
    $yml = file_get_contents($uri);
    $content = Yaml::decode($yml);
    $content['version'] = $this->_version;
    file_put_contents($uri, Yaml::encode($content));
  }
  
  /**
   * Add/Replace version in composer.json, and read composer.json content
   * @param string $uri
   */
  private function _applyVersionToComposerJson(string $uri): void
  {
    $json = file_get_contents($uri);
    $content = Json::decode($json);
    $content['version'] = $this->_version;
    if (is_null($this->_composerJson)) {
      $this->_composerJson = $content;
    }
    file_put_contents($uri, Json::encode($content));
  }
  
  /**
   * Temporary Directory in Drupal Schema
   * @return string
   */
  private function _getTempDir(): string
  {
    if (is_null($this->_tempDir)) {
      $this->_tempDir = 'temporary://packages/' . mt_rand(1000, 9999) . '/' . time();
    }
    
    return $this->_tempDir;
  }
  
  /**
   * Absolute Path of Temporary Directory
   * @return string
   */
  private function _getRealTempDir(): string
  {
    $dir = $this->_getTempDir();
    $result = \Drupal::service('file_system')
            ->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $real = \Drupal::service('file_system')->realpath($dir);
    return $real;
  }
  
}
