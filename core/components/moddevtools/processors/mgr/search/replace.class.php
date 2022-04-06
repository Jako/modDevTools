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
 */
class modDevToolsReplaceProcessor extends modElementUpdateProcessor
{
    public $nameField = 'name';
    public $contentField = 'content';

    /** @var ModDevTools */
    public $moddevtools;

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
        $this->classKey = $this->getProperty('class');
        switch ($this->classKey) {
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

        return parent::run();
    }

    /**
     * @return boolean
     */
    public function beforeSet()
    {
        $search = $this->getProperty('search', '');
        $replace = $this->getProperty('replace', '');
        $all = ($this->getProperty('all', 'false') === 'true' || $this->getProperty('all', false) === true);

        $content = $this->object->getContent();

        if ($all) {
            $content = str_replace($search, $replace, $content);
            $offset = 0;
        } else {
            $offset = (int)$this->getProperty('offset', 0);
            $offsetString = substr($content, 0, $offset);
            $newContent = substr($content, $offset);
            $strings = explode($search, $newContent, 2);
            $newContent = implode($replace, $strings);

            if (strpos($strings[1], $search) === false) {
                $offset = 0;
            } else {
                $offset = $offset + strlen($strings[0]) + strlen($replace);
            }
            $content = $offsetString . $newContent;
        }
        $this->setProperty($this->contentField, $content);
        $this->setProperty($this->nameField, $this->object->get($this->nameField));
        $this->setProperty('offset', $offset);

        return parent::beforeSet();
    }

    /**
     * @return array|string
     */
    public function cleanup()
    {
        $object = [
            'id' => $this->object->get('id'),
            'name' => $this->object->get($this->nameField),
            'class' => $this->classKey,
            'content' => $this->moddevtools->getSearchContent($this->object->get($this->contentField), $this->getProperty('search'), $this->getProperty('offset')),
            'type' => $this->objectType,
            'offset' => $this->getProperty('offset'),
        ];

        return $this->success('', $object);
    }

}

return 'modDevToolsReplaceProcessor';