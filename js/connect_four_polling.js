/**
 * @file
 * AJAX commands used by Editor module.
 */

(function ($, Drupal, drupalSettings) {
  setInterval(function () {
    Drupal.ajax({url: 'connect-four-polling/' + drupalSettings.turn}).execute();
  }, 1000);
})(jQuery, Drupal, drupalSettings);
