<?php

namespace Drupal\points\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\EntityOwnerInterface;

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
   * @return \Drupal\points\Entity\PointInterface
   */
  public function setPoints($points);

  /**
   * @return string
   */
  public function getLog();

  /**
   * @param string $log
   * @return \Drupal\points\Entity\PointInterface
   */
  public function setLog($log);

  /**
   * @return double
   */
  public function getState();

}
