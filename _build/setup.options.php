<?php
/**
 * Setup options
 *
 * @package moddevtools
 * @subpackage build
 *
 * @var modX $modx
 * @var array $options
 */

// Defaults
$defaults = [
    'regenerate_tables' => true,
];

$output = '<style type="text/css">
    #modx-setupoptions-panel { display: none; }
    #modx-setupoptions-form p { margin-bottom: 10px; }
    #modx-setupoptions-form h2 { margin-bottom: 15px; }
</style>';

$values = [];
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
        $output .= '<h2>Install modDevTools</h2>
        <p>Thanks for installing modDevTools. This open source extra is 
        maintained by Treehill Studio - MODX development in Münsterland.</p>

        <p>During the installation, we will collect some statistical data (the
        hostname, the MODX UUID, the PHP version and the MODX version of your
        MODX installation). Your data will be kept confidential and under no
        circumstances be used for promotional purposes or disclosed to third
        parties. We only like to know the usage count of this package.</p>
        
        <p>If you install this package, you are giving us your permission to
        collect, process and use that data for statistical purposes.</p>
        
        <p>Please review the installation options carefully.</p>';

        $output .= '<div style="position: relative">
                        <input type="hidden" name="regenerate_tables" value="0">
                        <input type="checkbox" name="regenerate_tables" id="regenerate_tables" ' . (($defaults['regenerate_tables']) ? 'checked' : '') . ' value="1"> 
                        <label for="link_elements" style="display: inline;">Generate element links during setup (switch off, if you experience a timeout during installing modDevTools).</label>
                    </div>';
        break;
    case xPDOTransport::ACTION_UPGRADE:
        $output .= '<h2>Upgrade modDevTools</h2>
        <p>modDevTools will be upgraded. This open source extra is maintained by
        Treehill Studio - MODX development in Münsterland.</p>

        <p>During the upgrade, we will collect some statistical data (the
        hostname, the MODX uuid, the PHP version, the MODX version of your
        MODX installation and the previous installed version of this extra
        package). Your data will be kept confidential and under no
        circumstances be used for promotional purposes or disclosed to third
        parties. We only like to know the usage count of this package.</p>

        <p>If you upgrade this package, you are giving us your permission to
        collect, process and use that data for statistical purposes.</p>';

        $output .= '<div style="position: relative">
                        <input type="hidden" name="regenerate_tables" value="0">
                        <input type="checkbox" name="regenerate_tables" id="regenerate_tables" ' . (($defaults['regenerate_tables']) ? 'checked' : '') . ' value="1"> 
                        <label for="link_elements" style="display: inline;">Regenerate element links during setup (switch off, if you experience a timeout during installing modDevTools).</label>
                    </div>';
        break;
    case xPDOTransport::ACTION_UNINSTALL:
        break;
}

return $output;
