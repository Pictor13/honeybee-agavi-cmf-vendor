<?php

namespace Honeygavi\Ui\OutputFormat;

interface OutputFormatServiceInterface
{
    /**
     * @param string $name identifier of output format
     *
     * @return OutputFormatInterface|null
     */
    public function getOutputFormat($name);
}
