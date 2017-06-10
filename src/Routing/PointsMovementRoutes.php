<?php

namespace Drupal\points\Routing;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\Routing\Route;

/**
 * Defines dynamic routes.
 */
class PointsMovementRoutes {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routes = [];
    $config_entities = \Drupal::entityTypeManager()->getStorage('field_storage_config')->loadMultiple();
    foreach ($config_entities as $config_entity) {
      if ($config_entity->get('type') === 'entity_reference' && $config_entity->get('settings')['target_type'] === 'point') {
        $entity_type_id = $config_entity->get('entity_type');
        $routes['entity.'.$entity_type_id . '.points.movement'] = new Route(
          // Path to attach this route to:
          "/$entity_type_id/{". $entity_type_id . "}/points",
          // Route defaults:
          [
            '_controller' => '\Drupal\points\Controller\EntityPointsMovementController::page',
            '_title' => 'Points'
          ],
          // Route requirements:
          [
            '_permission'  => 'access point overview',
          ]
        );
      }
    }

    return $routes;
  }

}