<?php

namespace Drupal\node_trash\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Change path '/user/login' to '/login'.
    if ($route = $collection->get('entity.node.delete_multiple_form')) {
      $routing_form = ['_form' => 'Drupal\node_trash\Form\NodeTrashMultiple', 'entity_type_id' => 'node'];
      $route->setDefaults($routing_form);
    }
  }

}