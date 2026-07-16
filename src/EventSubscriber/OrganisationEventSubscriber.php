<?php

namespace Drupal\os2forms_nemlogin_openid_connect\EventSubscriber;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\os2forms_nemlogin_openid_connect\Helper\WebformHelper;
use Drupal\os2forms_organisation\Event\OrganisationUserIdEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Organisation user id event subscriber.
 */
class OrganisationEventSubscriber implements EventSubscriberInterface {

  /**
   * The webform helper.
   *
   * @var \Drupal\os2forms_nemlogin_openid_connect\Helper\WebformHelper
   */
  protected WebformHelper $webformHelper;

  /**
   * The constructor.
   */
  public function __construct(WebformHelper $webformHelper) {
    $this->webformHelper = $webformHelper;
  }

  /**
   * Subscribed events.
   */
  public static function getSubscribedEvents(): array {
    return [
      // If class does not exist this event subscriber is not initialized,
      // meaning this will cause no error.
      // @see https://www.php.net/manual/en/language.namespaces.importing.php#121045
      // This is done to avoid a hard dependency on os2forms_organsation.
      // Set priority high (100) to get this running early.
      /* @phpstan-ignore-next-line */
      OrganisationUserIdEvent::class => ['setOrganisationUserId', 100],
    ];
  }

  /**
   * Attempts settings organisation user id.
   *
   * @phpstan-ignore-next-line
   */
  public function setOrganisationUserId(OrganisationUserIdEvent $event): void {
    // Check if id has already been set.
    /* @phpstan-ignore-next-line */
    if (!empty($event->getUserId())) {
      return;
    }

    try {
      // Use the plugin configured on the current webform.
      $plugin = $this->webformHelper->getAuthProviderPlugin();

      if ($plugin->isAuthenticated()) {
        $userId = $plugin->fetchValue('nameidentifier');
        if (!empty($userId)) {
          /* @phpstan-ignore-next-line */
          $event->setUserId($userId);
        }
      }
    }
    catch (PluginException $exception) {
      return;
    }
  }

}
