<?php

namespace Honeybee\FrameworkBinding\Agavi\App\ActionPack\Resource\Embed;

use AgaviRequestDataHolder;
use Honeybee\FrameworkBinding\Agavi\App\Base\View;

class EmbedSuccessView extends View
{
    public function executeHtml(AgaviRequestDataHolder $request_data)
    {
        $this->setupHtml($request_data);

        /** @var Honeybee\Projection\ProjectionInterface **/
        $resource = $this->getAttribute('resource');
        /** @var Honeybee\Entity **/
        $embed = $this->getAttribute('embed');

        $default_settings = [];
        if ($request_data->hasParameter('input_group')) {
            $default_settings['group_parts'] = $request_data->getParameter('input_group');
        }
        $renderer_settings = $this->getResourceRendererSettings($default_settings);

        $rendered_embed = $this->renderSubject($embed, $renderer_settings);
        $this->setAttribute('rendered_embed', $rendered_embed);
    }

    protected function getResourceRendererSettings($default_settings = [])
    {
        return array_replace_recursive($default_settings, []);
    }
}
