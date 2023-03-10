<?php
/**
 * Resolve regenerate tables
 *
 * @package moddevtools
 * @subpackage build
 *
 * @var array $options
 * @var xPDOObject $object
 */

$success = true;

if ($object->xpdo) {
    /** @var modX $modx */
    $modx =& $object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            if ($modx->getOption('regenerate_tables', $options, '1') == '1') {
                $autoloadPath = $modx->getOption('moddevtools.core_path', null, $modx->getOption('core_path') . 'components/moddevtools/') . 'vendor/autoload.php';
                require_once $autoloadPath;
                $processorsPath = $modx->getOption('moddevtools.core_path', null, $modx->getOption('core_path') . 'components/moddevtools/') . 'processors/';
                $modx->runProcessor('mgr/tables/regenerate', [
                    'filters' => ['modChunk', 'modTemplate', 'modResource']
                ], ['processors_path' => $processorsPath]);
                $modx->log(xPDO::LOG_LEVEL_INFO, 'modDevTools tables regenerated.');
            }
            break;
    }
}
return $success;
