<?php
/**
 * Get list Chunks
 */

use TreehillStudio\ModDevTools\Processors\ObjectGetListProcessor;

class modDevToolsChunkGetListProcessor extends ObjectGetListProcessor
{
    public $classKey = 'modChunk';
    public $defaultSortField = 'modChunk.name';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'modChunk';

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

return 'modDevToolsChunkGetListProcessor';