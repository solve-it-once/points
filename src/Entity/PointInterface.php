<?php

namespace Drupal\points\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining Point entities.
 *
 * @ingroup points
 */
interface PointInterface extends  ContentEntityInterface, EntityChangedInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Point creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Point.
   */
  public function getCreatedTime();

  /**
   * Sets the Point creation timestamp.
   *
   * @param int $timestamp
   *   The Point creation timestamp.
   *
   * @return \Drupal\points\Entity\PointInterface
   *   The called Point entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * @return double
   */
  public function getPoints();

  /**
   * @param double $points
   * @return double
   */
  public function setPoints($points);

  /**
   * @return string
   */
  public function getLog();

  /**
   * @return \Drupal\points\Entity\PointMovement
   */
  public function getPreviousMovement();

}
