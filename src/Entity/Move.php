<?php

namespace Drupal\connect_four\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
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
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\connect_four\MoveListBuilder",
 *     "views_data" = "Drupal\connect_four\Entity\MoveViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\connect_four\Form\MoveForm",
 *       "add" = "Drupal\connect_four\Form\MoveForm",
 *       "edit" = "Drupal\connect_four\Form\MoveForm",
 *       "delete" = "Drupal\connect_four\Form\MoveDeleteForm",
 *     },
 *     "access" = "Drupal\connect_four\MoveAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\connect_four\MoveHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "connect_four_move",
 *   admin_permission = "administer move entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uid" = "user_id",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/move/{move}",
 *     "add-form" = "/admin/structure/move/add",
 *     "edit-form" = "/admin/structure/move/{move}/edit",
 *     "delete-form" = "/admin/structure/move/{move}/delete",
 *     "collection" = "/admin/structure/move",
 *   },
 *   field_ui_base_route = "move.settings"
 * )
 */
class Move extends ContentEntityBase implements MoveInterface {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
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
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
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
