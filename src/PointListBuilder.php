<?php

namespace Drupal\points;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines a class to build a listing of Point entities.
 *
 * @ingroup points
 */
class PointListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Point ID');
    $header['points'] = $this->t('Points');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\points\Entity\Point */
    $row['id'] = $entity->id();
    $row['points'] = $entity->getPoints();
    return $row + parent::buildRow($entity);
  }

}
