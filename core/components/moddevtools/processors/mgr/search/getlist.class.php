<?php
/**
 * Class modDevToolsSearchProcessor
 */

use TreehillStudio\ModDevTools\Processors\Processor;

class modDevToolsSearchProcessor extends Processor
{
    /** @var array $map */
    public $map = [];

    /** @var array $searchOptions */
    protected $searchOptions = [];

    public function initialize()
    {
        $this->map = $this->moddevtools->getSearchElementMap();
        foreach ($this->map as $class => $element) {
            $this->map[$class]['typeText'] = $this->modx->lexicon($element['type']);
        }
        $this->searchOptions = $this->moddevtools->getSearchOptions($this->getProperties());
        return true;
    }

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function checkPermissions()
    {
        return $this->moddevtools->getOption('viewChunk') || $this->moddevtools->getOption('viewTemplate');
    }

    /**
     * Check if the user may view the content of the elements of a class.
     *
     * @param string $class
     * @return bool
     */
    protected function canView($class)
    {
        return (bool)$this->moddevtools->getOption($this->map[$class]['view']);
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
        $search = (string)$this->getProperty('search-string');
        if (!is_array($filters) || trim($search) === '') {
            return $this->outputArray($data);
        }

        $pattern = $this->moddevtools->buildPattern($search, $this->searchOptions, $error);
        if ($pattern === false) {
            // The form reader of the search form needs the (empty) results to display the message
            return json_encode([
                'success' => false,
                'message' => $this->modx->lexicon($error),
                'total' => 0,
                'results' => [],
            ]);
        }

        foreach ($filters as $class => $enabled) {
            if (isset($this->map[$class]) && $enabled && $this->canView($class)) {
                $data = array_merge($data, $this->getElements($class, $search, $pattern));
            }
        }

        return $this->outputArray($data);
    }

    /**
     * @param string $class
     * @param string $search
     * @param string $pattern
     * @return array
     */
    public function getElements($class, $search, $pattern)
    {
        $data = [];
        $content = $this->map[$class]['content'];

        $c = $this->modx->newQuery($class);
        if ($this->searchOptions['mode'] === 'text') {
            // The text search can be prefiltered in the database. The matches are verified below.
            $like = $this->searchOptions['ignoreCase'] ? 'LIKE' : 'LIKE BINARY';
            // The content of static elements is read from the file, so they can't be prefiltered.
            $c->where([
                'static' => 0,
                $content . ':' . $like => '%' . addcslashes($search, '\\%_') . '%'
            ]);
            $c->orCondition(['static' => 1]);
        }

        /** @var modElement[] $elements */
        $elements = $this->modx->getIterator($class, $c);
        foreach ($elements as $element) {
            $text = $this->moddevtools->getElementContent($element, $content);
            $matches = $this->moddevtools->findMatches($text, $pattern);
            if (empty($matches)) {
                continue;
            }
            $data[] = [
                'id' => $element->get('id'),
                'name' => $element->get($this->map[$class]['nameField']),
                'class' => $class,
                'content' => $this->moddevtools->getSearchContent($text, $search, 0, $this->searchOptions),
                'type' => $this->map[$class]['type'],
                'type_text' => $this->map[$class]['typeText'],
                'matches' => count($matches),
                'offset' => 0
            ];
        }

        return $data;
    }
}

return 'modDevToolsSearchProcessor';
