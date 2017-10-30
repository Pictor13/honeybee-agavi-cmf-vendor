<?php

namespace Honeygavi;

use AgaviConfig;
use AgaviConfigCache;
use AgaviContext;
use Auryn\Injector as DiContainer;
use Closure;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceLocatorInterface;
use Honeybee\ServiceProvisionerInterface;
use Honeygavi\Provisioner\AclServiceProvisioner;
use Honeygavi\Provisioner\ActivityServiceProvisioner;
use Honeygavi\Provisioner\AuthenticationServiceProvisioner;
use Honeygavi\Provisioner\CommandBusProvisioner;
use Honeygavi\Provisioner\CommandEnricherProvisioner;
use Honeygavi\Provisioner\ConnectorServiceProvisioner;
use Honeygavi\Provisioner\DataAccessServiceProvisioner;
use Honeygavi\Provisioner\DefaultProvisioner;
use Honeygavi\Provisioner\EnvironmentProvisioner;
use Honeygavi\Provisioner\EventBusProvisioner;
use Honeygavi\Provisioner\ExpressionServiceProvisioner;
use Honeygavi\Provisioner\FilesystemServiceProvisioner;
use Honeygavi\Provisioner\FixtureServiceProvisioner;
use Honeygavi\Provisioner\JobServiceProvisioner;
use Honeygavi\Provisioner\LoggerProvisioner;
use Honeygavi\Provisioner\MailServiceProvisioner;
use Honeygavi\Provisioner\MigrationServiceProvisioner;
use Honeygavi\Provisioner\NavigationServiceProvisioner;
use Honeygavi\Provisioner\OutputFormatServiceProvisioner;
use Honeygavi\Provisioner\PermissionServiceProvisioner;
use Honeygavi\Provisioner\ProcessMapProvisioner;
use Honeygavi\Provisioner\ProvisionerInterface;
use Honeygavi\Provisioner\RendererServiceProvisioner;
use Honeygavi\Provisioner\ServiceLocatorProvisioner;
use Honeygavi\Provisioner\StateMachineBuilderProvisioner;
use Honeygavi\Provisioner\TaskServiceProvisioner;
use Honeygavi\Provisioner\TemplateRendererProvisioner;
use Honeygavi\Provisioner\TranslatorProvisioner;
use Honeygavi\Provisioner\UrlGeneratorProvisioner;
use Honeygavi\Provisioner\ViewConfigServiceProvisioner;
use Honeygavi\Provisioner\ViewTemplateServiceProvisioner;
use Honeygavi\Provisioner\WorkflowActivityServiceProvisioner;
use Honeygavi\Provisioner\WorkflowServiceProvisioner;

class ServiceProvisioner implements ServiceProvisionerInterface
{
    const SERVICES_CONFIG_NAME = 'services.xml';

    const AGGREGATE_ROOT_TYPE_MAP_CONFIG_NAME = 'aggregate_root_type_map.php';

    const PROJECTION_TYPE_MAP_CONFIG_NAME = 'projection_type_map.php';

    protected static $default_provisioner_class = DefaultProvisioner::CLASS;

    protected static $default_provisioners = [
        'honeybee.infrastructure.logger' => LoggerProvisioner::CLASS, // logger must be first
        'honeybee.ui.translator' => TranslatorProvisioner::CLASS,
        'honeybee.ui.url_generator' => UrlGeneratorProvisioner::CLASS,
        'honeybee.environment' => EnvironmentProvisioner::CLASS,
        'honeybee.infrastructure.acl_service' => AclServiceProvisioner::CLASS,
        'honeybee.infrastructure.auth_service' => AuthenticationServiceProvisioner::CLASS,
        'honeybee.infrastructure.command_enricher' => CommandEnricherProvisioner::CLASS,
        'honeybee.infrastructure.connector_service' => ConnectorServiceProvisioner::CLASS,
        'honeybee.infrastructure.data_access_service' => DataAccessServiceProvisioner::CLASS,
        'honeybee.infrastructure.expression_service' => ExpressionServiceProvisioner::CLASS,
        'honeybee.infrastructure.job_service' => JobServiceProvisioner::CLASS,
        'honeybee.infrastructure.mail_service' => MailServiceProvisioner::CLASS,
        'honeybee.infrastructure.migration_service' => MigrationServiceProvisioner::CLASS,
        'honeybee.infrastructure.fixture_service' => FixtureServiceProvisioner::CLASS,
        'honeybee.infrastructure.permission_service' => PermissionServiceProvisioner::CLASS,
        'honeybee.infrastructure.template_renderer' => TemplateRendererProvisioner::CLASS,
        'honeybee.infrastructure.filesystem_service' => FilesystemServiceProvisioner::CLASS,
        'honeybee.infrastructure.command_bus' => CommandBusProvisioner::CLASS,
        'honeybee.infrastructure.event_bus' => EventBusProvisioner::CLASS,
        'honeybee.infrastructure.process_map' => ProcessMapProvisioner::CLASS,
        'honeybee.infrastructure.state_machine_builder' => StateMachineBuilderProvisioner::CLASS,
        'honeybee.infrastructure.workflow_service' => WorkflowServiceProvisioner::CLASS,
        'honeybee.model.task_service' => TaskServiceProvisioner::CLASS,
        'honeybee.ui.activity_service' => ActivityServiceProvisioner::CLASS,
        'honeybee.ui.navigation_service' => NavigationServiceProvisioner::CLASS,
        'honeybee.ui.output_format_service' => OutputFormatServiceProvisioner::CLASS,
        'honeybee.ui.renderer_service' => RendererServiceProvisioner::CLASS,
        'honeybee.ui.view_config_service' => ViewConfigServiceProvisioner::CLASS,
        'honeybee.ui.view_template_service' => ViewTemplateServiceProvisioner::CLASS,
        'honeybee.ui.workflow_activity_service' => WorkflowActivityServiceProvisioner::CLASS,
        'honeybee.service_locator' => ServiceLocatorProvisioner::CLASS,
    ];

    protected $di_container;

    protected $service_map;

    protected $aggregate_root_type_map;

    protected $projection_type_map;

    protected $provisioned_services;

    public function __construct(DiContainer $di_container)
    {
        $this->di_container = $di_container;

        $this->service_map = include AgaviConfigCache::checkConfig(
            AgaviConfig::get('core.config_dir') . DIRECTORY_SEPARATOR . self::SERVICES_CONFIG_NAME,
            AgaviContext::getInstance()->getName()
        );
        $this->aggregate_root_type_map = include implode(
            DIRECTORY_SEPARATOR,
            [ AgaviConfig::get('core.config_dir'), 'includes', self::AGGREGATE_ROOT_TYPE_MAP_CONFIG_NAME ]
        );
        $this->projection_type_map = include implode(
            DIRECTORY_SEPARATOR,
            [ AgaviConfig::get('core.config_dir'), 'includes', self::PROJECTION_TYPE_MAP_CONFIG_NAME ]
        );

        $this->di_container->share($this->service_map);
        $this->di_container->share($this->aggregate_root_type_map);
        $this->di_container->share($this->projection_type_map);

        $this->provisioned_services = [];
    }

    public function provision()
    {
        $di_container = $this->di_container;

        foreach (self::$default_provisioners as $service_key => $default_provisioner) {
            $this->provisionService(
                $service_key,
                function (ServiceDefinitionInterface $service_definition) use ($di_container, $default_provisioner) {
                    $di_container->make($default_provisioner)->build($service_definition, new Settings([]));
                }
            );
        }

        foreach ($this->aggregate_root_type_map as $aggregate_root_type) {
            $this->di_container->share($aggregate_root_type);
        }
        foreach ($this->projection_type_map as $projection_type) {
            $this->di_container->share($projection_type);
        }

        $this->provisionServices();

        return $this->createServiceLocator();
    }

    protected function provisionServices()
    {
        $di_container = $this->di_container;

        $remaining_services = array_diff($this->service_map->getKeys(), $this->provisioned_services);

        $default_provisioner = static::$default_provisioner_class;

        foreach ($remaining_services as $service_key) {
            $service_definition = $this->service_map->getItem($service_key);
            $this->provisionService(
                $service_key,
                function (ServiceDefinitionInterface $service_definition) use ($di_container, $default_provisioner) {
                    $di_container->make($default_provisioner)->build($service_definition, new Settings([]));
                }
            );
        }
    }

    protected function provisionService(
        $service_alias,
        Closure $default_provisioning,
        array $settings = []
    ) {
        if (!$this->service_map->hasKey($service_alias)) {
            throw new RuntimeError(
                sprintf("Couldn't find service for key: %s. Maybe a typo within the services.xml?", $service_alias)
            );
        }

        $service_definition = $this->service_map->getItem($service_alias);

        if ($service_definition->hasProvisioner()) {
            $this->runServiceProvisioner($service_definition, $settings);
        } else {
            $default_provisioning($service_definition);
        }

        $this->provisioned_services[] = $service_alias;
    }

    protected function runServiceProvisioner(
        ServiceDefinitionInterface $service_definition,
        array $settings
    ) {
        $provisioner_config = $service_definition->getProvisioner();

        if (!$provisioner_config) {
            throw new RuntimeError(
                'Missing provisioner meta-data (at least "class" plus optional a "method" and some "settings").'
            );
        }

        if (!class_exists($provisioner_config['class'])) {
            throw new RuntimeError('Unable to load provisioner class: ' . $provisioner_config['class']);
        }

        $provisioner = $this->di_container->make($provisioner_config['class']);
        $provisioner_method = $provisioner_config['method'];
        $provisioner_callable = [ $provisioner, $provisioner_method ];

        if (isset($provisioner_config['settings']) && is_array($provisioner_config['settings'])) {
            $settings = array_merge($provisioner_config['settings'], $settings);
        }
        $provisioner_settings = new Settings($settings);

        if (!empty($provisioner_method) && is_callable($provisioner_callable)) {
            $provisioner->$provisioner_method($service_definition, $provisioner_settings);
        } elseif ($provisioner instanceof ProvisionerInterface) {
            $provisioner->build($service_definition, $provisioner_settings);
        } else {
            throw new RuntimeError(
                sprintf(
                    "Provisioner needs <method> configuration or must implement %s",
                    ProvisionerInterface::CLASS
                )
            );
        }
    }

    protected function createServiceLocator()
    {
        $service_definition = $this->service_map->getItem('honeybee.service_locator');
        $service = $service_definition->getClass();

        $this->di_container->share($service)->alias(ServiceLocatorInterface::CLASS, $service);

        return $this->di_container->make($service);
    }
}
