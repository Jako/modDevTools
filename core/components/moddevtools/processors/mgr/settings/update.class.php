<?php
/**
 * Update a system setting
 *
 * @package moddevtools
 * @subpackage processors
 */

// Compatibility between 2.x/3.x
if (file_exists(MODX_PROCESSORS_PATH . 'system/settings/update.class.php')) {
    require_once MODX_PROCESSORS_PATH . 'system/settings/update.class.php';
} elseif (!class_exists('modSystemSettingsUpdateProcessor')) {
    class_alias(\MODX\Revolution\Processors\System\Settings\Update::class, \modSystemSettingsUpdateProcessor::class);
}

/**
 * Class modDevToolsSystemSettingsUpdateProcessor
 */
class modDevToolsSystemSettingsUpdateProcessor extends modSystemSettingsUpdateProcessor
{
    public $checkSavePermission = false;
    public $languageTopics = ['setting', 'namespace', 'moddevtools:default'];

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function checkPermissions()
    {
        return !empty($this->permission) ? $this->modx->hasPermission($this->permission) || $this->modx->hasPermission('moddevtools_' . $this->permission) : true;
    }

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function beforeSave()
    {
        $this->setProperty('namespace', 'moddevtools');
        $this->checkForBooleanValue();
        $this->checkCanSave();
        return parent::beforeSave();
    }

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function afterSave()
    {
        $this->updateTranslations($this->getProperties());
        $this->clearCache();
        return parent::afterSave();
    }

    /**
     * Verify the namespace passed is a valid namespace
     */
    protected function checkCanSave()
    {
        $key = $this->getProperty('key');
        if (strpos($key, 'moddevtools.') !== 0) {
            $this->addFieldError('key', $this->modx->lexicon('moddevtools.systemsetting_err_key_nv'));
        }
        if (!$this->modx->hasPermission($this->permission) && !$this->modx->hasPermission('moddevtools_' . $this->permission)) {
            $this->addFieldError('usergroup', $this->modx->lexicon('moddevtools.systemsetting_err_usergroup_nv'));
        }
    }
}

return 'modDevToolsSystemSettingsUpdateProcessor';
