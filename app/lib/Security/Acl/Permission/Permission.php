<?php

namespace Honeygavi\Security\Acl\Permission;

use Trellis\Common\Object;

class Permission extends Object implements PermissionInterface
{
    const ALLOW = 'allow';

    const DENY = 'deny';

    const GENERIC = 'generic';

    protected $name;

    protected $access_scope;

    protected $access_type = self::GENERIC;

    protected $type;

    protected $expression;

    public function getName()
    {
        return $this->name;
    }

    public function getAccessScope()
    {
        return $this->access_scope;
    }

    public function getAccessType()
    {
        return $this->access_type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    public function __toString()
    {
        $name = $this->getName();

        $expr = $this->getExpression();
        if (!empty($expr)) {
            $name .= ' if ' . $expr;
        }

        $str = sprintf(
            "[%s][%s] %s on [%s]",
            $this->getAccessType(),
            $this->getType(),
            $name,
            $this->getAccessScope()
        );

        return $str;
    }
}
