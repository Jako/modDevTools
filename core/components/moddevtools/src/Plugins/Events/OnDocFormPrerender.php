<?php
/**
 * @package moddevtools
 * @subpackage plugin
 */

namespace TreehillStudio\ModDevTools\Plugins\Events;

use modSystemEvent;
use TreehillStudio\ModDevTools\Plugins\Plugin;

class OnDocFormPrerender extends Plugin
{
    public function process()
    {
        if ($this->moddevtools->getOption('modxversion') == 2) {
            $this->getBreadCrumbs();
        }
    }

    /**
     *
     */
    public function getBreadCrumbs()
    {
        $mode = $this->modx->getOption('mode', $this->scriptProperties);
        $resource = $this->modx->getOption('resource', $this->scriptProperties);

        if (($mode === modSystemEvent::MODE_NEW) || !$resource) {
            if (!isset($_GET['parent'])) {
                return;
            }
            $resource = $this->modx->getObject('modResource', $_GET['parent']);
            if (!$resource) {
                return;
            }
        }
        $context = $resource->get('context_key');
        if ($context != 'web') {
            $this->modx->reloadContext($context);
        }

        $limit = (int)$this->moddevtools->getOption('breadcrumb_limit', [], 3);
        $resources = $this->modx->getParentIds($resource->get('id'), $limit, ['context' => $context]);

        if ($mode === modSystemEvent::MODE_NEW) {
            array_unshift($resources, $_GET['parent']);
        }

        $crumbs = [];
        $root = $this->modx->toJSON([
            'text' => $this->moddevtools->getOption('show_breadcrumb_context') ? '(' . $context . ')' : '',
            'className' => 'first' . ($this->moddevtools->getOption('show_breadcrumb_context') ? ' contextname' : ''),
            'root' => true,
            'url' => '?'
        ]);

        $controllerConfig = $this->modx->controller->config;
        $action = $controllerConfig['controller'];

        if ($action == 'resource/create') {
            $action = 'resource/update';
        }

        if (isset($controllerConfig['id'])) {
            if ($controllerConfig['controller'] == 'resource/create') {
                $actionObj = $this->modx->getObject('modAction', ['controller' => 'resource/update']);
                $action = $actionObj->get('id');
            } else {
                $action = $controllerConfig['id'];
            }
        }

        $isAll = false;
        for ($i = count($resources) - 1; $i >= 0; $i--) {
            $resId = $resources[$i];
            if ($resId == 0) {
                continue;
            }
            $parent = $this->modx->getObject('modResource', $resId);
            if (!$parent) {
                break;
            }
            if ($parent->get('parent') == 0) {
                $isAll = true;
            }

            $crumbs[] = [
                'text' => $parent->get('pagetitle'),
                'url' => '?a=' . $action . '&id=' . $parent->get('id')
            ];
        }

        if ((count($resources) == $limit) && !$isAll) {
            array_unshift($crumbs, [
                'text' => '...',
            ]);
        }

        // Add pagetitle of current page
        if ($mode === modSystemEvent::MODE_NEW) {
            $pagetitle = $this->modx->lexicon('new_document');
        } else {
            $pagetitle = $resource->get('pagetitle');
        }
        $crumbs[] = ['text' => $pagetitle];

        $crumbs = $this->modx->toJSON($crumbs);

        $assetsUrl = $this->moddevtools->getOption('assetsUrl');
        $jsUrl = $this->moddevtools->getOption('jsUrl') . 'mgr/';
        $jsSourceUrl = $assetsUrl . '../../../source/js/mgr/';
        $cssUrl = $this->moddevtools->getOption('cssUrl') . 'mgr/';
        $cssSourceUrl = $assetsUrl . '../../../source/css/mgr/';

        if ($this->moddevtools->getOption('debug') && ($assetsUrl != MODX_ASSETS_URL . 'components/moddevtools/')) {
            $this->modx->controller->addJavascript($jsSourceUrl . 'moddevtools.js');
            $this->modx->controller->addJavascript($jsSourceUrl . 'widgets/breadcrumbs.panel.js');
            $this->modx->controller->addCss($cssSourceUrl . 'breadcrumbs.css');
        } else {
            $this->modx->controller->addJavascript($jsUrl . 'moddevtools.min.js');
            $this->modx->controller->addCss($cssUrl . 'breadcrumbs.min.css');
        }
        $this->modx->controller->addHtml("<script>
            Ext.onReady(function() {
                var root = {$root};
                var crumbs = {$crumbs};
                var header = Ext.getCmp('modx-resource-header').ownerCt;
                header.insert(1, {
                    xtype: 'moddevtools-breadcrumbs-panel',
                    id: 'resource-breadcrumbs',
                    desc: '',
                    root : root
                });
                header.doLayout();

                var crumbCmp = Ext.getCmp('resource-breadcrumbs');
                var bd = {
                    trail : crumbs
                };
		        crumbCmp.updateDetail(bd);

		        Ext.getCmp('modx-resource-pagetitle').on('keyup', function() {
                    bd.trail[bd.trail.length - 1] = {
                        text: crumbCmp.getPagetitle()
                    };
                    crumbCmp._updatePanel(bd);
                });
            });
            </script>"
        );
    }
}
