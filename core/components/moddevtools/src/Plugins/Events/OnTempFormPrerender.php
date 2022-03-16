<?php
/**
 * @package moddevtools
 * @subpackage plugin
 */

namespace TreehillStudio\ModDevTools\Plugins\Events;

use modSystemEvent;
use TreehillStudio\ModDevTools\Plugins\Plugin;

class OnTempFormPrerender extends Plugin
{
    /**
     * {@inheritDoc}
     * @return void
     */
    public function process()
    {
        if ($this->scriptProperties['mode'] == modSystemEvent::MODE_UPD) {
            $this->initializeTabs();
            $this->outputTemplateTab();
        }
    }

    /**
     * Output Template tab
     */
    public function outputTemplateTab()
    {
        $this->modx->controller->addHtml("
            <script>
                Ext.onReady(function() {" .
            ($this->moddevtools->getOption('viewChunk') ? "modDevTools.util.addTab('modx-template-tabs',{
                        title: _('chunks'),
                        id: 'moddevtools-template-chunks-tab',
                        width: '100%',
                        items: [{
                            xtype: 'moddevtools-panel-chunks',
                            link_type: 'temp-chunk',
                            intro: _('moddevtools.template_chunks_intro')
                        }]
                    });\n" : "") .
            ($this->moddevtools->getOption('viewSnippet') ? "modDevTools.util.addTab('modx-template-tabs',{
                        title: _('snippets'),
                        id: 'moddevtools-template-snippets-tab',
                        width: '100%',
                        items: [{
                            xtype: 'moddevtools-panel-snippets',
                            link_type: 'temp-snip',
                            intro: _('moddevtools.template_snippets_intro')
                        }]
                    });\n" : "") .
            ($this->moddevtools->getOption('viewResource') ? "modDevTools.util.addTab('modx-template-tabs',{
                        title: _('resources'),
                        id: 'moddevtools-template-resources-tab',
                        width: '100%',
                        items: [{
                            html: _('moddevtools.resource_template_intro'),
                            border: false,
                            cls: 'panel-desc',
                            width: '100%'
                        },{
                            baseCls: 'main-wrapper',
                            width: '100%',
                            layout: 'anchor',
                            items: [{
                                xtype: 'moddevtools-grid-resources',
                                link_type: 'res-temp',
                                anchor: '100%'
                            }]
                        }]
                    });\n" : "") .
            "                });
            </script>");
    }
}
