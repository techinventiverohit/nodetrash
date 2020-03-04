<?php

/**
 * @file
 * Contains \Drupal\fds_nroll_application_manager\EventSubscriber\ApplicationRedirectSubscriber
 */

namespace Drupal\node_trash\EventSubscriber;

use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;


class NodeTrashEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // This announces which events you want to subscribe to.
    // We only need the request event for this example.  Pass
    // this an array of method names

    $events[KernelEvents::REQUEST][] = ['redirectDeleteNodePage'];
    return $events;
  }

  /**
   * Redirect requests for my_content_type node detail pages to node/123.
   *
   * @param GetResponseEvent $event
   * @return void
   */
  public function redirectDeleteNodePage(GetResponseEvent $event) {
    $request = $event->getRequest();
    if ($request->attributes->get('_route') !== 'entity.node.delete_form') {
      return $event;
    }

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['_route']) == 'xmlhttprequest')) {
      return $event;
    }

    $node = $request->attributes->get('node');
    if(empty($node)) {
      return $event;
    }

    if (($node->trash->value) && $node->trash->value == True) {
      return $event;
    }

    $nid = $node->id();
    $redirect_url = Url::fromRoute('node_trash.form', ['node' => $nid]);
    $response = new RedirectResponse($redirect_url->toString());
    $event->setResponse($response);
    return $event;

  }




}
