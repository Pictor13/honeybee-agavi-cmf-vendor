<?php

use Honeybee\FrameworkBinding\Agavi\App\Base\View;

class Honeybee_Core_Util_CompileJs_CompileJsErrorView extends View
{
    public function executeConsole(AgaviRequestDataHolder $request_data)
    {
        if ($request_data->getParameter('silent', false)) {
            return $this->cliError('');
        }

        if ($this->hasAttribute('error')) {
            return $this->cliError('Error: ' . $this->getAttribute('error') . PHP_EOL);
        }

        $error_message = 'Compilation of items had errors.' . PHP_EOL . PHP_EOL;
        $validation_errors = $this->getErrorMessages();
        if (!empty($validation_errors)) {
            $error_message .= implode(PHP_EOL, $validation_errors) . PHP_EOL;
        }

        if (!$this->hasAttribute('report')) {
            return $this->cliError($error_message);
        }

        $report = $this->getAttribute('report');
        $error_message .= 'Report for each item:' . PHP_EOL . PHP_EOL;
        foreach ($report as $directory => $data) {
            $error_message .= $data['name'] . ': ' . ($data['success'] ? 'OK' : 'FAILED');
            if (!$data['success'] && $request_data->getParameter('verbose', true)) {
                $error_message .= PHP_EOL . 'Command: ' . $data['cmd'] . PHP_EOL;
                if (!empty($data['stdout'])) {
                    $error_message .= 'STDOUT: ' . PHP_EOL . $data['stdout'];
                }
                if (!empty($data['stderr'])) {
                    $error_message .= 'STDERR: ' . PHP_EOL . $data['stderr'];
                }
                if (empty($data['stdout']) && empty($data['stderr'])) {
                    $error_message .= PHP_EOL;
                }
            }
            $error_message .= PHP_EOL . PHP_EOL;
        }

        return $this->cliError($error_message);
    }
}
