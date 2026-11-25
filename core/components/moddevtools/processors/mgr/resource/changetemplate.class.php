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
     * {@inheritDoc}
     * @return bool|string|null
     */
    public function initialize()
    {
        // Only the template of the resource may be changed by this processor
        $this->properties = array_intersect_key($this->properties, ['id' => true, 'template' => true]);

        return parent::initialize();
    }

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function afterSave()
    {
        // The resource cache has to be refreshed, otherwise the old template is still used
        $this->modx->cacheManager->refresh([
            'resource' => ['contexts' => [$this->object->get('context_key')]],
        ]);

        return parent::afterSave();
    }

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