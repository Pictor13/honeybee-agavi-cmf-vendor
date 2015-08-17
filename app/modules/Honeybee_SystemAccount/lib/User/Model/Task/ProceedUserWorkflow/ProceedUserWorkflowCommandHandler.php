<?php

namespace Honeybee\SystemAccount\User\Model\Task\ProceedUserWorkflow;

use Honeybee\SystemAccount\User\Model\Aggregate\UserType;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Model\Task\ProceedWorkflow\ProceedWorkflowCommandHandler;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\Filesystem\FilesystemServiceInterface;
use Psr\Log\LoggerInterface;

class ProceedUserWorkflowCommandHandler extends ProceedWorkflowCommandHandler
{
    public function __construct(
        UserType $user_type,
        DataAccessServiceInterface $data_access_service,
        FilesystemServiceInterface $filesystem_service,
        EventBusInterface $event_bus,
        LoggerInterface $logger
    ) {
        parent::__construct($user_type, $data_access_service, $filesystem_service, $event_bus, $logger);
    }
}
