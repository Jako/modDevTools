modDevTools.grid.Properties = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'moddevtools-grid-properties-' + Ext.id(),
        url: modDevTools.config.connectorUrl,
        baseParams: {
            action: 'mgr/element/getproperties',
            classKey: 'modSnippet',
            propertySet: 0,
            pk: config.pk
        },
        cls: 'modx-grid modx-grid-small modx' + modDevTools.config.modxversion,
        fields: ['fieldLabel', 'value', 'description'],
        showActionsColumn: false,
        columns: [{
            header: _('moddevtools.property_default'),
            dataIndex: 'fieldLabel',
            width: 100,
            renderer: function (value, metaData, record) {
                return '&' + value + '=`' + record.data.value + '`';
            }
        }, {
            header: _('moddevtools.description'),
            dataIndex: 'description',
            width: 200
        }],
        paging: false,
        showPerPage: false
    });
    modDevTools.grid.Properties.superclass.constructor.call(this, config);
};
Ext.extend(modDevTools.grid.Properties, MODx.grid.Grid, {});
Ext.reg('moddevtools-grid-properties', modDevTools.grid.Properties);
