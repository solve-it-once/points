<?php

namespace Drupal\points\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProvider;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for all entity bundles.
 */
class PointsLocalTask extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * Route provider object.
   *
   * @var \Drupal\Core\Routing\RouteProvider
   */
  protected $routProvider;

  /**
   * Creates an PointsLocalTask object.
   *
   * @param \Drupal\Core\Routing\RouteProvider $route_provider
   *   The route provider services.
   */
  public function __construct(RouteProvider $route_provider) {
    $this->routProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $derivatives = [];
    $config_entities = \Drupal::entityTypeManager()->getStorage('field_storage_config')->loadMultiple();
    foreach ($config_entities as $config_entity) {
      if ($config_entity->get('type') === 'entity_reference' && $config_entity->get('settings')['target_type'] === 'point') {
        $entity_type_id = $config_entity->get('entity_type');
        $derivatives[$entity_type_id . '.points'] = [
          'route_name' => "entity.$entity_type_id.points.movement",
          'title' => $this->t('Points'),
          'base_route' => 'entity.' . $entity_type_id . '.canonical',
          'weight' => 50,
        ];
      }
    }

    foreach ($derivatives as &$entry) {
      $entry += $base_plugin_definition;
    }
    return $derivatives;
  }
}