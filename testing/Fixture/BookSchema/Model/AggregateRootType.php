<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model;

use Honeybee\Model\Aggregate\AggregateRootType as BaseAggregateRootType;

abstract class AggregateRootType extends BaseAggregateRootType
{
    const VENDOR = 'Honeybee-CMF';

    const PACKAGE = 'TestFixtures';

    public function getPackage()
    {
        return self::PACKAGE;
    }

    public function getVendor()
    {
        return self::VENDOR;
    }
}
