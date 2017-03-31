<?php

namespace Honeygavi\App\ActionPack\Files\Download;

use Honeygavi\App\Base\Action;
use AgaviRequestDataHolder;

class DownloadAction extends Action
{
    public function executeRead(AgaviRequestDataHolder $request_data)
    {
        $this->setAttribute('aggregate_root_type', $this->getAggregateRootType());

        return 'Success';
    }
}
