<?php
/**
 * @package moddevtools
 * @subpackage plugin
 */

namespace TreehillStudio\ModDevTools\Plugins\Events;

use modResource;
use TreehillStudio\ModDevTools\Plugins\Plugin;
use xPDO;

class OnChunkFormSave extends Plugin
{
    public function process()
    {
        if ($this->moddevtools->getOption('debug')) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Start OnChunkFormSave');
        }
        /** @var modResource $resource */
        $chunk = $this->scriptProperties['chunk'];
        $this->moddevtools->parseContent($chunk);
    }
}
