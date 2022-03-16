<?php
/**
 * @package moddevtools
 * @subpackage plugin
 */

namespace TreehillStudio\ModDevTools\Plugins\Events;

use modResource;
use TreehillStudio\ModDevTools\Plugins\Plugin;
use xPDO;

class OnDocFormSave extends Plugin
{
    public function process()
    {
        if ($this->moddevtools->getOption('debug')) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Start OnDocFormSave');
        }
        /** @var modResource $resource */
        $resource = $this->scriptProperties['resource'];
        $this->moddevtools->parseContent($resource);
    }
}
