<?php

namespace Honeybee\Tests\Fixture\BookSchema\Task\CreateAuthor;

use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;

class CreateAuthorCommand extends CreateAggregateRootCommand
{
    public function getEventClass()
    {
        return AuthorCreatedEvent::CLASS;
    }
}
