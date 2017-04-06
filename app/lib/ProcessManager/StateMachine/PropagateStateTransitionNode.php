<?php

namespace Honeygavi\ProcessManager\StateMachine;

use Honeybee\Common\Error\RuntimeError;
use Honeygavi\ProcessManager\ProcessStateInterface;
use Honeybee\Infrastructure\Command\CommandList;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Workflux\State\State;
use Workflux\StatefulSubjectInterface;

class PropagateStateTransitionNode extends State
{
    protected $transition_map;

    protected $aggregate_root_type_map;

    // @codingStandardsIgnoreStart
    public function __construct(
        $name,
        $type = self::TYPE_ACTIVE,
        array $options = [],
        AggregateRootTypeMap $aggregate_root_type_map
    ) {
        // @codingStandardsIgnoreEnd
        parent::__construct($name, $type, $options);
        $this->transition_map = $this->options->get('transition_map');
        $this->aggregate_root_type_map = $aggregate_root_type_map;
    }

    public function onEntry(StatefulSubjectInterface $process_state)
    {
        if (!$process_state instanceof ProcessStateInterface) {
            throw new RuntimeError('Only ' . ProcessStateInterface::CLASS . ' subjects supported by ' . static::CLASS);
        }

        parent::onEntry($process_state);

        $commands = $this->createCommands($process_state);
        $execution_context = $process_state->getExecutionContext();
        $execution_context->setParameter('commands', new CommandList($commands));
    }

    protected function createCommands(ProcessStateInterface $process_state)
    {
        $commands = [];
        $payload = $process_state->getPayload();
        foreach ($payload['affected_entities'] as $affected_entity) {
            $origin_state = $payload['origin_state'];
            $type_prefix = $affected_entity['type_prefix'];
            // @todo handling for transition based on type as well as state
            $transitions = $this->transition_map->get($type_prefix);
            if (isset($transitions[$origin_state])) {
                $command_class = $transitions['command'];
                $aggregate_root_type = $this->aggregate_root_type_map->getItem($type_prefix);
                $commands[] = new $command_class(
                    [
                        'aggregate_root_type' => get_class($aggregate_root_type),
                        'aggregate_root_identifier' => $affected_entity['identifier'],
                        'known_revision' => $affected_entity['revision'],
                        'current_state_name' => $affected_entity['state'],
                        'event_name' => $transitions[$origin_state]
                    ]
                );
            }
        }
        return $commands;
    }

    protected function needs($option_key)
    {
        if (!$this->options->has($option_key)) {
            throw new RuntimeError(sprintf('Missing required option "%s"', $option_key));
        }

        return $this;
    }
}
