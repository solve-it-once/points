<?php

namespace Drupal\points\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the point_movement entity class.
 *
 * @ContentEntityType(
 *   id = "point_movement",
 *   label = @Translation("Point movement"),
 *   handlers = {
 *     "views_data" = "Drupal\points\PointMovementViewsData",
 *     "list_builder" = "Drupal\points\PointMovementListBuilder",
 *   },
 *   admin_permission = "administer point entities",
 *   fieldable = TRUE,
 *   base_table = "point_movement",
 *   entity_keys = {
 *     "id" = "mid",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class PointMovement extends ContentEntityBase implements PointMovementInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['point_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Point ID'))
      ->setDescription(t('The id of a point entity.'))
      ->setSetting('unsigned', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['points'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Points Number'))
      ->setDescription(t('This is a number that records points of this movement.'))
      ->setSetting('unsigned', FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // todo: point type

    $fields['uid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user id of a user who is responsible for this change.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the movement happened.'));

    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Description'))
      ->setDescription(t('The description of this movement.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }
}
