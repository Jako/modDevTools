<?php
/**
 * Get list Snippets
 */

use TreehillStudio\ModDevTools\Processors\ObjectGetListProcessor;

class modDevToolsSnippetGetListProcessor extends ObjectGetListProcessor
{
    public $classKey = 'modSnippet';
    public $defaultSortField = 'modSnippet.name';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'modSnippet';

    /**
     * {@inheritDoc}
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->leftJoin('modDevToolsLink', 'Link', [$this->classKey . '.id = Link.child']);
        $c->where([
            'Link.link_type' => $this->getProperty('link_type'),
            'Link.parent' => $this->getProperty('parent'),
        ]);
        return $c;
    }
}

return 'modDevToolsSnippetGetListProcessor';