<?php
/**
 * @package moddevtools
 * @subpackage plugin
 */

namespace TreehillStudio\ModDevTools\Plugins\Events;

use modResource;
use TreehillStudio\ModDevTools\Plugins\Plugin;
use xPDO;

class OnTempFormSave extends Plugin
{
    public function process()
    {
        if ($this->moddevtools->getOption('debug')) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Start OnTempFormSave');
        }
        /** @var modResource $resource */
        $template = $this->scriptProperties['template'];
        $this->moddevtools->parseContent($template);
    }
}
