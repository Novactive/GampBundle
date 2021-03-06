<?php

namespace FourLabs\GampBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

class AnalyticsFactory
{
    /**
     * @param RequestStack $requestStack
     * @param int          $version
     * @param string       $trackingId
     * @param bool         $ssl
     * @param bool         $anonymize
     * @param bool         $async
     * @param bool         $debug
     * @param bool         $sandbox
     *
     * @return Analytics
     */
    public function createAnalytics(RequestStack $requestStack, $version, $trackingId, $ssl, $anonymize, $async, $debug, $sandbox)
    {
        $analytics = new Analytics($ssl, $sandbox);

        $analytics
            ->setProtocolVersion($version)
            ->setTrackingId($trackingId)
            ->setAnonymizeIp($anonymize)
            ->setAsyncRequest($async && !$debug)
            ->setDebug($debug)
        ;

        if (($request = $requestStack->getCurrentRequest())) {
            $userAgent = $request->headers->has('User-Agent') ? $request->headers->get('User-Agent') : '';
            $analytics
                ->setIpOverride($request->getClientIp())
                ->setUserAgentOverride($userAgent)
            ;

            // set clientId from ga cookie if exists, otherwise this must be set at a later point
            if ($request->cookies->has('_ga')) {
                $cookie = $this->parseCookie($request->cookies->get('_ga'));
                $analytics->setClientId(array_pop($cookie));
            }
        }

        return $analytics;
    }

    /**
     * Parse the GA Cookie and return data as an array.
     *
     * @param $cookie
     *
     * @return array(version, domainDepth, cid)
     *                        Example of GA cookie: _ga:GA1.2.492973748.1449824416
     */
    public function parseCookie($cookie)
    {
        return explode('.', substr($cookie, 2), 3);
    }
}
