modDevTools.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        buttons: [{
            text: _('help_ex'),
            handler: MODx.loadHelpPane
        }],
        formpanel: 'moddevtools-panel-home',
        components: [{
            xtype: 'moddevtools-panel-home'
        }]
    });
    modDevTools.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(modDevTools.page.Home, MODx.Component);
Ext.reg('moddevtools-page-home', modDevTools.page.Home);
