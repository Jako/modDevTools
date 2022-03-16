<?php
/**
 * @package moddevtools
 * @subpackage plugin
 */

namespace TreehillStudio\ModDevTools\Plugins\Events;

use modSystemEvent;
use TreehillStudio\ModDevTools\Plugins\Plugin;

class OnChunkFormPrerender extends Plugin
{
    /**
     * {@inheritDoc}
     * @return void
     */
    public function process()
    {
        if ($this->scriptProperties['mode'] == modSystemEvent::MODE_UPD) {
            $this->initializeTabs();
            $this->outputChunkTab();
        }
    }

    /**
     * Output Chunk tab
     */
    private function outputChunkTab()
    {
        $this->modx->controller->addHtml("
            <script>
                Ext.onReady(function() {" .
            ($this->moddevtools->getOption('viewTemplate') ? "modDevTools.util.addTab('modx-chunk-tabs',{
                        title: _('templates'),
                        id: 'moddevtools-chunk-templates-tab',
                        width: '100%',
                        items: [{
                            xtype: 'moddevtools-panel-templates',
                            link_type: 'temp-chunk',
                            intro: _('moddevtools.chunk_templates_intro')
                        }]
                    });\n" : "") .
            ($this->moddevtools->getOption('viewChunk') ? "modDevTools.util.addTab('modx-chunk-tabs',{
                        title: _('chunks'),
                        id: 'moddevtools-chunk-chunks-tab',
                        width: '100%',
                        items: [{
                            xtype: 'moddevtools-panel-chunks',
                            link_type: 'chunk-chunk',
                            intro: _('moddevtools.chunk_chunks_intro')
                        }]
                    });\n" : "") .
            ($this->moddevtools->getOption('viewSnippet') ? "modDevTools.util.addTab('modx-chunk-tabs',{
                        title: _('snippets'),
                        id: 'moddevtools-chunk-snippets-tab',
                        width: '100%',
                        items: [{
                            xtype: 'moddevtools-panel-snippets',
                            link_type: 'chunk-snip',
                            intro: _('moddevtools.chunk_snippets_intro')
                        }]
                    });" : "") .
            ($this->moddevtools->getOption('viewResource') ? "modDevTools.util.addTab('modx-chunk-tabs',{
                        title: _('resources'),
                        id: 'moddevtools-chunk-resources-tab',
                        width: '100%',
                        items: [{
                            html: _('moddevtools.resource_chunk_intro'),
                            border: false,
                            cls: 'panel-desc',
                            width: '100%'
                        },{
                            baseCls: 'main-wrapper',
                            width: '100%',
                            layout: 'anchor',
                            items: [{
                                xtype: 'moddevtools-grid-resources',
                                link_type: 'res-chunk',
                                anchor: '100%'
                            }]
                        }]
                    });\n" : "") .
            "                });
            </script>");
    }
}
