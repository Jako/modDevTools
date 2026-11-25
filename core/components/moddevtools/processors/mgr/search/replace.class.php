<?php
/**
 * Replace Elements content
 */

use TreehillStudio\ModDevTools\ModDevTools;

// Compatibility between 2.x/3.x
if (file_exists(MODX_PROCESSORS_PATH . 'element/update.class.php')) {
    require_once MODX_PROCESSORS_PATH . 'element/update.class.php';
} elseif (!class_exists('modElementUpdateProcessor')) {
    class_alias(\MODX\Revolution\Processors\Element\Update::class, \modElementUpdateProcessor::class);
}

/**
 * Class modDevToolsReplaceProcessor
 *
 * Properties:
 * - id, class: The element
 * - search, replace: The search and replace strings
 * - mode, ignorecase, wholeword: The search options
 * - offset: The character offset in the content, where the next match is searched
 * - all: Replace all matches
 * - skip: Don't replace the next match, only move the offset behind it
 * - indexes, hash: Replace only the matches with these indexes (comma separated), if the content hash is unchanged
 */
class modDevToolsReplaceProcessor extends modElementUpdateProcessor
{
    public $nameField = 'name';
    public $contentField = 'content';

    /** @var ModDevTools */
    public $moddevtools;

    /** @var string $elementClass The legacy class name of the element, e.g. modChunk */
    protected $elementClass = '';

    /** @var array $searchOptions */
    protected $searchOptions = [];

    /** @var bool $prepared */
    protected $prepared = false;

    /**
     * {@inheritDoc}
     * @param modX $modx A reference to the modX instance
     * @param array $properties An array of properties
     */
    function __construct(modX &$modx, array $properties = [])
    {
        parent::__construct($modx, $properties);

        $corePath = $this->modx->getOption('moddevtools.core_path', null, $this->modx->getOption('core_path') . 'components/moddevtools/');
        $this->moddevtools = $this->modx->getService('moddevtools', 'ModDevTools', $corePath . 'model/moddevtools/');
    }

    /**
     * Run the processor, returning a modProcessorResponse object.
     * @return bool|modProcessorResponse
     */
    public function run()
    {
        $this->elementClass = (string)$this->getProperty('class');
        switch ($this->elementClass) {
            case 'modChunk':
                $this->objectType = 'chunk';
                $this->permission = 'save_chunk';
                $this->contentField = 'snippet';
                break;
            case 'modTemplate':
                $this->objectType = 'template';
                $this->permission = 'save_template';
                $this->nameField = 'templatename';
                break;
            default:
                return false;
        }
        // MODX 3 compares the class key with the namespaced class, e.g. to find the name field of templates
        $namespacedClass = 'MODX\\Revolution\\' . $this->elementClass;
        $this->classKey = class_exists($namespacedClass) ? $namespacedClass : $this->elementClass;

        return parent::run();
    }

    /**
     * Get a boolean property.
     * @param string $k
     * @return bool
     */
    protected function getBooleanProperty($k)
    {
        return in_array($this->getProperty($k, false), [true, 1, '1', 'true'], true);
    }

    /**
     * Skipping a match doesn't change the element, so it is not saved.
     * @return mixed
     */
    public function process()
    {
        if ($this->getBooleanProperty('skip')) {
            $result = $this->prepare();
            if ($result !== true) {
                return $this->failure($result);
            }
            return $this->cleanup();
        }

        return parent::process();
    }

    /**
     * Calculate the new content and offset
     *
     * @return bool|string True or the error message
     */
    protected function prepare()
    {
        if ($this->prepared) {
            return true;
        }
        $this->prepared = true;

        $search = (string)$this->getProperty('search', '');
        $replace = (string)$this->getProperty('replace', '');
        $this->searchOptions = $this->moddevtools->getSearchOptions($this->getProperties());
        $mode = $this->searchOptions['mode'];

        $pattern = $this->moddevtools->buildPattern($search, $this->searchOptions, $error);
        if ($pattern === false) {
            return $this->modx->lexicon($error);
        }

        $content = (string)$this->object->getContent();
        $matches = $this->moddevtools->findMatches($content, $pattern);
        if ($matches === false) {
            return $this->modx->lexicon('moddevtools.err_pattern_limit');
        }

        $newContent = $content;
        $offset = 0;
        $indexes = $this->getProperty('indexes', null);
        if ($indexes !== null) {
            // Replace the selected matches of a preview
            if ($this->getProperty('hash') !== md5($content)) {
                return $this->modx->lexicon('moddevtools.err_content_changed');
            }
            $indexes = array_map('intval', array_filter(explode(',', $indexes), 'strlen'));
            if (!$indexes) {
                return $this->modx->lexicon('moddevtools.err_no_matches');
            }
            $newContent = $this->moddevtools->applyReplacements($content, $matches, $replace, $mode, $indexes);
        } elseif ($this->getBooleanProperty('all')) {
            $newContent = $this->moddevtools->applyReplacements($content, $matches, $replace, $mode);
        } else {
            // The offset is a character offset (not a byte offset)
            $startOffset = max(0, (int)$this->getProperty('offset', 0));
            $startByte = strlen(mb_substr($content, 0, $startOffset));
            foreach ($matches as $index => $match) {
                if ($match['start'] < $startByte) {
                    continue;
                }
                $before = mb_strlen(substr($content, 0, $match['start']));
                if ($this->getBooleanProperty('skip')) {
                    $length = mb_strlen($match['text']);
                } else {
                    $replacement = $this->moddevtools->expandReplacement($replace, $match, $mode);
                    $newContent = $this->moddevtools->applyReplacements($content, $matches, $replace, $mode, [$index]);
                    $length = mb_strlen($replacement);
                }
                // The next search starts behind this match, if there is another match
                $offset = isset($matches[$index + 1]) ? $before + $length : 0;
                break;
            }
        }

        $this->setProperty($this->contentField, $newContent);
        $this->setProperty($this->nameField, $this->object->get($this->nameField));
        $this->setProperty('offset', $offset);

        return true;
    }

    /**
     * @return boolean
     */
    public function beforeSet()
    {
        $result = $this->prepare();
        if ($result !== true) {
            return $result;
        }

        return parent::beforeSet();
    }

    /**
     * @return array|string
     */
    public function cleanup()
    {
        $content = (string)$this->object->get($this->contentField);
        $matches = [];
        $pattern = $this->moddevtools->buildPattern($this->getProperty('search'), $this->searchOptions);
        if ($pattern !== false) {
            $found = $this->moddevtools->findMatches($content, $pattern);
            $matches = is_array($found) ? $found : [];
        }
        $object = [
            'id' => $this->object->get('id'),
            'name' => $this->object->get($this->nameField),
            'class' => $this->elementClass,
            'content' => $this->moddevtools->getSearchContent($content, $this->getProperty('search'), $this->getProperty('offset'), $this->searchOptions),
            'type' => $this->objectType,
            'offset' => $this->getProperty('offset'),
            'matches' => count($matches),
        ];

        return $this->success('', $object);
    }

}

return 'modDevToolsReplaceProcessor';
