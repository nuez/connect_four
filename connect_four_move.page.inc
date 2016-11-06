<?php

/**
 * @file
 * Contains move.page.inc.
 *
 * Page callback for Move entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Move templates.
 *
 * Default template: move.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_connect_four_move(array &$variables) {
  // Fetch Move Entity Object.
  $move = $variables['elements']['#move'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
