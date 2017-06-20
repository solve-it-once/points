<?php

namespace Drupal\points\Form;

use Drupal\inline_entity_form\Form\EntityInlineForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the inline form for Point Entity.
 */
class PointInlineForm extends EntityInlineForm {

  /**
   * {@inheritdoc}
   */
  public function entityForm(array $entity_form, FormStateInterface $form_state) {
    $entity_form = parent::entityForm($entity_form, $form_state);
    /** @var \Drupal\points\Entity\Point $entity */
    $entity = $entity_form['#entity'];
    $user_inputs = $form_state->getUserInput();
    if (!$user_inputs) {
      $entity_form['state'] = [
        '#type' => 'hidden',
        '#value' => $entity->getPoints()
      ];
    } else {
      $points = $user_inputs[$entity_form['#parents'][0]];
      // TODO: do we need to handle when mutiple Point entities are allowed?
      $entity->set('state', $points[0]['inline_entity_form']['state']);
    }
    return $entity_form;
  }
}