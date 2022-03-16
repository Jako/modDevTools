<?php
/**
 * @package moddevtools
 * @subpackage plugin
 */

namespace TreehillStudio\ModDevTools\Plugins\Events;

use modSystemEvent;
use TreehillStudio\ModDevTools\Plugins\Plugin;

class OnSnipFormPrerender extends Plugin
{
    /**
     * {@inheritDoc}
     * @return void
     */
    public function process()
    {
        if ($this->scriptProperties['mode'] == modSystemEvent::MODE_UPD) {
            $this->initializeTabs();
            $this->outputSnippetTab();
        }
    }

    /**
     * Output Snippet tab
     */
    private function outputSnippetTab()
    {
        $this->modx->controller->addHtml("
            <script>
                Ext.onReady(function() {" .
            ($this->moddevtools->getOption('viewResource') ? "modDevTools.util.addTab('modx-snippet-tabs',{
                        title: _('resources'),
                        id: 'moddevtools-snippet-resources-tab',
                        width: '100%',
                        items: [{
                            html: _('moddevtools.resource_snippet_intro'),
                            border: false,
                            cls: 'panel-desc',
                            width: '100%'
                        },{
                            baseCls: 'main-wrapper',
                            width: '100%',
                            layout: 'anchor',
                            items: [{
                                xtype: 'moddevtools-grid-resources',
                                link_type: 'res-snip',
                                anchor: '100%'
                            }]
                        }]
                    });" : "") .
            "                });
            </script>");
    }
}
