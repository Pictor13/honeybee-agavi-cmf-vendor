<?php

namespace Honeygavi\Agavi\Provisioner;

use Auryn\Injector as DiContainer;
use Psr\Log\LoggerInterface;
use Trellis\Common\Object;

abstract class AbstractProvisioner extends Object implements ProvisionerInterface
{
    protected $di_container;

    protected $logger;

    public function __construct(DiContainer $di_container, LoggerInterface $logger)
    {
        $this->di_container = $di_container;
        $this->logger = $logger;
    }
}
