<?php
/**
 * modDevTools Plugin
 *
 * @package moddevtools
 * @subpackage plugin
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$className = 'TreehillStudio\ModDevTools\Plugins\Events\\' . $modx->event->name;

$corePath = $modx->getOption('moddevtools.core_path', null, $modx->getOption('core_path') . 'components/moddevtools/');
/** @var modDevTools $moddevtools */
$moddevtools = $modx->getService('moddevtools', 'modDevTools', $corePath . 'model/moddevtools/', [
    'core_path' => $corePath
]);

if ($moddevtools) {
    if (class_exists($className)) {
        $handler = new $className($modx, $scriptProperties);
        if (get_class($handler) == $className) {
            $handler->run();
        } else {
            $modx->log(xPDO::LOG_LEVEL_ERROR, $className. ' could not be initialized!', '', 'modDevTools Plugin');
        }
    } else {
        $modx->log(xPDO::LOG_LEVEL_ERROR, $className. ' was not found!', '', 'modDevTools Plugin');
    }
}

return;