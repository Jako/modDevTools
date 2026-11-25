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
            fields: ['id', 'name', 'class', 'type', 'content', 'matches', 'offset']
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
                    hiddenName: 'filters[modChunk]',
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
                    hiddenName: 'filters[modTemplate]',
                    id: 'moddevtools-search-templates',
                    boxLabel: _('templates'),
                    inputValue: 1,
                    checked: true,
                    border: false
                }
            }]
        }, {
            id: 'options-group',
            xtype: 'fieldset',
            cls: 'moddevtools-fieldset',
            title: _('moddevtools.search_options'),
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
                    xtype: 'modx-combo',
                    id: 'moddevtools-search-mode',
                    name: 'mode',
                    hiddenName: 'mode',
                    store: [
                        ['text', _('moddevtools.search_mode_text')],
                        ['wildcard', _('moddevtools.search_mode_wildcard')],
                        ['regex', _('moddevtools.search_mode_regex')]
                    ],
                    value: 'text',
                    mode: 'local',
                    triggerAction: 'all',
                    editable: false,
                    forceSelection: true,
                    width: 230,
                    border: false,
                    listeners: {
                        select: function (combo) {
                            // Ignore case and whole words are only available for the plain text search
                            var visible = combo.getValue() === 'text';
                            Ext.getCmp('moddevtools-search-ignorecase-wrap').setVisible(visible);
                            Ext.getCmp('moddevtools-search-wholeword-wrap').setVisible(visible);
                            Ext.getCmp('moddevtools-search-mode-desc').update(_('moddevtools.search_mode_' + combo.getValue() + '_desc'));
                        }
                    }
                }
            }, {
                id: 'moddevtools-search-ignorecase-wrap',
                items: {
                    xtype: 'xcheckbox',
                    name: 'ignorecase',
                    hiddenName: 'ignorecase',
                    id: 'moddevtools-search-ignorecase',
                    boxLabel: _('moddevtools.ignore_case'),
                    inputValue: 1,
                    checked: false,
                    border: false
                }
            }, {
                id: 'moddevtools-search-wholeword-wrap',
                items: {
                    xtype: 'xcheckbox',
                    name: 'wholeword',
                    hiddenName: 'wholeword',
                    id: 'moddevtools-search-wholeword',
                    boxLabel: _('moddevtools.whole_word'),
                    inputValue: 1,
                    checked: false,
                    border: false
                }
            }, {
                id: 'moddevtools-search-mode-desc',
                xtype: 'box',
                cls: 'desc-under',
                style: {
                    clear: 'both',
                    width: '100%',
                    paddingTop: '10px'
                },
                html: _('moddevtools.search_mode_text_desc')
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
                                id: 'found-panel-' + i,
                                title: this.getPanelTitle(foundItems[i]),
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
                                }, {
                                    xtype: 'button',
                                    text: _('moddevtools.preview'),
                                    handler: this.preview.createDelegate(this, [i], false),
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
    getPanelTitle: function (item) {
        var title = _(item.type) + ' ' + item.name + ' (' + item.id + ')';
        if (typeof item.matches !== 'undefined') {
            title += ' – ' + item.matches + ' ' + _('moddevtools.matches');
        }
        return title;
    },
    getSearchParams: function () {
        var form = this.getForm();
        return {
            search: form.findField('search-string').getValue(),
            replace: form.findField('replace-string').getValue(),
            mode: Ext.getCmp('moddevtools-search-mode').getValue(),
            ignorecase: Ext.getCmp('moddevtools-search-ignorecase').getValue() ? 1 : 0,
            wholeword: Ext.getCmp('moddevtools-search-wholeword').getValue() ? 1 : 0
        };
    },
    preview: function (i) {
        var record = this.records[i];
        MODx.Ajax.request({
            url: modDevTools.config.connectorUrl,
            params: Ext.apply({
                action: 'mgr/search/preview',
                id: record.id,
                class: record.class
            }, this.getSearchParams()),
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'moddevtools-window-replace-preview',
                            preview: r.object,
                            listeners: {
                                apply: {
                                    fn: function (indexes, hash) {
                                        this.replace(i, false, false, {
                                            indexes: indexes.join(','),
                                            hash: hash
                                        });
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
                        w.show();
                    },
                    scope: this
                }
            }
        });
    },
    replace: function (i, all, skip, extra) {
        var record = this.records[i];
        MODx.Ajax.request({
            url: modDevTools.config.connectorUrl,
            params: Ext.apply({
                id: record.id,
                class: record.class,
                action: 'mgr/search/replace',
                offset: (all) ? 0 : record.offset,
                all: all,
                skip: skip ? 1 : 0
            }, this.getSearchParams(), extra || {}),
            listeners: {
                success: {
                    fn: function (r) {
                        if (r.success && (typeof r.object !== 'undefined')) {
                            var element = Ext.getCmp('found-element-' + i);
                            element.setValue(r.object.content);
                            var panel = Ext.getCmp('found-panel-' + i);
                            if (panel) {
                                panel.setTitle(this.getPanelTitle(r.object));
                            }
                            this.records[i] = r.object;
                        }
                    }, scope: this
                }
            }
        });
    }
});
Ext.reg('moddevtools-search-form', modDevTools.panel.SearchForm);

modDevTools.window.ReplacePreview = function (config) {
    config = config || {};
    var preview = config.preview;
    var summary = _('moddevtools.preview_matches', {total: preview.total, name: preview.name});
    if (preview.total > preview.shown) {
        summary += ' ' + _('moddevtools.preview_truncated', {shown: preview.shown});
    }
    var encode = Ext.util.Format.htmlEncode;
    var sm = new Ext.grid.CheckboxSelectionModel({checkOnly: true});
    this.grid = new Ext.grid.GridPanel({
        store: new Ext.data.JsonStore({
            data: preview.matches,
            fields: ['index', 'line', 'before', 'match', 'replacement', 'after']
        }),
        sm: sm,
        border: false,
        stripeRows: true,
        autoScroll: true,
        columns: [sm, {
            header: _('moddevtools.preview_line'),
            dataIndex: 'line',
            width: 60,
            fixed: true,
            sortable: true
        }, {
            id: 'moddevtools-preview-change',
            header: _('moddevtools.preview_change'),
            dataIndex: 'match',
            css: 'white-space: normal;',
            renderer: function (value, meta, record) {
                var r = record.data;
                return '<span class="moddevtools-preview-line">' + encode(r.before) +
                    '<del>' + encode(r.match) + '</del><ins>' + encode(r.replacement) + '</ins>' +
                    encode(r.after) + '</span>';
            }
        }],
        autoExpandColumn: 'moddevtools-preview-change',
        tbar: [{
            xtype: 'tbtext',
            text: summary
        }, '->', {
            text: _('moddevtools.preview_select_all'),
            handler: function () {
                sm.selectAll();
            }
        }, {
            text: _('moddevtools.preview_select_none'),
            handler: function () {
                sm.clearSelections();
            }
        }],
        listeners: {
            viewready: function () {
                sm.selectAll();
            }
        }
    });
    Ext.applyIf(config, {
        title: _('moddevtools.preview_title'),
        modal: true,
        width: 900,
        height: 480,
        layout: 'fit',
        items: [this.grid],
        buttons: [{
            text: _('close'),
            handler: function () {
                this.close();
            },
            scope: this
        }, {
            text: _('moddevtools.preview_apply'),
            cls: 'primary-button',
            handler: this.applySelected,
            scope: this
        }]
    });
    modDevTools.window.ReplacePreview.superclass.constructor.call(this, config);
    this.addEvents('apply');
    this.preview = preview;
};
Ext.extend(modDevTools.window.ReplacePreview, Ext.Window, {
    applySelected: function () {
        var indexes = [];
        Ext.each(this.grid.getSelectionModel().getSelections(), function (record) {
            indexes.push(record.get('index'));
        });
        if (!indexes.length) {
            return;
        }
        this.fireEvent('apply', indexes, this.preview.hash);
        this.close();
    }
});
Ext.reg('moddevtools-window-replace-preview', modDevTools.window.ReplacePreview);
