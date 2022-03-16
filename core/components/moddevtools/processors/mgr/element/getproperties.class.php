<?php
/**
 * Get list Element Properties
 */

// Compatibility between 2.x/3.x
if (file_exists(MODX_PROCESSORS_PATH . 'element/getinsertproperties.class.php')) {
    require_once MODX_PROCESSORS_PATH . 'element/getinsertproperties.class.php';
} elseif (!class_exists('modResourceGetListProcessor')) {
    class_alias(\MODX\Revolution\Processors\Element\modElementGetInsertProperties::class, \modElementGetInsertProperties::class);
}

/**
 * Class modDevToolsElementGetProperties
 */
class modDevToolsElementGetProperties extends modElementGetInsertProperties
{
    /**
     * {@inheritDoc}
     * @return string
     */
    public function process() {
        $properties = $this->getElementProperties();
        $list = array();
        if (!empty($properties) && is_array($properties)) {
            foreach ($properties as $key => $property) {
                $propertyArray = $this->prepareProperty($key,$property);
                if (!empty($propertyArray)) {
                    $list[] = $propertyArray;
                }
            }
        }

        return $this->outputArray($list);
            $this->success('', $list);
        return $this->toJSON($list);
    }
}

return 'modDevToolsElementGetProperties';