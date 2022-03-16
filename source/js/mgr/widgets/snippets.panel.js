modDevTools.panel.Snippets = function (config) {
    config = config || {};
    Ext.apply(config, {
        id: 'tools-panel-snippets',
        params: {
            action: 'mgr/snippet/getlist',
            parent: MODx.request.id,
            link_type: config.link_type
        },
        config: {
            element: 'snippet',
            mimeType: 'application/x-php',
            modxTags: false
        }
    });
    this.config = config;
    modDevTools.panel.Snippets.superclass.constructor.call(this, config);
};
Ext.extend(modDevTools.panel.Snippets, modDevTools.panel.Elements, {
    getElementValue: function (r) {
        return '<?php\r\n' + (r.snippet || '');
    },
    loadProperties: function (r) {
        return {
            xtype: 'panel',
            listeners: {
                beforerender: {
                    fn: function (form) {
                        form.add({
                            title: _('properties'),
                            headerCfg: {
                                style: {
                                    background: '#f0f0f0',
                                    margin: '10px 0 0',
                                    padding: '10px',
                                    cursor: 'pointer'
                                }
                            },
                            items: [{
                                xtype: 'moddevtools-grid-properties',
                                pk: r.id,
                                style: {
                                    padding: '10px',
                                    border: '1px solid #ececec'
                                }
                            }
                            ],
                            collapsible: true,
                            collapsed: true,
                            listeners: {
                                afterrender: function (panel) {
                                    panel.header.on('click', function () {
                                        if (panel.collapsed) {
                                            panel.expand();
                                        } else {
                                            panel.collapse();
                                        }
                                    });
                                }
                            }
                        });
                        form.doLayout();
                    }, scope: this
                }
            }
        };
    }
});
Ext.reg('moddevtools-panel-snippets', modDevTools.panel.Snippets);
