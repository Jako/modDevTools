<?php
/**
 * Class modDevToolsSearchProcessor
 */

use TreehillStudio\ModDevTools\Processors\Processor;

class modDevToolsSearchProcessor extends Processor
{
    public $map;

    public function initialize()
    {
        $this->map = [
            'modChunk' => [
                'nameField' => 'name',
                'content' => 'snippet',
                'type' => 'chunk',
                'typeText' => $this->modx->lexicon('chunk')
            ],
            'modTemplate' => [
                'nameField' => 'templatename',
                'content' => 'content',
                'type' => 'template',
                'typeText' => $this->modx->lexicon('template')
            ]
        ];
        return true;
    }

    /**
     * Run the processor and return the result.
     *
     * @return mixed
     */
    public function process()
    {
        $data = [];

        $filters = $this->getProperty('filters');
        foreach ($filters as $class => $enabled) {
            if (isset($this->map[$class]) && $enabled) {
                $data = array_merge($data, $this->getElements($class));
            }
        }

        return $this->outputArray($data);
    }

    /**
     * @param $class
     * @return array
     */
    public function getElements($class)
    {
        $data = [];

        $search = $this->getProperty('search-string');
        $c = $this->modx->newQuery($class, [$this->map[$class]['content'] . ':LIKE BINARY' => '%' . $search . '%']);

        /** @var modElement[] $elements */
        $elements = $this->modx->getIterator($class, $c);
        foreach ($elements as $element) {
            $object = $element->toArray();
            $data[] = [
                'id' => $object['id'],
                'name' => $object[$this->map[$class]['nameField']],
                'class' => $class,
                'content' => $this->moddevtools->getSearchContent($object['content'], $search),
                'type' => $this->map[$class]['type'],
                'type_text' => $this->map[$class]['typeText'],
                'offset' => 0
            ];
        }

        return $data;
    }
}

return 'modDevToolsSearchProcessor';