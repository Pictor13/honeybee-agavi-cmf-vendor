<?php

namespace Honeygavi\App\ActionPack\Resource;

use Honeygavi\App\Base\View;
use AgaviRequestDataHolder;

class ResourceSuccessView extends View
{
    public function executeHtml(AgaviRequestDataHolder $request_data)
    {
        $this->setupHtml($request_data);

        $resource = $this->getAttribute('resource');

        $renderer_settings = $this->getResourceRendererSettings();
        $rendered_resource = $this->renderSubject($resource, $renderer_settings);

        $routing = $this->getContext()->getRouting();
        $head_revision = $request_data->getParameter('head_revision');
        $prev_link = false;

        $activity_service = $this->getServiceLocator()->getActivityService();
        $activity_container = $activity_service->getContainer($this->getViewScope());
        $activity_map = $activity_container->getActivityMap();

        if ($resource->getRevision() > 1) {
            if ($activity_map->hasKey('prev_revision')) {
                $prev_link = $this->renderSubject(
                    $activity_map->getItem('prev_revision'),
                    [
                        'additional_url_parameters' => [
                            'resource' => $resource,
                            'revision' => $resource->getRevision() - 1
                        ]
                    ]
                );
            }
        }
        $next_link = false;
        if ($resource->getRevision() < $head_revision) {
            if ($activity_map->hasKey('next_revision')) {
                $prev_link = $this->renderSubject(
                    $activity_map->getItem('next_revision'),
                    [
                        'additional_url_parameters' => [
                            'resource' => $resource,
                            'revision' => $resource->getRevision() + 1
                        ]
                    ]
                );
            }
        }

        $this->setSubheaderActivities($request_data);

        $this->setAttribute('rendered_resource', $rendered_resource);
        $this->setAttribute('prev_link', $prev_link);
        $this->setAttribute('next_link', $next_link);
    }

    public function executeHaljson(AgaviRequestDataHolder $request_data)
    {
        $activity_service = $this->getServiceLocator()->getActivityService();

        $resource = $this->getAttribute('resource');

        $head_revision = $request_data->getParameter('head_revision');

        $curie = $this->getCurieName();
        $curies = $this->getCuries();

        $links = array_merge([], $curies);

        $activity_container = $activity_service->getContainer('default_resource_activities');
        $activity_map = $activity_container->getActivityMap();

        if ($resource->getRevision() > 1) {
            if ($activity_map->hasKey('prev_revision')) {
                $activity = $activity_service->getActivity('default_resource_activities', 'prev_revision');
                $links[$curie . ':default_resource_activities~prev_revision'] = $this->renderSubject(
                    $activity,
                    [
                        'curie' => $curie,
                        'additional_url_parameters' => [
                            'resource' => $resource,
                            'revision' => $resource->getRevision() - 1
                        ]
                    ]
                );
            }
        }

        if ($resource->getRevision() < $head_revision) {
            if ($activity_map->hasKey('next_revision')) {
                $activity = $activity_service->getActivity('default_resource_activities', 'next_revision');
                $links[$curie . ':default_resource_activities~next_revision'] = $this->renderSubject(
                    $activity,
                    [
                        'curie' => $curie,
                        'additional_url_parameters' => [
                            'resource' => $resource,
                            'revision' => $resource->getRevision() + 1
                        ]
                    ]
                );
            }
        }

        $rendered_resource = $this->renderSubject($resource, $this->getResourceRendererSettings());

        $json = array_replace_recursive([], $rendered_resource, [ '_links' => $links ]);

        return json_encode($json, self::JSON_OPTIONS);
    }

    public function executeJson(AgaviRequestDataHolder $request_data)
    {
        $resource = $this->getAttribute('resource');

        $this->getResponse()->setContent(json_encode($resource->toArray()));
    }

    public function executeConsole(AgaviRequestDataHolder $request_data)
    {
        echo print_r($this->getAttribute('resource')->toArray(), true) . PHP_EOL;
    }

    protected function getResourceRendererSettings($default_settings = [])
    {
        return array_replace_recursive([], $default_settings);
    }

    public function getBreadcrumbsActivities()
    {
        $breadcrumbs_root_activities = $this->getBreadcrumbsRootActivities();

        return $breadcrumbs_root_activities;
    }

    public function getBreadcrumbsRootActivities()
    {
        $resource_type = $this->getAttribute('resource')->getType();

        return [
            $this->getServiceLocator()->getActivityService()->getActivity($resource_type->getPrefix(), 'collection')
        ];
    }
}
