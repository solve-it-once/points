<?php

namespace Drupal\points\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

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
 *   field_ui_base_route = "entity.point_type.edit_form"
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::postSave($storage);

    if ($this->isNew()) {
      $original_point = 0;
      $this->isNew = TRUE;
    }
    else {
      $original_point = $this->original->get('points')->value;
    }

    $new_point = $this->get('points')->value;
    if ($original_point != $new_point) {
      $this->point_delta = $new_point - $original_point;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if (isset($this->point_delta)) {
      $query = \Drupal::entityQuery('point');
      $result = $query->condition('id', $this->id())->execute();
      if ($result) {
        $points = $this->point_delta;
        $this->createTransaction($this->id(), $points,0, NULL);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createTransaction($point_id, $points, $uid = 0, $des) {
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
  }

}

