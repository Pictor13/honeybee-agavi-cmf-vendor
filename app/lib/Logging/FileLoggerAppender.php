<?php

namespace Honeygavi\Logging;

use AgaviContext;
use AgaviFileLoggerAppender;

class FileLoggerAppender extends AgaviFileLoggerAppender
{
    const USE_APPENDER_NAME_AS_DESTINATION = 'USE_APPENDER_NAME_AS_DESTINATION';

    public function initialize(AgaviContext $context, array $parameters = array())
    {
        if (!isset($parameters['destination']) && !isset($parameters['file'])) {
            $parameters['destination'] = self::USE_APPENDER_NAME_AS_DESTINATION;
        }

        parent::initialize($context, $parameters);
    }
}
