<?php
/**
 * Update System Setting
 *
 * @package moddevtools
 * @subpackage processors
 */

require_once dirname(__FILE__) . '/update.class.php';

/**
 * Class modDevToolsSystemSettingsUpdateFromGridProcessor
 */
class modDevToolsSystemSettingsUpdateFromGridProcessor extends modDevToolsSystemSettingsUpdateProcessor
{
    /**
     * {@inheritDoc}
     * @return bool|string|null
     */
    public function initialize() {
        $data = $this->getProperty('data');
        if (empty($data)) return $this->modx->lexicon('invalid_data');
        $properties = json_decode($data, true);
        $this->setProperties($properties);
        $this->unsetProperty('data');

        return parent::initialize();
    }
}

return 'modDevToolsSystemSettingsUpdateFromGridProcessor';
