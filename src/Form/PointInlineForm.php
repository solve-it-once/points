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
    $points_inputs = NULL;
    if (array_key_exists($entity_form['#parents'][0], $user_inputs)) {
      $field_name = array_slice($entity_form['#parents'], -3, 1)[0];
      if ($field_name == $entity_form['#parents'][0]) {
        $points_inputs = $user_inputs[$entity_form['#parents'][0]];
      }
      else {
        $points_inputs = $this->searchPointsEntity($field_name, $user_inputs[$entity_form['#parents'][0]]);
      }
    }
    // Check if user has submit a point entity data
    if (!$points_inputs) {
      $entity_form['state'] = [
        '#type' => 'hidden',
        '#value' => $entity->getPoints(),
      ];
      // TODO: do we need to handle when mutiple Point entities are allowed?
      $entity->set('state', $entity->getPoints());
    }
    else {
      // TODO: do we need to handle when mutiple Point entities are allowed?
      $entity->set('state', $points_inputs[0]['inline_entity_form']['state']);
    }
    return $entity_form;
  }

  /**
   * Search Point entity data in nested IEF.
   *
   * @param string $needle
   *   Point field name.
   * @param array $haystack
   *   Form user input values.
   *
   * @return mixed|null
   *   Point entity data.
   */
  public function searchPointsEntity($needle, array $haystack) {
    $entity_found = NULL;
    foreach ($haystack as $key => $value) {
      if ($key === $needle) {
        $entity_found = $value;
      }
      if (is_array($value)) {
        $this->searchPointsEntity($needle, $value);
      }
    }
    return $entity_found;
  }

}
