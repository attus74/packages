<?php

namespace Drupal\packages\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;

/**
 * Composer.json Field Formatter
 * 
 * @author Attila Németh
 * @date 16.12.2020        
 * 
 * @FieldFormatter(
 *  id = "packages_composer_formatter",
 *  label = @Translation("Composer.json Formatter"),
 *  field_types = {
 *    "string_long",
 *  },
 * )
 */
class ComposerFormatter extends FormatterBase {
 
  /**
   * {@inheritdoc}
   */
  public function settingsSummary()
  {
    return [t('Composer.json Data')];
  }
  
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode)
  {
    $elements = [];
    foreach($items as $item) {
      $json = $item->get('value')->getValue();
      $data = Json::decode($json);
      $version = [];
      if (array_key_exists('authors', $data)) {
        $version['authors'] = $this->_formatAuthors($data);
      }
      if (array_key_exists('require', $data) || array_key_exists('require-dev', $data)) {
        $version['require'] = $this->_formatRequire($data);
      }
      $elements[] = $version;
    }
    return $elements;
  }
  
  private function _formatRequire($data): array
  {
    $requires = [];
    if (array_key_exists('require', $data)) {
      foreach($data['require'] as $name => $version) {
        $requires[] = $name . ': ' . $version;
      }
    }
    if (array_key_exists('require-dev', $data)) {
      foreach($data['require-dev'] as $name => $version) {
        $requires[] = $name . ': ' . $versio . ' (dev)';
      }
    }
    $build = [
      '#theme' => 'item_list',
      '#title' => t('Requires'),
      '#items' => $requires,
    ];
    return $build;
  }
  
  private function _formatAuthors($data): array
  {
    $authors = [];
    foreach($data['authors'] as $author) {
      $label = '';
      if (array_key_exists('name', $author)) {
        $label .= $author['name'];
      }
      if (array_key_exists('email', $author)) {
        $url = Url::fromUri('mailto:' . $author['email']);
        $link = Link::fromTextAndUrl($author['email'], $url)->toString();
        $label .= ' &lt;' . (string)$link . '&gt;';
      }
      if (array_key_exists('role', $author)) {
        $label .= ' (' . t($author['role']) . ')';
      }
      $authors[] = Markup::create($label);
    }
    $build = [
      '#theme' => 'item_list',
      '#title' => t('Authors'),
      '#items' => $authors,
    ];
    return $build;
  }
    
}