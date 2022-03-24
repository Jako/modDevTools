modDevTools.panel.SearchForm = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        cls: 'container form-with-labels',
        labelAlign: 'left',
        autoHeight: true,
        anchor: '100%',
        saveMsg: _('search'),
        url: modDevTools.config.connectorUrl,
        errorReader: new Ext.data.JsonReader({
            totalProperty: 'total',
            root: 'results',
            fields: ['id', 'name', 'class', 'type', 'content']
        }),
        baseParams: {
            action: 'mgr/search/getlist'
        },
        defaults: {border: false},
        items: [{
            layout: 'column',
            cls: 'x-toolbar',
            style: {
                backgroundColor: 'transparent'
            },
            defaults: {
                layout: 'form',
                cls: 'col-sm-4',
                border: false
            },
            items: [{
                columnWidth: 0.4,
                items: [{
                    xtype: 'textfield', cls: 'col-sm-4',
                    id: 'search-string',
                    fieldLabel: _('moddevtools.text_to_find'),
                    allowBlank: false,
                    anchor: '100%',
                    border: false,
                    labelStyle: 'line-height:24px;',
                    listeners: {
                        specialkey: function (f, o) {
                            if (o.getKey() === 13) {
                                this.submit();
                            }
                        },
                        scope: this
                    }
                }]
            }, {
                columnWidth: 0.4,
                items: [{
                    xtype: 'textfield', cls: 'col-sm-4',
                    id: 'replace-string',
                    fieldLabel: _('moddevtools.replace_with'),
                    anchor: '100%',
                    border: false,
                    labelStyle: 'line-height:24px;',
                    listeners: {
                        specialkey: function (f, o) {
                            if (o.getKey() === 13) {
                                this.submit();
                            }
                        },
                        scope: this
                    }
                }]
            }, {
                columnWidth: 0.2,
                items: [{
                    xtype: 'button',
                    cls: 'col-sm-4 primary-button',
                    align: 'left',
                    text: _('moddevtools.find'),
                    handler: this.submit,
                    scope: this,
                    anchor: '100%',
                    border: false
                }]
            }]
        }, {
            id: 'filter-group',
            xtype: 'fieldset',
            cls: 'moddevtools-fieldset',
            title: _('moddevtools.search_filters'),
            layout: 'auto',
            defaults: {
                style: {
                    width: 'auto',
                    float: 'left',
                    marginRight: '25px'
                },
                border: false
            },
            items: [{
                items: {
                    xtype: 'xcheckbox',
                    name: 'filters[modChunk]',
                    id: 'moddevtools-search-chunks',
                    boxLabel: _('chunks'),
                    inputValue: 1,
                    checked: true,
                    border: false
                }
            }, {
                items: {
                    xtype: 'xcheckbox',
                    name: 'filters[modTemplate]',
                    id: 'moddevtools-search-templates',
                    boxLabel: _('templates'),
                    inputValue: 1,
                    checked: true,
                    border: false
                }
            }]
        }, {
            id: 'moddevtools-search-results'
        }],
        listeners: {
            success: {
                fn: function (response) {
                    var results = Ext.getCmp('moddevtools-search-results');
                    results.removeAll();
                    if (response.result.success && response.result.errors) {
                        var foundItems = response.result.errors;
                        this.records = foundItems;
                        for (var i = 0; i < foundItems.length; i++) {
                            var item = {
                                xtype: 'panel',
                                title: _(foundItems[i].type) + ' ' + foundItems[i].name + ' (' + foundItems[i].id + ')',
                                headerCfg: {
                                    cls: 'moddevtools-el-header',
                                    style: {
                                        'font-weight': '700',
                                        background: '#e4e9ee',
                                        color: '#696969'
                                    }
                                },
                                items: [{
                                    id: 'found-element-' + i,
                                    xtype: 'displayfield',
                                    value: foundItems[i].content,
                                    height: 'auto',
                                    cls: 'moddevtools-search-code'
                                }],
                                bbar: [{
                                    xtype: 'button',
                                    text: _('moddevtools.replace'),
                                    handler: this.replace.createDelegate(this, [i, false, false], false),
                                    scope: this
                                }, {
                                    xtype: 'button',
                                    text: _('moddevtools.replace_all'),
                                    handler: this.replace.createDelegate(this, [i, true, false], false),
                                    scope: this
                                }, {
                                    xtype: 'button',
                                    text: _('moddevtools.skip'),
                                    handler: this.replace.createDelegate(this, [i, false, true], false),
                                    scope: this
                                }, '->', {
                                    xtype: 'splitbutton',
                                    text: _('moddevtools.edit'),
                                    handler: this.edit.createDelegate(this, [i], true),
                                    menu: new Ext.menu.Menu({
                                        cls: 'x-menu-no-icon',
                                        items: [{
                                            text: '<i class="icon icon-edit"></i> ' + _('moddevtools.edit'),
                                            iconCls: 'no-icon',
                                            handler: this.edit.createDelegate(this, [i], true),
                                            scope: this
                                        }, {
                                            text: '<i class="icon icon-clone"></i> ' + _('moddevtools.duplicate'),
                                            iconCls: 'no-icon',
                                            handler: this.duplicate.createDelegate(this, [i], true),
                                            scope: this
                                        }]
                                    }),
                                    scope: this
                                }, {
                                    xtype: 'splitbutton',
                                    text: _('moddevtools.quickedit'),
                                    handler: this.quickedit.createDelegate(this, [i], true),
                                    menu: new Ext.menu.Menu({
                                        cls: 'x-menu-no-icon',
                                        items: [{
                                            text: '<i class="icon icon-edit"></i> ' + _('moddevtools.quickedit'),
                                            iconCls: 'no-icon',
                                            handler: this.quickedit.createDelegate(this, [i], true),
                                            scope: this
                                        }, {
                                            text: '<i class="icon icon-clone"></i> ' + _('moddevtools.quickduplicate'),
                                            iconCls: 'no-icon',
                                            handler: this.quickduplicate.createDelegate(this, [i], true),
                                            scope: this
                                        }]
                                    }),
                                    scope: this
                                }]
                            };
                            results.add(item);
                        }
                    } else {
                        results.add({
                            html: '<h3>' + _('moddevtools.notfound') + '</h3>',
                            style: {
                                margin: '10px 0'
                            }
                        });
                    }
                    results.doLayout();
                }, scope: this
            }
        }
    });
    modDevTools.panel.SearchForm.superclass.constructor.call(this, config);
};
Ext.extend(modDevTools.panel.SearchForm, MODx.FormPanel, {
    edit: function (btn, e, i) {
        var item = this.records[i];
        MODx.loadPage('element/' + item.type + '/update', 'id=' + item.id);
    },
    duplicate: function (btn, e, i) {
        var item = this.records[i];
        MODx.Ajax.request({
            url: MODx.config.connector_url,
            params: {
                action: 'element/' + item.type + '/get',
                id: item.id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var rec = {
                            id: r.object.id,
                            type: item.type,
                            name: _('duplicate_of', {name: item.name}),
                            source: r.object.source,
                            static: r.object.static,
                            static_file: r.object.static_file,
                            category: r.object.category
                        };
                        var w = MODx.load({
                            xtype: 'modx-window-element-duplicate',
                            record: rec,
                            listeners: {
                                success: {
                                    fn: function (r) {
                                        var response = Ext.decode(r.a.response.responseText);
                                        MODx.loadPage('element/' + rec.type + '/update', 'id=' + response.object.id);
                                    },
                                    scope: this
                                },
                                hide: {
                                    fn: function () {
                                        this.destroy();
                                    }
                                }
                            }
                        });
                        w.show(e.target);
                    },
                    scope: this
                }
            }
        });
    },
    quickedit: function (btn, e, i) {
        var item = this.records[i];
        MODx.Ajax.request({
            url: MODx.config.connector_url,
            params: {
                action: 'element/' + item.type + '/get',
                id: item.id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var nameField = (item.type === 'template') ? 'templatename' : 'name';
                        var w = MODx.load({
                            xtype: 'modx-window-quick-update-' + item.type,
                            record: r.object,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.replace(i, false, true);
                                    },
                                    scope: this
                                },
                                hide: {
                                    fn: function () {
                                        this.destroy();
                                    }
                                }
                            }
                        });
                        w.title += ': <span dir="ltr">' + w.record[nameField] + ' (' + w.record.id + ')</span>';
                        w.setValues(r.object);
                        w.show(btn.target);
                    },
                    scope: this
                }
            }
        });
    },
    quickduplicate: function (btn, e, i) {
        var item = this.records[i];
        MODx.Ajax.request({
            url: MODx.config.connector_url,
            params: {
                action: 'element/' + item.type + '/get',
                id: item.id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var nameField = (item.type === 'template') ? 'templatename' : 'name';
                        var record = r.object;
                        record[nameField] = _('duplicate_of', {name: record[nameField]});
                        record.id = null;
                        var w = MODx.load({
                            xtype: 'modx-window-quick-create-' + item.type,
                            title: _('quick_create_' + item.type),
                            record: record,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.destroy();
                                    }
                                },
                                hide: {
                                    fn: function () {
                                        this.destroy();
                                    }
                                }
                            }
                        });
                        w.title += ': <span dir="ltr">' + w.record[nameField] + '</span>';
                        w.setValues(record);
                        w.show(btn.target);
                    },
                    scope: this
                }
            }
        });
    },
    replace: function (i, all, skip) {
        var record = this.records[i];
        var form = this.getForm();
        MODx.Ajax.request({
            url: modDevTools.config.connectorUrl,
            params: {
                id: record.id,
                class: record.class,
                action: 'mgr/search/replace',
                offset: (all) ? 0 : record.offset,
                search: form.findField('search-string').getValue(),
                replace: form.findField(skip ? 'search-string' : 'replace-string').getValue(),
                all: all
            },
            listeners: {
                success: {
                    fn: function (r) {
                        if (r.success && (typeof r.object !== 'undefined')) {
                            var element = Ext.getCmp('found-element-' + i);
                            element.setValue(r.object.content);
                            this.records[i] = r.object;
                        }
                    }, scope: this
                }
            }
        });
    }
});
Ext.reg('moddevtools-search-form', modDevTools.panel.SearchForm);