<?php

namespace Drupal\points\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PointState constraint.
 */
class PointStateConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (isset($entity)) {
      /** @var \Drupal\points\Entity\PointInterface $entity */
      if (!$entity->isNew()) {
        $last_movement = $entity->getPreviousMovement();
        $query = \Drupal::entityQuery('point_movement')
          ->condition('point_id', $entity->id())
          ->sort('changed', 'DESC')
          ->range(0, 1);
        $result = $query->execute();
        if (!$last_movement || $last_movement->id() != array_pop($result)) {
          $this->context->addViolation($constraint->message);
        }
      }
    }
  }
}
