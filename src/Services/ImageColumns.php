<?php

namespace A17\Twill\Image\Services;

use Illuminate\Support\Arr;
use A17\Twill\Image\Services\Interfaces\ImageSizes;

class ImageColumns implements ImageSizes
{
    protected $config;

    protected static $configFile = 'frontend.config.json';

    protected $configBreakpoints = 'structure.breakpoints';

    protected $configMainColWidths = 'structure.container';

    protected $configInnerGutters = 'structure.gutters.inner';

    protected $configOuterGutters = 'structure.gutters.outer';

    protected $configColumns = 'structure.columns';

    protected $breakpoints;

    protected $mainColWidths;

    protected $innerGutters;

    protected $outerGutters;

    protected $columns;

    public function __construct()
    {
        $this->config = $this->getFEConfig();

        // array needs to be ordered by largest to smallest. If the config starts with the 'xs' breakpoint we assume it's backwards and reverse the array
        $this->breakpoints
            = array_key_first(Arr::get($this->config, $this->configBreakpoints)) == 'xs'
            ? array_reverse(Arr::get($this->config, $this->configBreakpoints))
            : Arr::get($this->config, $this->configBreakpoints);

        $this->mainColWidths = array_map(
            [$this, 'parseConfigToInt'],
            Arr::get($this->config, $this->configMainColWidths)
        );

        $this->innerGutters = array_map(
            [$this, 'parseConfigToInt'],
            Arr::get($this->config, $this->configInnerGutters)
        );

        $this->outerGutters = array_map(
            [$this, 'parseConfigToInt'],
            Arr::get($this->config, $this->configOuterGutters)
        );

        $this->columns = array_map(
            [$this, 'parseConfigToInt'],
            Arr::get($this->config, $this->configColumns)
        );
    }

    public function sizes($sizes = []): string
    {
        $sizes_output = '';

        foreach ($this->breakpoints as $name => $point):

        if (!empty($sizes[$name])) {
            if (strrpos($sizes[$name], 'px') > 0 || strrpos($sizes[$name], 'vw') > 0) {
                $thisSize = $sizes[$name];
            } elseif ($this->mainColWidths[$name] !== 'auto') {
                $thisSize = round(((($this->mainColWidths[$name] - ($this->columns[$name] - 1) * $this->innerGutters[$name]) / $this->columns[$name]) * $sizes[$name]) + ($sizes[$name] - 1) * $this->innerGutters[$name]).'px';
            } else {
                $thisSize = '((100vw - '.((($this->columns[$name] - 1) * $this->innerGutters[$name]) + (2 * $this->outerGutters[$name])).'px) / '.$this->columns[$name].') * '.$sizes[$name];

                if ($sizes[$name] >= 1) {
                    $thisSize = '('.$thisSize.') + '.(($sizes[$name] - 1) * $this->innerGutters[$name]).'px';
                }

                $thisSize = 'calc('.$thisSize.')';
            }
        } else {
            if ($this->mainColWidths[$name] !== 'auto') {
                $thisSize = $this->mainColWidths[$name].'px';
            } else {
                $thisSize = 'calc(100vw-'.(2 * $this->outerGutters[$name]).'px)';
            }
        }

        $sizes_output .= ($name === 'xxl' ? '' : ', ').  '(min-width: '. $point .') '. $thisSize;
        endforeach;

        return $sizes_output;
    }

    public function mediaQuery($args): string
    {
        $mq = is_array($args) ? $args[0] : $args;
        $constrain = is_array($args) ? $args[1] : 'min';
        $mq_num = ($constrain == 'max' ? intval($this->breakpoints[$mq]) - 1 : intval($this->breakpoints[$mq]));

        return '('. $constrain .'-width: '. $mq_num .'px)';
    }

    protected function getFEConfig()
    {
        $fe_config_json = file_get_contents(base_path(self::$configFile));

        return json_decode($fe_config_json, true);
    }

    protected function parseConfigToInt($val)
    {
        return $val == 'auto' ? $val : intval($val);
    }

    public static function shouldInstantiateService(): bool
    {
        return file_exists(base_path(self::$configFile));
    }
}
