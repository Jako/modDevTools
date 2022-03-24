<?php
/**
 * Get list Templates
 */

use TreehillStudio\ModDevTools\Processors\ObjectGetListProcessor;

class modDevToolsTemplateGetListProcessor extends ObjectGetListProcessor
{
    public $classKey = 'modTemplate';
    public $defaultSortField = 'modTemplate.templatename';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'modTemplate';

    /**
     * {@inheritDoc}
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->leftJoin('modDevToolsLink', 'Link', [$this->classKey . '.id = Link.parent']);
        $c->where([
            'Link.link_type' => $this->getProperty('link_type'),
            'Link.child' => $this->getProperty('child'),
        ]);
        return $c;
    }

    /**
     * Prepare the row for iteration
     * @param xPDOObject $object
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        $array = [
            'id' => $object->get('id'),
            'name' => $object->get('templatename'),
            'snippet' => $object->get('content'),
        ];
        return $array;
    }
}

return 'modDevToolsTemplateGetListProcessor';