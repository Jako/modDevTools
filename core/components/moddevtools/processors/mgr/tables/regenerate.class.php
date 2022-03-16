<?php
/**
 * Regenerate Links
 */

use TreehillStudio\ModDevTools\Processors\Processor;

/**
 * Class modDevToolsRegenerateProcessor
 */
class modDevToolsRegenerateProcessor extends Processor
{
    public $map = [
        'modChunk' => [
            'name' => 'name',
            'content' => 'snippet',
            'type' => 'chunk'
        ],
        'modTemplate' => [
            'name' => 'templatename',
            'content' => 'content',
            'type' => 'template'
        ],
        'modResource' => [
            'name' => 'pagetitle',
            'content' => 'snippet',
            'type' => 'resource'
        ]
    ];

    /**
     * {@inheritDoc}
     * @return array|string
     */
    public function process()
    {
        if (!$this->moddevtools->getOption('accessRegenerate')) {
            return $this->failure($this->modx->lexicon('permission_denied'));
        }

        $filters = $this->getProperty('filters');
        foreach ($filters as $class) {
            if (isset($this->map[$class])) {
                $objects = $this->modx->getIterator($class);
                foreach ($objects as $object) {
                    $this->modx->setLogLevel(xPDO::LOG_LEVEL_WARN); // Change log level to WARN
                    $this->moddevtools->parseContent($object);
                    $this->modx->setLogLevel(xPDO::LOG_LEVEL_INFO); // Change log level to INFO
                    $this->modx->log(xPDO::LOG_LEVEL_INFO,
                        'Regenerated links to ' . $this->map[$class]['type'] . ': ' .
                        $object->get($this->map[$class]['name']) . ' (' . $object->get('id') . ')'
                    );
                }
            }
        }

        $this->modx->log(xPDO::LOG_LEVEL_INFO, 'COMPLETED');
        return $this->success();
    }
}

return 'modDevToolsRegenerateProcessor';