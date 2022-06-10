modDevTools.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        cls: 'container home-panel' + ((modDevTools.config.debug) ? ' debug' : '') + ' modx' + modDevTools.config.modxversion,
        defaults: {
            collapsible: false,
            autoHeight: true
        },
        items: [{
            html: '<h2>' + _('moddevtools') + '</h2>' + ((modDevTools.config.debug) ? '<div class="ribbon top-right"><span>' + _('moddevtools.debug_mode') + '</span></div>' : ''),
            border: false,
            cls: 'modx-page-header'
        }, {
            defaults: {
                autoHeight: true
            },
            border: true,
            items: [{
                xtype: 'moddevtools-panel-overview'
            }]
        }, {
            cls: "treehillstudio_about",
            html: '<img width="146" height="40" src="' + modDevTools.config.assetsUrl + 'img/mgr/treehill-studio-small.png"' + ' srcset="' + modDevTools.config.assetsUrl + 'img/mgr/treehill-studio-small@2x.png 2x" alt="Treehill Studio">',
            listeners: {
                afterrender: function () {
                    this.getEl().select('img').on('click', function () {
                        var msg = '<span style="display: inline-block; text-align: center;">&copy; 2014-2019 by Vitaly Kireev <a href="https://github.com/argnist" target="_blank">github.com/argnist</a><br>' +
                            '<img src="' + modDevTools.config.assetsUrl + 'img/mgr/treehill-studio.png" srcset="' + modDevTools.config.assetsUrl + 'img/mgr/treehill-studio@2x.png 2x" alt="Treehill Studio" style="margin-top: 10px"><br>' +
                            '&copy; 2019-2022 by <a href="https://treehillstudio.com" target="_blank">treehillstudio.com</a></span>';
                        Ext.Msg.show({
                            title: _('moddevtools') + ' ' + modDevTools.config.version,
                            msg: msg,
                            buttons: Ext.Msg.OK,
                            cls: 'treehillstudio_window',
                            width: 358
                        });
                    });
                }
            }
        }]
    });
    modDevTools.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(modDevTools.panel.Home, MODx.Panel);
Ext.reg('moddevtools-panel-home', modDevTools.panel.Home);

modDevTools.panel.FormTab = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'moddevtools-panel-' + config.formtype,
        title: config.title,
        items: [{
            html: '<p>' + config.description + '</p>',
            border: false,
            cls: 'panel-desc'
        }, {
            layout: 'form',
            cls: 'x-form-label-left main-wrapper',
            defaults: {
                autoHeight: true
            },
            border: true,
            items: [{
                id: 'moddevtools-panel-' + config.formtype + '-form',
                xtype: 'moddevtools-' + config.formtype + '-form',
                preventRender: true
            }]
        }]
    });
    modDevTools.panel.FormTab.superclass.constructor.call(this, config);
};
Ext.extend(modDevTools.panel.FormTab, MODx.Panel);
Ext.reg('moddevtools-panel-formtab', modDevTools.panel.FormTab);

modDevTools.panel.Overview = function (config) {
    config = config || {};
    this.ident = 'moddevtools-panel-overview-' + Ext.id();
    this.panelOverviewTabs = [{
        xtype: 'moddevtools-panel-formtab',
        title: _('moddevtools.search'),
        description: _('moddevtools.search_desc'),
        formtype: 'search'
    }, {
        xtype: 'moddevtools-panel-formtab',
        title: _('moddevtools.regenerate'),
        description: _('moddevtools.regenerate_desc'),
        formtype: 'regenerate'
    }];
    if (modDevTools.config.is_admin) {
        this.panelOverviewTabs.push({
            xtype: 'moddevtools-panel-settings'
        })
    }
    Ext.applyIf(config, {
        id: 'moddevtools-panel-overview',
        items: [{
            xtype: 'modx-tabs',
            border: true,
            stateful: true,
            stateId: 'moddevtools-panel-overview',
            stateEvents: ['tabchange'],
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            autoScroll: true,
            deferredRender: false,
            forceLayout: true,
            defaults: {
                layout: 'form',
                autoHeight: true,
                hideMode: 'offsets'
            },
            items: this.panelOverviewTabs,
            listeners: {
                tabchange: function (o, t) {
                    if (t.xtype === 'moddevtools-panel-settings') {
                        Ext.getCmp('moddevtools-grid-system-settings').getStore().reload();
                    }
                }
            }
        }]
    });
    modDevTools.panel.Overview.superclass.constructor.call(this, config);
};
Ext.extend(modDevTools.panel.Overview, MODx.Panel);
Ext.reg('moddevtools-panel-overview', modDevTools.panel.Overview);
