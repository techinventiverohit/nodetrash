<?php

namespace Drupal\node_trash\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
/**
 * Defines a confirmation form to confirm deletion of something by id.
 */
class NodeTrashRestoreConfirm extends ConfirmFormBase {



  /**
   * entity of the item to delete.
   *
   * @var int
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node=null) {
    $this->entity = $node;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo: Do the deletion.
    $node_object = $this->entity;
    $node_object->set('trash', '0');
    $node_object->save();

    $form_state->setRedirectUrl($node_object->toUrl('canonical'));
    $message =  $this->t('The @type %title has been restore.', [
      '@type' => $this->entity->bundle(),
      '%title' => $this->entity->label(),
    ]);
    \Drupal::messenger()->addMessage($message, 'status');


  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()  {
    return "confirm_soft_restore_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.node.canonical', ['node' => $this->entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to restore this content %title?', ['%title' => $this->entity->getTitle()]);
  }

}