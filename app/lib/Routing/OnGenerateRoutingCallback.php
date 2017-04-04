<?php

namespace Honeygavi\Routing;

use AgaviExecutionContainer;
use AgaviRoutingCallback;

/**
 * Lets you specify route/query parameters to add or remove on route generation.
 * Supported parameters:
 * - "remove": names of query params to remove when generating the route
 * - "add": name/value pairs for query params to add to the route
 */
class OnGenerateRoutingCallback extends AgaviRoutingCallback
{
    /**
     * Gets executed when the route of this callback matched.
     *
     * @param array The parameters generated by this route.
     * @param AgaviExecutionContainer The original execution container.
     *
     * @return bool false as routes with this callback should never match.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onMatched(array &$parameters, AgaviExecutionContainer $container)
    {
        return true;
    }

    /**
     * Gets executed when the route of this callback did not match.
     *
     * @param AgaviExecutionContainer The original execution container.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onNotMatched(AgaviExecutionContainer $container)
    {
        return;
    }

    /**
     * Gets executed when the route of this callback is about to be reverse
     * generated into an URL.
     *
     * @param array The default parameters stored in the route.
     * @param array The parameters the user supplied to AgaviRouting::gen().
     * @param array The options the user supplied to AgaviRouting::gen().
     *
     * @return bool false as this route part should not be generated.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onGenerate(array $default_parameters, array &$user_parameters, array &$user_options)
    {
        /*
         * Please note, that Agavi 1.0.7 etc. don't call routing callbacks when using "$ro->gen(null)" without
         * specifying parameters. To mitigate this provide routing parameters or use this instead (trololo):
         *
         * $ro->gen(implode('+', array_reverse($rq->getAttribute('matched_routes', 'org.agavi.routing'))) or
         * $ro->gen(end($rq->getAttribute('matched_routes', 'org.agavi.routing')));
         *
         * @see AgaviWebRouting::gen() line 289
         */

        foreach ($this->getParameter('remove', []) as $param) {
            $user_parameters[$param] = null;
        }

        foreach ($this->getParameter('add', []) as $param => $value) {
            $user_parameters[$param] = $value;
        }

        return true;
    }
}