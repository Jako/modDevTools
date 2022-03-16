<?php
/**
 * Change Resource template
 */

use TreehillStudio\ModDevTools\Processors\ObjectUpdateProcessor;

/**
 * Class modDevToolsResourceChangeTemplateProcessor
 */
class modDevToolsResourceChangeTemplateProcessor extends ObjectUpdateProcessor
{
    public $classKey = 'modResource';
    public $permission = 'save_document';
    public $languageTopics = ['resource'];
    public $objectType = 'resource';

    /**
     * @return boolean
     */
    public function beforeSave()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        $template = $this->getProperty('template');
        if (empty($template)) {
            return $this->modx->lexicon('moddevtools.template_err_ns');
        }
        $template = $this->modx->getObject('modTemplate', $template);

        if (empty($template)) {
            return $this->modx->lexicon('moddevtools.template_err_nf');
        }

        return true;
    }
}

return 'modDevToolsResourceChangeTemplateProcessor';