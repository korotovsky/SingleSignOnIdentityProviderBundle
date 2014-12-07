<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\EventListener;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class TargetPathSubscriber
 * @package Krtv\Bundle\SingleSignOnIdentityProviderBundle\EventListener
 */
class TargetPathSubscriber implements EventSubscriberInterface
{
    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 10), // 10 is before Firewall listener.
        );
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $service = $request->get(ServiceManager::SERVICE_PARAM);
        if (!$service) {
            return;
        }

        // Get current session service
        $sessionService = $this->serviceManager->getSessionService();

        // Set request service anyway
        $this->serviceManager->setRequestService($service);

        // If session service already exists, that means logout process already
        // started from SSO service
        if ($sessionService && $request->attributes->get('_route') === 'sso_logout_path') {
            return;
        }

        // Compare with previous session service value if exists
        // And set if current session service is different.
        if (!$sessionService || $sessionService !== $service) {
            $this->serviceManager->setSessionService($service);
        }
    }
}

