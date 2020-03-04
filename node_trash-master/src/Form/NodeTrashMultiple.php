<?php

namespace Drupal\node_trash\Form;

use Drupal\Core\Entity\Form\DeleteMultipleForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * Provides a form for deleting a node.
 *
 * @internal
 */
class  NodeTrashMultiple extends DeleteMultipleForm {


  public function submitForm(array &$form, FormStateInterface $form_state) {

    $total_count = 0;
    $delete_entities = [];
    $delete_translations = [];
    $inaccessible_entities = [];
    $storage = $this->entityTypeManager->getStorage($this->entityTypeId);
    $trash_entities = [];
    $entities = $storage->loadMultiple(array_keys($this->selection));
    foreach ($this->selection as $id => $selected_langcodes) {
      $entity = $entities[$id];
      if (!isset($entity->trash->value)) {
        // move trash
        $trash_entities[$id] = $entity;
      }
      else {
        $delete_trash_entities[$id] = $entity;
      }
      if (!$entity->access('delete', $this->currentUser)) {
        $inaccessible_entities[] = $entity;
        continue;
      }
      foreach ($selected_langcodes as $langcode) {
        if ($entity instanceof TranslatableInterface) {
          $entity = $entity->getTranslation($langcode);

          // If the entity is the default translation then deleting it will
          // delete all the translations.
          if ($entity->isDefaultTranslation()) {
            $delete_entities[$id] = $entity;
            // If there are translations already marked for deletion then remove
            // them as they will be deleted anyway.
            unset($delete_translations[$id]);
            // Update the total count. Since a single delete will delete all
            // translations, we need to add the number of translations to the
            // count.
            $total_count += count($entity->getTranslationLanguages());
          }
          // Add the translation to the list of translations to be deleted
          // unless the default translation is being deleted.
          elseif (!isset($delete_entities[$id])) {
            $delete_translations[$id][] = $entity;
          }
        }
        elseif (!isset($delete_entities[$id])) {
          $delete_entities[$id] = $entity;
          $total_count++;
        }
      }
    }


    $delete_entities=array_intersect_key($trash_entities,$delete_entities);
    $total_count = count($delete_entities);

    if ($delete_entities) {
      $storage->delete($delete_entities);
      foreach ($delete_entities as $entity) {
        $this->logger($entity->getEntityType()->getProvider())->notice('The @entity-type %label has been deleted.', [
          '@entity-type' => $entity->getEntityType()->getLowercaseLabel(),
          '%label' => $entity->label(),
        ]);
      }
    }

    // Move to Trash
    if ($delete_trash_entities) {
      foreach ($delete_trash_entities as $trash_entity) {
        $trash_entity->set('trash', true);
        $trash_entity->save();
        $this->logger($entity->getEntityType()->getProvider())->notice('The @entity-type %label move to trash', [
          '@entity-type' => $trash_entity->getEntityType()->getLowercaseLabel(),
          '%label'       => $trash_entity->label(),
        ]);
      }
      if ($told_trash = count($delete_trash_entities)) {
        $message =  $this->formatPlural($told_trash, '@count move to trash item.', '@count move to trash items.');
        $this->messenger->addStatus($message);
      }
    }
    if ($delete_translations) {
      /** @var \Drupal\Core\Entity\TranslatableInterface[][] $delete_translations */
      foreach ($delete_translations as $id => $translations) {
        $entity = $entities[$id]->getUntranslated();
        foreach ($translations as $translation) {
          $entity->removeTranslation($translation->language()->getId());
        }
        $entity->save();
        foreach ($translations as $translation) {
          $this->logger($entity->getEntityType()->getProvider())->notice('The @entity-type %label @language translation has been deleted.', [
            '@entity-type' => $entity->getEntityType()->getLowercaseLabel(),
            '%label'       => $entity->label(),
            '@language'    => $translation->language()->getName(),
          ]);
        }
        $total_count += count($translations);
      }
    }

    if ($total_count) {
      $this->messenger->addStatus($this->getDeletedMessage($total_count));
    }
    if ($inaccessible_entities) {
      $this->messenger->addWarning($this->getInaccessibleMessage(count($inaccessible_entities)));
    }
    $this->tempStore->delete($this->currentUser->id());
    $form_state->setRedirectUrl($this->getCancelUrl());
  }



}