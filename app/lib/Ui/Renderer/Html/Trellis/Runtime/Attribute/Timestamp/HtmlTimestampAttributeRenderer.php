<?php

namespace Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\Timestamp;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\Ui\Renderer\Html\Trellis\Runtime\Attribute\HtmlAttributeRenderer;
use Trellis\Runtime\Attribute\Timestamp\TimestampAttribute;

class HtmlTimestampAttributeRenderer extends HtmlAttributeRenderer
{
    protected function getDefaultTemplateIdentifier()
    {
        $view_scope = $this->getOption('view_scope', 'missing_view_scope.collection');
        if (StringToolkit::endsWith($view_scope, 'collection')) {
            return $this->output_format->getName() . '/attribute/datetime/as_itemlist_item_cell.twig';
        }

        return $this->output_format->getName() . '/attribute/datetime/as_input.twig';
    }

    protected function getTemplateParameters()
    {
        $params = parent::getTemplateParameters();

        $params['min_value'] = $this->getOption(
            'min_value',
            $this->attribute->getOption(TimestampAttribute::OPTION_MIN_TIMESTAMP)
        );

        $params['max_value'] = $this->getOption(
            'max_value',
            $this->attribute->getOption(TimestampAttribute::OPTION_MAX_TIMESTAMP)
        );

        return $params;
    }

    protected function getWidgetImplementor()
    {
        return $this->getOption('widget_implementor', 'jsb_Honeybee_Core/ui/DatePicker');
    }

    protected function getDefaultTranslationKeys()
    {
        return array_replace(parent::getDefaultTranslationKeys(), [ 'pattern' ]);
    }
}
