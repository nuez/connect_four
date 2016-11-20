<?php

namespace Drupal\connect_four\Entity;

use Drupal\connect_four\Exception\ConnectFourException;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\connect_four\MoveInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Move entity.
 *
 * @ingroup connect_four
 *
 * @ContentEntityType(
 *   id = "connect_four_move",
 *   label = @Translation("Move"),
 *   handlers = {
 *   },
 *   base_table = "connect_four_move",
 *   admin_permission = "administer move entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uid" = "user_id",
 *   },
 * )
 */
class Move extends ContentEntityBase implements MoveInterface {

  use EntityChangedTrait;

  public static function create(array $values = array()) {

    // Don't allow creating a move outside the playing field.
    if ($values['x'] >= GAME::WIDTH ||
      $values['x'] < 0 ||
      $values['y'] >= GAME::HEIGHT ||
      $values['y'] < 0) {
      throw new ConnectFourException('The move cannot be created outside the playing board.');
    }

    // Don't allow creating a duplicate move.
    /** @var QueryFactory $queryFactory */
    $queryFactory = \Drupal::getContainer()->get('entity.query');

    $duplicate = $queryFactory->get('connect_four_move')
      ->condition('x', $values['x'])
      ->condition('y', $values['y'])
      ->condition('game', $values['game'])
      ->execute();

    if (!empty($duplicate)) {
      throw new ConnectFourException('The move cannot be created with the same 
      coordinates of an already existing move.');
    }


    return parent::create($values);

  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * @return int
   */
  public function getX() {
    return $this->get('x')->value;
  }

  /**
   * @return int
   */
  public function getY() {
    return $this->get('y')->value;
  }

  /**
   * @return Game
   */
  public function getGame() {
    return $this->get('game')->entity;
  }

  /**
   * @return bool
   */
  public function isHome() {
    return $this->getOwnerId() == $this->getGame()->getHomeUser()->id();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Move entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Player'))
      ->setDescription(t('The user ID of author of the Move entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['x'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Y Coordinate'));

    $fields['y'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Y Coordinate'));

    $fields['game'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Game reference'))
      ->setSetting('target_type', 'connect_four_game')
      ->setDescription(t('Reference to the host game.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    return $fields;
  }
}
