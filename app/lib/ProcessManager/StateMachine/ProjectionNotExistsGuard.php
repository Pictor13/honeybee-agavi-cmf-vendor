<?php

namespace Honeygavi\ProcessManager\StateMachine;

use Workflux\StatefulSubjectInterface;

class ProjectionNotExistsGuard extends ProjectionExistsGuard
{
    public function accept(StatefulSubjectInterface $process_state)
    {
        return !parent::accept($process_state);
    }
}
