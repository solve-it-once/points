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
 *     "canonical" = "/admin/structure/point/{point}",
 *     "add-page" = "/admin/structure/point/add",
 *     "add-form" = "/admin/structure/point/add/{point_type}",
 *     "edit-form" = "/admin/structure/point/{point}/edit",
 *     "delete-form" = "/admin/structure/point/{point}/delete",
 *     "collection" = "/admin/structure/point",
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

    $fields['points'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Points'))
      ->setDescription(t('This is a number that records points'))
      ->setSettings(array(
        'default_value' => '',
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
