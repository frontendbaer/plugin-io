<?php

namespace IO\Services;

use IO\Helper\RuntimeTracker;
use Plenty\Plugin\ConfigRepository;

class TemplateConfigService
{
    use RuntimeTracker;

    private $configRepository;
    private $templatePluginName;
    
    public function __construct(ConfigRepository $configRepository)
    {
        $this->start("constructor");
        $this->configRepository = $configRepository;
        $this->templatePluginName = $this->configRepository->get('IO.template.template_plugin_name');
        $this->track("constructor");
    }

    public function get($key, $default = null)
    {
        $this->start("get");
        $result = null;
        if(strlen($this->templatePluginName))
        {
            $result = $this->configRepository->get($this->templatePluginName.'.'.$key, $default);
        }

        $this->track("get");
        return $result;
    }
}