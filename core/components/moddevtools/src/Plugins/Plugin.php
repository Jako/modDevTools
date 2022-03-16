<?php
/**
 * Abstract plugin
 *
 * @package moddevtools
 * @subpackage plugin
 */

namespace TreehillStudio\ModDevTools\Plugins;

use modX;
use ModDevTools;

/**
 * Class Plugin
 */
abstract class Plugin
{
    /** @var modX $modx */
    protected $modx;
    /** @var ModDevTools $moddevtools */
    protected $moddevtools;
    /** @var array $scriptProperties */
    protected $scriptProperties;

    /**
     * Plugin constructor.
     *
     * @param $modx
     * @param $scriptProperties
     */
    public function __construct($modx, &$scriptProperties)
    {
        $this->scriptProperties = &$scriptProperties;
        $this->modx =& $modx;
        $corePath = $this->modx->getOption('moddevtools.core_path', null, $this->modx->getOption('core_path') . 'components/moddevtools/');
        $this->moddevtools = $this->modx->getService('moddevtools', 'ModDevTools', $corePath . 'model/moddevtools/', [
            'core_path' => $corePath
        ]);
    }

    /**
     * Run the plugin event.
     */
    public function run()
    {
        $init = $this->init();
        if ($init !== true) {
            return;
        }

        $this->process();
    }

    /**
     * Initialize the plugin event.
     *
     * @return bool
     */
    public function init()
    {
        return true;
    }

    /**
     * Process the plugin event code.
     *
     * @return mixed
     */
    abstract public function process();

    /**
     * Initialize tabs in the manager form
     */
    public function initializeTabs()
    {
        $assetsUrl = $this->moddevtools->getOption('assetsUrl');
        $jsUrl = $this->moddevtools->getOption('jsUrl') . 'mgr/';
        $jsSourceUrl = $assetsUrl . '../../../source/js/mgr/';
        $cssUrl = $this->moddevtools->getOption('cssUrl') . 'mgr/';
        $cssSourceUrl = $assetsUrl . '../../../source/css/mgr/';

        $this->moddevtools->modx->controller->addLexiconTopic('moddevtools:default');
        if ($this->moddevtools->getOption('debug') && ($assetsUrl != MODX_ASSETS_URL . 'components/moddevtools/')) {
            $this->moddevtools->modx->controller->addCss($cssSourceUrl . 'moddevtools.css?v=v' . $this->moddevtools->version);
            $this->moddevtools->modx->controller->addJavascript($jsSourceUrl . 'moddevtools.js?v=v' . $this->moddevtools->version);
            $this->moddevtools->modx->controller->addJavascript($jsSourceUrl . 'helper/util.js?v=v' . $this->moddevtools->version);
            $this->moddevtools->modx->controller->addJavascript($jsSourceUrl . 'widgets/elements.panel.js?v=v' . $this->moddevtools->version);
            $this->moddevtools->modx->controller->addJavascript($jsSourceUrl . 'widgets/chunks.panel.js?v=v' . $this->moddevtools->version);
            $this->moddevtools->modx->controller->addJavascript($jsSourceUrl . 'widgets/snippets.panel.js?v=v' . $this->moddevtools->version);
            $this->moddevtools->modx->controller->addJavascript($jsSourceUrl . 'widgets/templates.panel.js?v=v' . $this->moddevtools->version);
            $this->moddevtools->modx->controller->addJavascript($jsSourceUrl . 'widgets/properties.grid.js?v=v' . $this->moddevtools->version);
            $this->moddevtools->modx->controller->addJavascript($jsSourceUrl . 'widgets/resources.grid.js?v=v' . $this->moddevtools->version);
        } else {
            $this->moddevtools->modx->controller->addCss($cssUrl . 'moddevtools.min.css?v=v' . $this->moddevtools->version);
            $this->moddevtools->modx->controller->addJavascript($jsUrl . 'moddevtools.min.js?v=v' . $this->moddevtools->version);
        }
        $this->moddevtools->modx->controller->addHtml('<script type="text/javascript">
			modDevTools.config = ' . json_encode($this->moddevtools->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ';
        </script>');
    }
}