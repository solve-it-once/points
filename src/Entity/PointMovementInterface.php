<?php

namespace Drupal\points\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

interface PointMovementInterface extends  ContentEntityInterface, EntityChangedInterface {
  /**
   * @param int $point_id
   * @return \Drupal\points\Entity\PointMovement
   */
  public function setPointId($point_id);

  /**
   * @return int
   */
  public function getPointId();
}
