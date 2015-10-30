<?php

namespace Krtv\Bundle\SingleSignOnIdentityProviderBundle\EventListener;

use Krtv\Bundle\SingleSignOnIdentityProviderBundle\Manager\ServiceManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\UriSigner;

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
     * @var UriSigner
     */
    private $uriSigner;

    /**
     * @param ServiceManager $serviceManager
     * @param UriSigner $uriSigner
     */
    public function __construct(ServiceManager $serviceManager, UriSigner $uriSigner)
    {
        $this->serviceManager = $serviceManager;
        $this->uriSigner = $uriSigner;
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

        $service = $this->serviceManager->getRequestService();
        $serviceExtra = $this->serviceManager->getRequestServiceExtra();
        $targetPath = $this->serviceManager->getRequestTargetPath();

        if (!$service) {
            return;
        }

        // Get current session service
        $sessionService = $this->serviceManager->getSessionService();
        if ($sessionService === false) {
            return; // logout process dispatched from IdP
        }

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

        // Check for _target_path parameter and check its signature otherwise set default url
        if ($service && $targetPath) {
            $serviceManager = $this->serviceManager->getServiceManager($service);

            if (!$this->uriSigner->check($targetPath)) {
                $targetPath = $serviceManager->getServiceIndexUrl();
            }

            $this->serviceManager->setSessionTargetPath($targetPath);
        }

        // If query has both service and extra parameters, then we have to check the signature
        // and store extra data to the session, otherwise throw BadHttpRequestException()
        if ($service && $serviceExtra) {
            if (!$this->uriSigner->check($request->getSchemeAndHttpHost() . $request->getRequestUri())) {
                throw new BadRequestHttpException('Invalid signature for signing extra data from service provider.');
            }

            $this->serviceManager->setSessionExtra($serviceExtra);
        }
    }
}
