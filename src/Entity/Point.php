<?php

namespace Drupal\points\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Point entity.
 *
 * @ingroup points
 *
 * @ContentEntityType(
 *   id = "point",
 *   label = @Translation("Point"),
 *   bundle_label = @Translation("Point type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\points\PointListBuilder",
 *     "views_data" = "Drupal\points\Entity\PointViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\points\Form\PointForm",
 *       "add" = "Drupal\points\Form\PointForm",
 *       "edit" = "Drupal\points\Form\PointForm",
 *       "delete" = "Drupal\points\Form\PointDeleteForm",
 *     },
 *     "inline_form" = "Drupal\inline_entity_form\Form\EntityInlineForm",
 *     "access" = "Drupal\points\PointAccessControlHandler",
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "point",
 *   admin_permission = "administer point entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/points/{point}",
 *     "add-page" = "/admin/structure/points/add",
 *     "add-form" = "/admin/structure/points/add/{point_type}",
 *     "edit-form" = "/admin/structure/points/{point}/edit",
 *     "delete-form" = "/admin/structure/points/{point}/delete",
 *     "collection" = "/admin/structure/points/overview",
 *   },
 *   bundle_entity_type = "point_type",
 *   field_ui_base_route = "entity.point_type.edit_form",
 *   constraints = {
 *     "PointState" = {}
 *   }
 * )
 */
class Point extends ContentEntityBase implements PointInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function getPoints() {
    return $this->get('points')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPoints($points) {
    $this->set('points', $points);
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
  public function getLog() {
    return $this->get('log')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousMovement() {
    return $this->get('mid')->entity;
  }

  private function setPreviousMovement($mid) {
    $this->set('mid', $mid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['log'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Log'))
      ->setDescription(t('The description of the pending movement.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 50,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE)
      ->setCustomStorage(TRUE);

    // The movement reference, populated by Point::preSave().
    $fields['mid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Previous movement'))
      ->setDescription(t('The latest movement of this point instance.'))
      ->setSetting('target_type', 'point_movement')
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    if ($this->isNew()) {
      $original_point = 0;
    } else {
      $original_point = $this->original->get('points')->value;
    }

    $new_point = $this->get('points')->value;
    if ($this->isNew() || $original_point != $new_point) {
      $delta = $new_point - $original_point;
      $mid = $this->createTransaction($this->id(), $delta,0, $this->getLog());
      $this->setPreviousMovement($mid);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    $movement = $this->getPreviousMovement();
    if (!$movement->getPointId()) {
      $movement->setPointId($this->id());
      $movement->save();
    }
  }

  private function createTransaction($point_id, $points, $uid = 0, $des) {
    if (!$uid) {
      $uid = \Drupal::currentUser()->id();
    }

    $movement = $this->entityTypeManager()
      ->getStorage('point_movement')
      ->create(
        [
          'point_id' => $point_id,
          'points' => $points,
          'uid' => $uid,
          'description' => $des,
        ]
      );

    $movement->save();
    return $movement->id();
  }
}

