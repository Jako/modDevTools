modDevTools.util.dateRenderer = function (format) {
    return function (v) {
        if (typeof v === 'string' && v !== '' && Date.parseDate(v, 'Y-m-d H:i:s')) {
            var format = (format) ? format : MODx.config.manager_date_format + ' ' + MODx.config.manager_time_format;
            v = Date.parseDate(v, 'Y-m-d H:i:s');
            return Ext.util.Format.date(v, format);
        } else {
            return v;
        }
    }
};

modDevTools.util.yesnoRenderer = function (value, cell) {
    cell.css = (parseInt(value) === 1 || value) ? 'green' : 'red';
    return (parseInt(value) === 1 || value) ? _('yes') : _('no');
}

modDevTools.util.getMenu = function (actions, grid, selected) {
    var menu = [];
    var cls, icon, title, action;
    for (var i in actions) {
        if (!actions.hasOwnProperty(i)) {
            continue;
        }
        var a = actions[i];
        if (!a['menu']) {
            if (a === '-') {
                menu.push('-');
            }
            continue;
        } else if (menu.length > 0 && /^remove/i.test(a['action'])) {
            menu.push('-');
        }
        if (selected.length > 1) {
            if (!a['multiple']) {
                continue;
            } else if (typeof (a['multiple']) == 'string') {
                a['title'] = a['multiple'];
            }
        }
        if (!a['title']) {
            continue;
        }
        cls = a['cls'] || '';
        icon = a['icon'] || '';
        title = a['title'] || '';
        action = a['action'] ? grid[a['action']] : '';
        menu.push({
            handler: action,
            text: String.format(
                '<span class="{0}"><i class="x-menu-item-icon {1}"></i>{2}</span>',
                cls, icon, title
            )
        });
    }
    return menu;
};

modDevTools.util.actionsRenderer = function (value, props, row) {
    var res = [];
    var cls, icon, title, action, item;
    for (var i in row.data.actions) {
        if (!row.data.actions.hasOwnProperty(i)) {
            continue;
        }
        var a = row.data.actions[i];
        if (!a['button']) {
            continue;
        }
        cls = a['cls'] || '';
        icon = a['icon'] || '';
        action = a['action'] || '';
        title = a['title'] || '';
        item = String.format(
            '<li class="{0}"><a class="action" href="#" data-action="{2}" title="{3}"><i class="{1}"></i></a></li>',
            cls, icon, action, title
        );
        res.push(item);
    }
    return String.format(
        '<ul class="moddevtools-row-actions">{0}</ul>',
        res.join('')
    );
};

modDevTools.util.addTab = function (tbp, opt) {
    var tabs = Ext.getCmp(tbp);
    if (tabs) {
        Ext.applyIf(opt, {
            id: 'modx-' + Ext.id() + '-tab',
            layout: 'form',
            labelAlign: 'left',
            autoHeight: true,
            defaults: {
                border: false,
                msgTarget: 'side',
                width: 400
            }
        });
        tabs.add(opt);
        tabs.doLayout();
        tabs.setActiveTab(0);
    }
};
