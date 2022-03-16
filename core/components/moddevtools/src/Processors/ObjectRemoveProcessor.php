<?php
/**
 * Abstract remove processor
 *
 * @package moddevtools
 * @subpackage processors
 */

namespace TreehillStudio\ModDevTools\Processors;

use TreehillStudio\ModDevTools\ModDevTools;
use modObjectRemoveProcessor;
use modX;

/**
 * Class ObjectRemoveProcessor
 */
class ObjectRemoveProcessor extends modObjectRemoveProcessor
{
    public $languageTopics = ['moddevtools:default'];

    /** @var ModDevTools $moddevtools */
    public $moddevtools;

    /**
     * {@inheritDoc}
     * @param modX $modx A reference to the modX instance
     * @param array $properties An array of properties
     */
    public function __construct(modX &$modx, array $properties = [])
    {
        parent::__construct($modx, $properties);

        $corePath = $this->modx->getOption('moddevtools.core_path', null, $this->modx->getOption('core_path') . 'components/moddevtools/');
        $this->moddevtools = $this->modx->getService('moddevtools', 'ModDevTools', $corePath . 'model/moddevtools/');
    }

    /**
     * Get a boolean property.
     * @param string $k
     * @param mixed $default
     * @return bool
     */
    public function getBooleanProperty($k, $default = null)
    {
        return ($this->getProperty($k, $default) === 'true' || $this->getProperty($k, $default) === true || $this->getProperty($k, $default) === '1' || $this->getProperty($k, $default) === 1);
    }
}
