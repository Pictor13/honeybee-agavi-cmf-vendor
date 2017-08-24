<?php

namespace Honeygavi\App\ActionPack\Collection;

use AgaviConfig;
use AgaviRequestDataHolder;
use Honeybee\Infrastructure\Config\Settings;
use Honeygavi\App\Base\View;
use Honeygavi\Ui\Activity\Activity;
use Honeygavi\Ui\Activity\SearchActivity;
use Honeygavi\Ui\Activity\Url as ActivityUrl;
use Honeygavi\Ui\ValueObjects\Pagination;

class CollectionSuccessView extends View
{
    public function executeHtml(AgaviRequestDataHolder $request_data)
    {
        if ($this->hasAttribute('command') && $this->getContainer()->getRequestMethod() === 'write') {
            if ($request_data->getParameter('__submit', false) === 'save_and_continue') {
                $this->getResponse()->setRedirect(
                    $this->getContext()->getRouting()->gen(null, [], [ 'relative' => false ])
                );
            } else {
                $this->getResponse()->setRedirect(
                    $this->getContext()->getRouting()->gen(
                        'module.collection',
                        [ 'module' => $this->getAttribute('resource_type'), 'sort' => 'modified_at:desc' ],
                        [ 'relative' => false ]
                    )
                );
            }
            return;
        }

        $this->setupHtml($request_data);

        $resource_type = $this->getAttribute('resource_type');
        $this->setAttribute('resource_type_name', $resource_type->getName());

        $this->setRenderedResourceCollection($request_data);
        $this->setSubheaderActivities($request_data);
        $this->setPrimaryActivities($request_data);
        $this->setSearchForm($request_data);
        $this->setSortActivities($request_data);
        $this->setPagination($request_data);
    }

    public function executeHaljson(AgaviRequestDataHolder $request_data)
    {
        $service_locator = $this->getContext()->getServiceLocator();
        $view_config_service = $service_locator->getViewConfigService();
        $activity_service = $service_locator->getActivityService();
        $url_generator = $service_locator->getUrlGenerator();
        $tm = $this->translation_manager;
        $view_scope = $this->getViewScope();

        $resource_collection = $this->getAttribute('resource_collection');
        $list_config = $request_data->getParameter('list_config');
        $resource_type = $this->getAttribute('resource_type');
        $number_of_results = $this->getAttribute('number_of_results', 0);

        $pagination = Pagination::createByOffset(
            $number_of_results,
            $list_config->getLimit(),
            $list_config->getOffset()
        );

        $view_config = $view_config_service->getViewConfig($view_scope);

        $rendered_resource_collection = $this->renderSubject($resource_collection);

        $td = $resource_type->getPrefix() . '.views';

        $json = [
            'resource' => $tm->_('collection.page_title', $td),
            'resource_type' => $resource_type->getPrefix(),
            'resource_type_name' => $tm->_($resource_type->getName(), $td),
            'current_number_of_results' => count($resource_collection),
            'total_number_of_results' => $number_of_results,
            '_embedded' => [
                $resource_type->getPrefix() => $rendered_resource_collection,
            ],
            '_links' => [
            ],
            'query' => [
                'search' => $list_config->getSearch(),
                'filter' => $list_config->getFilter(),
                'sort' => $list_config->getSort(),
                'limit' => $list_config->getLimit(),
                'offset' => $list_config->getOffset(),
            ],
            // 'pagination' => $pagination->toArray(),
        ];

        $curie = $this->getCurieName();
        $curies = $this->getCuries();

        $links = array_merge([], $curies);

        // get pagination links
        $page_links = $this->renderSubject(
            $pagination,
            [
                'curie' => $curie,
                'translation_domain' => $view_config->getSettings()->get(
                    'pagination_translation_domain',
                    'application.pagination'
                ),
            ]
        );

        // get links for sort activities
        $sort_links = $this->renderSubject(
            $activity_service->getContainer($view_scope . '.sort_activities')->getActivityMap(),
            [
                'curie' => $curie
            ],
            'sort_activities'
        );

        // get links for primary activities
        $primary_links = $this->renderSubject(
            $activity_service->getContainer($view_scope . '.primary_activities')->getActivityMap(),
            [
                'curie' => $curie
            ],
            'primary_activities'
        );

        // get links for subheader activities
        $subheader_links = $this->renderSubject(
            $activity_service->getContainer($view_scope . '.subheader_activities')->getActivityMap(),
            [
                'curie' => $curie
            ],
            'subheader_activities'
        );

        $search_activity = $activity_service->getActivity($view_scope, 'search');
        $search_link = $this->renderSubject(
            $search_activity,
            [
                'curie' => $curie,
                'search_value' => $request_data->getParameter('search'),
                'form_parameters' => [
                    'sort' => $request_data->getParameter('sort')
                ]
            ]
        );
        $search_link_name = sprintf('%s:%s~%s', $curie, $search_activity->getScope(), $search_activity->getName());
        $search_links = [];
        $search_links[$search_link_name] = $search_link;

        $json['_links'] = array_merge(
            $links,
            $page_links,
            $sort_links,
            $primary_links,
            $subheader_links,
            $search_links
        );

        return json_encode($json, self::JSON_OPTIONS);
    }

    public function executeJson(AgaviRequestDataHolder $request_data)
    {
        $resource_collection = $this->getAttribute('resource_collection');
        $list_config = $request_data->getParameter('list_config');

        return json_encode(
            [
                'collection' => $resource_collection->toArray(),
                'scrolling' => [
                    'limit' => $list_config->getLimit(),
                    'next_offset' => $list_config->getOffset(),
                    'total' => $this->getAttribute('number_of_results', 0)
                ]
            ]
        );
    }

    public function executeConsole(AgaviRequestDataHolder $request_data)
    {
        $resource_collection = $this->getAttribute('resource_collection');
        $resource_type = $this->getAttribute('resource_type');

        $resource_type_name = $resource_type->getName();

        $rendered_resource_collection = $this->renderSubject($resource_collection);
        $this->setAttribute('rendered_resource_collection', $rendered_resource_collection);

        $rendered_sort_activities = $this->setSortActivities($request_data);
        $sorting = 'Available sorting:' . PHP_EOL . PHP_EOL . $rendered_sort_activities;

        $settings = [
            'url_parameters' => []
        ];
        if ($request_data->hasParameter('sort')) {
            $settings['url_parameters']['sort'] = $request_data->getParameter('sort');
        }
        $rendered_pagination = $this->setPagination($request_data, $settings);

        $list_config = $request_data->getParameter('list_config');
        $from = $list_config->getOffset() + 1;
        $to = $from + ($resource_collection->count() - 1);

        $number_of_results = $this->getAttribute('number_of_results', 'xxx');

        $text = <<<EOT

# $resource_type_name list (item $from to $to of $number_of_results)

$sorting

$rendered_pagination

$rendered_resource_collection


EOT;

        $this->cliMessage($text);
    }

    protected function setRenderedResourceCollection(AgaviRequestDataHolder $request_data)
    {
        $resource_collection = $this->getAttribute('resource_collection');
        $this->setAttribute('rendered_resource_collection', $this->renderSubject($resource_collection));
    }

    protected function setSearchForm(AgaviRequestDataHolder $request_data)
    {
        $view_scope = $this->getViewScope();
        $view_config_service = $this->getServiceLocator()->getViewConfigService();
        $view_settings = $view_config_service->getViewConfig($view_scope)->getSettings();
        $activity_service = $this->getServiceLocator()->getActivityService();
        $search_activity = $activity_service->getActivity($view_scope, 'search');

        $rendered_search_form = $this->renderSubject(
            new SearchActivity(array_merge($search_activity->toArray(), [ 'url' => $search_activity->getUrl() ])),
            [
                'view_scope' => $view_scope,
                'search_value' => $request_data->getParameter('search'),
                'form_parameters' => [ 'sort' => $request_data->getParameter('sort') ],
                'enable_list_filters' => $view_settings->get('enable_list_filters', true),
                'defined_list_filters' => $view_settings->get('list_filters', []),
                'list_filters_values' => $request_data->getParameter('list_config')->getFilter()
            ],
            $view_config_service->getRendererConfig($view_scope, $this->getOutputFormat(), 'search_activity'),
            [ 'resource' => $this->getAttribute('resource_type')->createEntity() ]
        );

        $this->setAttribute('rendered_search_form', $rendered_search_form);
        $this->setAttribute('search_value', $request_data->getParameter('search'));
    }

    protected function setSortActivities(AgaviRequestDataHolder $request_data)
    {
        $activity_service = $this->getServiceLocator()->getActivityService();

        // get sort activities defined for current view config scope
        $sort_activities_container = $activity_service->getContainer($this->getViewScope() . '.sort_activities');
        $sort_activities = $sort_activities_container->getActivityMap();
        $current_sort_value = $request_data->getParameter('sort');

        $output_format = $this->getOutputFormat();
        $view_scope = $this->getViewScope();

        // we generate an id instead of default to a random one, as we need to render the sort
        // activities twice in the html and need unique ids there (by replacing the necessary html snippet)
        $sort_trigger_id = 'sortTrigger' . rand(1, 10000);

        $default_data = [
            'view_scope' => $view_scope,
        ];

        // get sort_activities renderer config
        $view_config_service = $this->getServiceLocator()->getViewConfigService();
        $renderer_config = $view_config_service->getRendererConfig(
            $view_scope,
            $output_format,
            'sort_activities',
            $default_data
        );

        /** which activity is the current default one?
         *
         * fallbacks order:
         *  - 'sort' url parameter
         *  - eventual renderer config setting
         *  - eventual custom activity name (from setting or validation)
         *  - first activity of the map
         */
        $default_activity_map = $sort_activities->filterByUrlParameter('sort', $current_sort_value);
        $default_activity_name = '';
        if (!$default_activity_map->isEmpty()) {
            $default_activity_name = $default_activity_map->getItem($default_activity_map->getKeys()[0])->getName();
        } else {
            // when a default_activity_name setting is present we ignore the custom 'sort' url parameter
            if ($renderer_config->has('default_activity_name')) {
                $default_activity_name = $renderer_config->get('default_activity_name');
            } elseif (empty($current_sort_value)) {
                if (!$sort_activities->isEmpty()) {
                    $default_activity_name = $sort_activities->getItem($sort_activities->getKeys()[0])->getName();
                }
            } else {
                // set the custom parameter value (when validation allows it)
                $default_activity_name = $current_sort_value;
            }
        }

        // sort_activities renderer settings
        $render_settings = [
            'trigger_id' => $sort_trigger_id,
        ];
        if (!$sort_activities->isEmpty() && !$sort_activities->hasKey($default_activity_name)) {
            // force a dropdown to display the custom (non-existent) value
            // but only allow the choice of configured activities
            $render_settings['as_dropdown'] = 'true';

            $custom_activity = new Activity([
                'name' => $default_activity_name,
                'label' => $default_activity_name.'.label',
                'url' => ActivityUrl::createUri('null'),
                'settings' => new Settings
            ]);
            $sort_activities->setItem($default_activity_name, $custom_activity);
        }

        if (!empty($default_activity_name)) {
            $render_settings['default_activity_name'] = $default_activity_name;
        }

        $renderer_service = $this->getServiceLocator()->getRendererService();
        $rendered_sort_activities = $renderer_service->renderSubject(
            $sort_activities,
            $output_format,
            $renderer_config,
            [],
            new Settings($render_settings)
        );

        $this->setAttribute('sort_trigger_id', $sort_trigger_id);
        $this->setAttribute('rendered_sort_activities', $rendered_sort_activities);

        return $rendered_sort_activities;
    }

    public function getBreadcrumbsActivities()
    {
        $collection_activity = $this->getServiceLocator()->getActivityService()->getActivity(
            $this->getAttribute('resource_type')->getPrefix(),
            'collection'
        );

        return [ $collection_activity ];
    }

    protected function getBreadcrumbsTitle()
    {
        $number_of_results = $this->getAttribute('number_of_results', 0);
        $translation_domain = $this->getTranslationDomainPrefix() . '.views';
        $default_translation = sprintf('%s', $number_of_results);

        $number_of_results = $this->getServiceLocator()->getTranslator()->translate(
            'collection.number_of_results',
            $translation_domain,
            null,
            [ $number_of_results ],
            $default_translation
        );

        return sprintf('%s (%s)', $this->getPageTitle(), $number_of_results);
    }

    protected function setPagination(AgaviRequestDataHolder $request_data, array $settings = [])
    {
        $number_of_results = $this->getAttribute('number_of_results', 0);
        $list_config = $request_data->getParameter('list_config');
        $offset = $list_config->getOffset();
        $limit_per_page = $list_config->getLimit();

        $pagination = Pagination::createByOffset($number_of_results, $limit_per_page, $offset);
        $this->setAttributes($pagination->toArray());

        $rendered_pagination = $this->renderSubject($pagination, $settings);

        $this->setAttribute('rendered_pagination', $rendered_pagination);

        return $rendered_pagination;
    }
}
