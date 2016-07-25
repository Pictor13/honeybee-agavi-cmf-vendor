<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\Boolean;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;

class HtmlBooleanAttributeRenderer extends HtmlAttributeRenderer
{
    protected function getDefaultTemplateIdentifier()
    {
        $view_scope = $this->getOption('view_scope', 'missing_view_scope.collection');
        if (StringToolkit::endsWith($view_scope, 'collection')) {
            return $this->output_format->getName() . '/attribute/boolean/as_itemlist_item_cell.twig';
        }

        return $this->output_format->getName() . '/attribute/boolean/as_input.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        return $params;
    }

    protected function getInputTemplateParameters()
    {
        $global_input_parameters = parent::getInputTemplateParameters();

        if (!empty($global_input_parameters['readonly'])) {
            $global_input_parameters['disabled'] = 'disabled';
        }

        return $global_input_parameters;
    }
}
