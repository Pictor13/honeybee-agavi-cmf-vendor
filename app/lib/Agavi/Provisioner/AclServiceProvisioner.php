<?php

namespace Honeybee\FrameworkBinding\Agavi\Provisioner;

use AgaviConfig;
use AgaviConfigCache;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Security\Acl\AclServiceInterface;
use Honeybee\ServiceDefinitionInterface;

class AclServiceProvisioner extends AbstractProvisioner
{
    const ACL_CONFIG_NAME = 'access_control.xml';

    public function build(ServiceDefinitionInterface $service_definition, SettingsInterface $provisioner_settings)
    {
        $service = $service_definition->getClass();

        $state = [ ':roles_configuration' => $this->loadAclConfig() ];

        $this->di_container->define($service, $state)->share($service)->alias(AclServiceInterface::CLASS, $service);
    }

    protected function loadAclConfig()
    {
        return include AgaviConfigCache::checkConfig(
            AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR . self::ACL_CONFIG_NAME
        );
    }
}
