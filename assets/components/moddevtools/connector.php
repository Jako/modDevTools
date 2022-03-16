<?php
/**
 * ModDevTools connector
 *
 * @package moddevtools
 * @subpackage connector
 *
 * @var modX $modx
 */

require_once dirname(__FILE__, 4) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('moddevtools.core_path', null, $modx->getOption('core_path') . 'components/moddevtools/');
/** @var modDevTools $modDevTools */
$modDevTools = $modx->getService('moddevtools', 'modDevTools', $corePath . 'model/moddevtools/', [
    'core_path' => $corePath
]);

// Handle request
$modx->request->handleRequest([
    'processors_path' => $modDevTools->getOption('processorsPath'),
    'location' => ''
]);
