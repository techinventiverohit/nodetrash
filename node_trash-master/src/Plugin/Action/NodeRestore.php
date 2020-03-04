<?php

namespace Drupal\node_trash\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;

/**
 *
 * @Action(
 *   id = "node_restore_action",
 *   label = @Translation("Restore selected content"),
 *   type = "node"
 * )
 */
class NodeRestore extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['trash' => NULL];
  }


}



