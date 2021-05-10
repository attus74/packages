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
    
    opcache_reset();
    
    $elements = [];
    foreach($items as $item) {
      $json = $item->get('value')->getValue();
      $data = Json::decode($json);
      $version = [];
      if (array_key_exists('authors', $data)) {
        $version['authors'] = $this->_formatAuthors($data);
      }
      if (array_key_exists('homepage', $data)) {
        $version['links'] = $this->_formatLinks($data);
      }
      if (array_key_exists('require', $data) || array_key_exists('require-dev', $data)) {
        $version['require'] = $this->_formatRequire($data);
      }
      if (array_key_exists('extra', $data)) {
        $version['extra'] = $this->_formatExtra($data);
      }
      $elements[] = $version;
    }
    return $elements;
  }
  
  private function _formatLinks(array $data): array
  {
    
    opcache_reset();
    
    $links = [];
    if (array_key_exists('homepage', $data)) {
      $url = Url::fromUri($data['homepage'], [
        'attributes' => [
          'target' => '_blank',
        ],
      ]);
      $links[] = Link::fromTextAndUrl(t('Homepage'), $url)->toString();
      if (array_key_exists('readme', $data)) {
        $url = Url::fromUri($data['homepage'] . '/' . $data['readme'], [
          'attributes' => [
            'target' => '_blank',
          ],
        ]);
        $links[] = Link::fromTextAndUrl(t('Readme'), $url)->toString();
      }
    }
    $build = [
      '#theme' => 'item_list',
      '#title' => t('Links'),
      '#items' => $links,
      '#weight' => +99,
    ];
    return $build;
  }
  
  private function _formatExtra(array $data): array
  {
    $build = [
      '#prefix' => '<div class="composer-extra">',
      '#suffix' => '</div>',
    ];
    if (array_key_exists('ubg_stability', $data['extra'])) {
      $label = t('Stability');
      switch($data['extra']['ubg_stability']) {
        case 'stable':
          $value = t('Live');
          $color = 'green';
          break;
        case 'alpha':
          $value = t('160');
          $color = 'yellow';
          break;
        case 'dev':
          $value = t('Development');
          $color = 'blue';
          break;
        default:
          $value = $data['extra']['ubg_stability'];
          $color = 'red';
      }
      $build[] = [
        '#theme' => 'image',
        '#uri' => 'https://img.shields.io/badge/' . $label . '-' . $value . '-' . $color,
      ];
    }
    return $build;
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
        $requires[] = $name . ': ' . $version . ' (dev)';
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