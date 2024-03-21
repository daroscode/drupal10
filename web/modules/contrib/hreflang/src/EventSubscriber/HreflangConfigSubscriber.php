<?php

namespace Drupal\hreflang\EventSubscriber;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listens to the config save event for hreflang.settings.
 */
class HreflangConfigSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The dynamic page cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|null
   */
  protected $cacheDynamicPageCache;

  /**
   * The page cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|null
   */
  protected $cachePage;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs the HreflangConfigSubscriber.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Cache\CacheBackendInterface|null $cache_dynamic_page_cache
   *   The dynamic page cache.
   * @param \Drupal\Core\Cache\CacheBackendInterface|null $cache_page
   *   The page cache.
   */
  public function __construct(EventDispatcherInterface $event_dispatcher, LoggerInterface $logger, MessengerInterface $messenger, ?CacheBackendInterface $cache_dynamic_page_cache = NULL, ?CacheBackendInterface $cache_page = NULL) {
    $this->cacheDynamicPageCache = $cache_dynamic_page_cache;
    $this->cachePage = $cache_page;
    $this->eventDispatcher = $event_dispatcher;
    $this->logger = $logger;
    $this->messenger = $messenger;
  }

  /**
   * Invalidates page caches on config change.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The ConfigCrudEvent to process.
   */
  public function onSave(ConfigCrudEvent $event): void {
    if ($event->getConfig()->getName() !== 'hreflang.settings') {
      return;
    }
    if (!$event->isChanged('x_default') && !$event->isChanged('x_default_fallback') && !$event->isChanged('defer_to_content_translation')) {
      return;
    }
    if (!$this->cacheDynamicPageCache && !$this->cachePage) {
      return;
    }
    $this->messenger->addStatus($this->t('Page caches are being cleared for new Hreflang settings to take effect.'));
    $listener = function () {
      if ($this->cacheDynamicPageCache) {
        $this->cacheDynamicPageCache->invalidateAll();
      }
      if ($this->cachePage) {
        $this->cachePage->invalidateAll();
      }
      $this->logger->notice('Page caches have been cleared for new Hreflang settings.');
    };
    $this->eventDispatcher->addListener(KernelEvents::TERMINATE, $listener, 400);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onSave'];
    return $events;
  }

}
