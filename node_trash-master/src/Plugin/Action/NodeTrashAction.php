<?php

namespace Drupal\node_trash\Plugin\Action;
use Drupal\Core\Action\Plugin\Action\DeleteAction;

class NodeTrashAction extends DeleteAction {

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    #kint($entities);die();
    foreach ($entities as $entity) {
//      if (!isset($entity->trash->value)) {
//
//      }
      return ;
    }
  }

}