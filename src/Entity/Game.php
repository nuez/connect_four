<?php

namespace Drupal\connect_four\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\connect_four\GameInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Game entity.
 *
 * @ingroup connect_four
 *
 * @ContentEntityType(
 *   id = "connect_four_game",
 *   label = @Translation("Game"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\connect_four\GameListBuilder",
 *     "views_data" = "Drupal\connect_four\Entity\GameViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\connect_four\Form\GameForm",
 *       "add" = "Drupal\connect_four\Form\GameForm",
 *       "edit" = "Drupal\connect_four\Form\GameForm",
 *       "delete" = "Drupal\connect_four\Form\GameDeleteForm",
 *     },
 *     "access" = "Drupal\connect_four\GameAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\connect_four\GameHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "connect_four_game",
 *   admin_permission = "administer game entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/connect_four_game/{connect_four_game}",
 *     "add-form" = "/admin/structure/connect_four_game/add",
 *     "edit-form" = "/admin/structure/connect_four_game/{connect_four_game}/edit",
 *     "delete-form" = "/admin/structure/connect_four_game/{connect_four_game}/delete",
 *     "collection" = "/admin/structure/connect_four_game",
 *   },
 *   field_ui_base_route = "connect_four_game.settings"
 * )
 */
class Game extends ContentEntityBase implements GameInterface {
  use EntityChangedTrait;

  const STARTED = 0;

  const FINISHED = 1;
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
      ->setDescription(t('The ID of the Game entity.'))
      ->setReadOnly(TRUE);

    $fields['home'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Home Player'))
      ->setDescription(t('The user ID of Home Player.'))
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

    $fields['away'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Away Player'))
      ->setDescription(t('The user ID of Home Player.'))
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

    $fields['status'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Game status'))
      ->setSetting('allowed_values', [
        self::STARTED => t('Started'),
        self::FINISHED => t('Finished'),
      ])
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE);

    $fields['winner'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Winner'))
      ->setDescription(t('Reference to winning user entity'))
      ->setSetting('target_type', 'user');

    $fields['config'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Configuration'))
      ->setDescription(t('Serialized configuration for the tournament'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));


    return $fields;
  }
}
