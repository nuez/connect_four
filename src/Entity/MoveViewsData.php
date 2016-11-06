<?php

namespace Drupal\connect_four\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Move entities.
 */
class MoveViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['move']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Move'),
      'help' => $this->t('The Move ID.'),
    );

    return $data;
  }

}
