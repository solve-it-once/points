<?php

namespace Drupal\points\Exception;

/**
 * Exception thrown if an update of a Point Entity does not validate its state.
 * Usually, this means another thread has already updated the same entity.
 */
class PointsStaleStateException extends \Exception {}