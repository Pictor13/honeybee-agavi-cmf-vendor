<?php

namespace Honeybee\FrameworkBinding\Agavi\App\ActionPack\Create;

use AgaviRequestDataHolder;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Agavi\App\Base\View;
use Honeybee\Ui\Renderer\Html\Attribute\ValueRenderer;
use Honeybee\Ui\Renderer\RendererFactory;

class CreateInputView extends View
{
    public function executeHtml(AgaviRequestDataHolder $request_data)
    {
        $this->setupHtml($request_data);

        $resource = $this->getAttribute('resource');

        $view_config_scope = $this->getAttribute('view_scope');
        $entity_short_prefix = StringToolkit::asSnakeCase($resource->getType()->getName());
        $data_ns = sprintf('create_%s', $entity_short_prefix);
        $renderer_settings = [ 'group_parts' => [ $data_ns ] ];
        $rendered_resource = $this->renderSubject($resource, $renderer_settings);
        $this->setAttribute('rendered_resource', $rendered_resource);

        $this->setPrimaryActivities($request_data);

        $this->setAttribute('create_url', $this->routing->gen(null));

        if ($template = $this->getCustomTemplate($resource)) {
            $this->getLayer('content')->setTemplate($template);
        }
    }

    public function executeJson(AgaviRequestDataHolder $request_data)
    {
        return json_encode(__METHOD__);
    }

    protected function setPrimaryActivities(AgaviRequestDataHolder $request_data)
    {
        $activity_service = $this->getServiceLocator()->getActivityService();

        $primary_activities_container = $activity_service->getContainer($this->getViewScope() . '.primary_activities');
        $primary_activities = $primary_activities_container->getActivityMap();

        $rendered_primary_activities = $this->renderSubject(
            $primary_activities,
            [],
            'primary_activities'
        );

        $this->setAttribute('rendered_primary_activities', $rendered_primary_activities);

        return $rendered_primary_activities;
    }
}
