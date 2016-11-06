<?php

/**
 * @file
 * Contains connect_four_game.page.inc.
 *
 * Page callback for Game entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Game templates.
 *
 * Default template: connect_four_game.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_connect_four_game(array &$variables) {
  // Fetch Game Entity Object.
  $connect_four_game = $variables['elements']['#connect_four_game'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
