<?php

namespace Honeybee\FrameworkBinding\Agavi\App\ActionPack\Resource\Embed;

use Honeybee\FrameworkBinding\Agavi\App\Base\Action;
use AgaviRequestDataHolder;

class EmbedAction extends Action
{
    public function executeRead(AgaviRequestDataHolder $request_data)
    {
        /** @var Honeybee\Projection\ProjectionInterface **/
        $resource = $request_data->getParameter('resource');
        /** @var Trellis\Runtime\Embed\EmbedInterface **/
        $embed = $request_data->getParameter('embed');

        $this->setAttribute('resource', $resource);
        $this->setAttribute('embed', $embed);
        $this->setAttribute('view_scope', $this->getScopeKey());

        return 'Success';
    }

    public function executeWrite(AgaviRequestDataHolder $request_data)
    {
        return $this->executeRead($request_data);
    }
}
