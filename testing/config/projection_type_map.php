<?php

use Honeybee\Projection\ProjectionTypeMap;
use Workflux\Builder\XmlStateMachineBuilder;

$module_dir = AgaviConfig::get('core.module_dir');
$testing_dir = AgaviConfig::get('core.testing_dir');

$projection_types = [];

$projection_types['honeybee.system_account.user::projection.standard'] = new Honeybee\SystemAccount\User\Projection\Standard\UserType(
    (new XmlStateMachineBuilder([
        'name' => 'honeybee_system_account_user_workflow_default',
        'state_machine_definition' => $module_dir . '/Honeybee_SystemAccount/config/User/workflows.xml'
    ]))->build()
);
$projection_types['honeybee-cmf.test_fixtures.author::projection.standard'] = new Honeybee\Tests\Fixture\BookSchema\Projection\Author\AuthorType(
    (new XmlStateMachineBuilder([
        'name' => 'author_workflow_default',
        'state_machine_definition' => $testing_dir . '/Fixture/BookSchema/Model/workflows.xml'
    ]))->build()
);
$projection_types['honeybee-cmf.test_fixtures.book::projection.standard'] = new Honeybee\Tests\Fixture\BookSchema\Projection\Book\BookType(
    (new XmlStateMachineBuilder([
        'name' => 'author_workflow_default',
        'state_machine_definition' => $testing_dir . '/Fixture/BookSchema/Model/workflows.xml'
    ]))->build()
);
$projection_types['honeybee-cmf.test_fixtures.publication::projection.standard'] = new Honeybee\Tests\Fixture\BookSchema\Projection\Publication\PublicationType(
    (new XmlStateMachineBuilder([
        'name' => 'author_workflow_default',
        'state_machine_definition' => $testing_dir . '/Fixture/BookSchema/Model/workflows.xml'
    ]))->build()
);
$projection_types['honeybee-cmf.test_fixtures.publisher::projection.standard'] = new Honeybee\Tests\Fixture\BookSchema\Projection\Publisher\PublisherType(
    (new XmlStateMachineBuilder([
        'name' => 'author_workflow_default',
        'state_machine_definition' => $testing_dir . '/Fixture/BookSchema/Model/workflows.xml'
    ]))->build()
);

return new ProjectionTypeMap($projection_types);
