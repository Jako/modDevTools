<?php
/**
 * Get list Resources by template
 */

// Compatibility between 2.x/3.x
if (file_exists(MODX_PROCESSORS_PATH . 'resource/getlist.class.php')) {
    require_once MODX_PROCESSORS_PATH . 'resource/getlist.class.php';
} elseif (!class_exists('modResourceGetListProcessor')) {
    class_alias(\MODX\Revolution\Processors\Resource\GetList::class, \modResourceGetListProcessor::class);
}

/**
 * Class modDevToolsResourceGetListProcessor
 */
class modDevToolsResourceGetListProcessor extends modResourceGetListProcessor
{
    /**
     * @param string $link
     * @param bool|string $nested
     * @return array
     */
    public function getParentsByLink($link, $nested = false)
    {
        $c = $this->modx->newQuery('modDevToolsLink');
        if ($nested) {
            $c->innerJoin('modDevToolsLink', 'Link', 'Link.parent=modDevToolsLink.child');
            $c->where([
                'modDevToolsLink.link_type' => $nested,
            ]);
            $child = 'Link.';
            $parent = 'modDevToolsLink.';
        } else {
            $child = '';
            $parent = '';
        }
        $c->where([
            $child . 'link_type' => $link,
            $child . 'child' => $this->getProperty('id'),
        ]);
        $c->select($parent . 'parent');
        if ($c->prepare() && $c->stmt->execute()) {
            return $c->stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            return [];
        }
    }

    /**
     * {@inheritDoc}
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c = parent::prepareQueryBeforeCount($c);

        $linkType = $this->getProperty('link_type', false);
        if ($linkType === 'res-chunk' || $linkType === 'res-snip') {
            $templates = [];
            $resources = [];

            if ($linkType === 'res-chunk') {
                $templates = $this->getParentsByLink('temp-chunk');
                $templates = array_merge($templates, $this->getParentsByLink('chunk-chunk', 'temp-chunk'));
                $resources = $this->getParentsByLink('res-chunk');
            } else if ($linkType === 'res-snip') {
                $templates = $this->getParentsByLink('temp-snip');
                $templates = array_merge($templates, $this->getParentsByLink('chunk-snip', 'temp-chunk'));
                $resources = $this->getParentsByLink('res-snip');
            }

            $templates = array_unique($templates);
            $tempCount = count($templates);
            $resCount = count($resources);

            if (($tempCount == 0) && ($resCount == 0)) {
                $c->where([
                    'id' => 0
                ]);
            } else {
                if ($tempCount > 0) {
                    $c->where([
                        'template:IN' => $templates
                    ]);
                }
                if ($resCount > 0) {
                    $c->orCondition([
                        'id:IN' => $resources
                    ]);
                }
            }
        } else {
            $c->where([
                'template' => $this->getProperty('id'),
            ]);
        }

        return $c;
    }

    /**
     * {@inheritDoc}
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        $ta = parent::prepareRow($object);
        $allowedFields = [
            'id' => true,
            'template' => true,
            'pagetitle' => true,
            'parent' => true,
            'published' => true,
            'deleted' => true,
            'menuindex' => true,
            'createdon' => true,
            'publishedon' => true,
            'context_key' => true,
        ];
        $ta = array_intersect_key($ta, $allowedFields);

        $ta['preview_url'] = $this->modx->makeUrl($ta['id'], $ta['context_key']);

        $ta['actions'] = [];
        if ($this->modx->hasPermission('edit_document')) {
            $ta['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-pencil-square-o',
                'title' => $this->modx->lexicon('edit'),
                'action' => 'editResource',
                'button' => true,
                'menu' => true,
            ];
        }
        $ta['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-eye',
            'title' => $this->modx->lexicon('preview'),
            'action' => 'previewResource',
            'button' => true,
            'menu' => true,
        ];
        if ($this->modx->hasPermission('save_document')) {
            $ta['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-wrench',
                'title' => $this->modx->lexicon('moddevtools.template_change'),
                'action' => 'changeTemplate',
                'button' => true,
                'menu' => true,
            ];
        }
        if ($this->modx->hasPermission('publish_document')) {
            if (!$ta['published']) {
                $ta['actions'][] = [
                    'cls' => '',
                    'icon' => 'icon icon-power-off red',
                    'title' => $this->modx->lexicon('publish'),
                    'action' => 'publishResource',
                    'button' => true,
                    'menu' => true,
                ];
            } else {
                $ta['actions'][] = [
                    'cls' => '',
                    'icon' => 'icon icon-power-off',
                    'title' => $this->modx->lexicon('unpublish'),
                    'action' => 'unpublishResource',
                    'button' => true,
                    'menu' => true,
                ];
            }
        }
        if ($this->modx->hasPermission('delete_document')) {
            if (!$ta['deleted']) {
                // Remove
                $ta['actions'][] = [
                    'cls' => '',
                    'icon' => 'icon icon-trash-o',
                    'title' => $this->modx->lexicon('remove'),
                    'action' => 'removeResource',
                    'button' => true,
                    'menu' => true,
                ];
            } else {
                $ta['actions'][] = [
                    'cls' => '',
                    'icon' => 'icon icon-trash-o red',
                    'title' => $this->modx->lexicon('undelete'),
                    'action' => 'undeleteResource',
                    'button' => true,
                    'menu' => true,
                ];
            }
        }

        return $ta;
    }
}

return 'modDevToolsResourceGetListProcessor';
