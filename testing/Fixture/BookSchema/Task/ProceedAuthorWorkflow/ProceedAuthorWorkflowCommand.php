<?php

namespace Honeybee\Tests\Fixture\BookSchema\Task\ProceedAuthorWorkflow;

use Honeybee\Model\Task\ProceedWorkflow\ProceedWorkflowCommand;

class ProceedAuthorWorkflowCommand extends ProceedWorkflowCommand
{
    public function getEventClass()
    {
        return AuthorWorkflowProceededEvent::CLASS;
    }
}
