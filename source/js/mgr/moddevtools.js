var moddevtools = function (config) {
    config = config || {};
    moddevtools.superclass.constructor.call(this, config);
};
Ext.extend(moddevtools, Ext.Component, {
    initComponent: function () {
        this.stores = {};
        this.ajax = new Ext.data.Connection({
            disableCaching: true,
        });
    }, page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, util: {}
});
Ext.reg('moddevtools', moddevtools);

modDevTools = new moddevtools();
