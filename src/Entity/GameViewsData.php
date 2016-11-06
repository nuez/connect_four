<?php

namespace Drupal\connect_four\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Game entities.
 */
class GameViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['connect_four_game']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Game'),
      'help' => $this->t('The Game ID.'),
    );

    return $data;
  }

}
