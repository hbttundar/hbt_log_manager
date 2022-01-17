<?php

namespace Drupal\hbt_log_Manager\EventSubscriber;


use Drupal\hbt_log_Manager\Logger\Logger;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;


/**
 * hbt_log_Manager event subscriber.
 */
class LogManagerSubscriber implements EventSubscriberInterface {


  protected RequestStack $request_stack;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected EventDispatcherInterface $eventDispatcher;

  /**
   * Constructs event subscriber.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   */
  public function __construct(RequestStack $request_stack, EventDispatcherInterface $event_dispatcher) {
    $this->eventDispatcher = $event_dispatcher;
    $this->request_stack = $request_stack;
    register_shutdown_function([&$this, 'persistCombinedLog']);
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('event_dispatcher'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onKernelRequest'],
      KernelEvents::TERMINATE => ['onKernelTerminate'],
    ];
  }

  /**
   * Kernel request event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Response event.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    Logger::startRequest($this->request_stack->getCurrentRequest()->getUri());
  }

  /**
   * Kernel response event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\PostResponseEvent $event
   *   Response event.
   */
  public function onKernelTerminate(PostResponseEvent $event) {
    Logger::infoEvent('End rendering this request:' . $this->request_stack->getCurrentRequest()
        ->getUri());
  }

  public function persistCombinedLog() {
    try {
      Logger::persistCombinedLog();
    } catch (Exception $e) {
      #Drupal::messenger()->addMessage(print_r($e, TRUE));
      error_log($e->getMessage());
    }

  }

}
