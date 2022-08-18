<?php
/**
 * ModDevTools
 *
 * Copyright 2014-2019 by Vitaly Kireev <kireevvit@gmail.com>
 * Copyright 2019-2022 by Thomas Jakobi <office@treehillstudio.com>
 *
 * @package moddevtools
 * @subpackage classfile
 */

namespace TreehillStudio\ModDevTools;

use modAccessibleObject;
use modChunk;
use modDevToolsLink;
use modResource;
use modSystemEvent;
use modTemplate;
use modX;
use xPDO;
use xPDOObject;

/**
 * Class ModDevTools
 */
class ModDevTools
{
    /**
     * A reference to the modX instance
     * @var modX $modx
     */
    public $modx;

    /**
     * The namespace
     * @var string $namespace
     */
    public $namespace = 'moddevtools';

    /**
     * The package name
     * @var string $packageName
     */
    public $packageName = 'ModDevTools';

    /**
     * The version
     * @var string $version
     */
    public $version = '1.3.5';

    /**
     * The class options
     * @var array $options
     */
    public $options = [];

    /**
     * ModDevTools constructor
     *
     * @param modX $modx A reference to the modX instance.
     * @param array $options An array of options. Optional.
     */
    public function __construct(modX &$modx, $options = [])
    {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $options, $this->namespace);

        $corePath = $this->getOption('core_path', $options, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/' . $this->namespace . '/');
        $assetsPath = $this->getOption('assets_path', $options, $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/' . $this->namespace . '/');
        $assetsUrl = $this->getOption('assets_url', $options, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/' . $this->namespace . '/');
        $modxversion = $this->modx->getVersionData();

        // Load some default paths for easier management
        $this->options = array_merge([
            'namespace' => $this->namespace,
            'version' => $this->version,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'vendorPath' => $corePath . 'vendor/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'pagesPath' => $corePath . 'elements/pages/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'pluginsPath' => $corePath . 'elements/plugins/',
            'controllersPath' => $corePath . 'controllers/',
            'processorsPath' => $corePath . 'processors/',
            'templatesPath' => $corePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'imagesUrl' => $assetsUrl . 'images/',
            'connectorUrl' => $assetsUrl . 'connector.php'
        ], $options);

        // Add default options
        $this->options = array_merge($this->options, [
            'debug' => (bool)$this->getOption('debug', $options, false),
            'modxversion' => $modxversion['version'],
            'is_admin' => $this->modx->user && ($this->modx->user->get('sudo') || $modx->hasPermission('settings') || $modx->hasPermission($this->namespace . '_settings')),
            'accessRegenerate' => $this->modx->user->get('sudo') || $this->modx->hasPermission('system_perform_maintenance_tasks'),
            'viewChunk' => $this->modx->user->get('sudo') || $this->modx->hasPermission('view_chunk'),
            'saveChunk' => $this->modx->user->get('sudo') || $this->modx->hasPermission('save_chunk'),
            'viewTemplate' => $this->modx->user->get('sudo') || $this->modx->hasPermission('view_template'),
            'saveTemplate' => $this->modx->user->get('sudo') || $this->modx->hasPermission('save_template'),
            'viewSnippet' => $this->modx->user->get('sudo') || $this->modx->hasPermission('view_snippet'),
            'editSnippet' => $this->modx->user->get('sudo') || $this->modx->hasPermission('edit_snippet'),
            'saveSnippet' => $this->modx->user->get('sudo') || $this->modx->hasPermission('save_snippet'),
            'viewResource' => $this->modx->user->get('sudo') || $this->modx->hasPermission('view_document'),
            'saveResource' => $this->modx->user->get('sudo') || $this->modx->hasPermission('save_document'),
            'extractLines' => 6,
            'extractEllips' => '...',
            'extractSeparator' => '<br>',
            'extractQuantity' => 1,
            'pcreModifier' => 'u',
            'chunkSuffix' => '.chunk.tpl'
        ]);

        $this->modx->addPackage($this->namespace, $this->getOption('modelPath'));

        $lexicon = $this->modx->getService('lexicon', 'modLexicon');
        $lexicon->load($this->namespace . ':default');
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption($key, $options = [], $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("$this->namespace.$key", $this->modx->config)) {
                $option = $this->modx->getOption("$this->namespace.$key");
            }
        }
        return $option;
    }

    /**
     * @param bool|string $link_type
     * @param bool|int $parent
     */
    public function clearLinks($link_type = false, $parent = false)
    {
        $c = $this->modx->newQuery('modDevToolsLink');
        if ($link_type) {
            $c->where([
                'link_type:LIKE' => $link_type . '-%'
            ]);
        }
        if ($parent) {
            $c->where([
                'parent' => $parent
            ]);
        }
        /** @var modDevToolsLink[] $links */
        $links = $this->modx->getIterator('modDevToolsLink', $c);
        foreach ($links as $link) {
            $link->remove();
        }
        // Reset autoincrement
        $this->modx->query('ALTER TABLE ' . $this->modx->getTableName('modDevToolsLink') . ' AUTO_INCREMENT = 0;');
    }

    /**
     * @param modAccessibleObject $object
     * @return bool|string
     */
    public function getLinkParentType($object)
    {
        if ($object instanceof modTemplate) {
            return 'temp';
        } elseif ($object instanceof modChunk) {
            return 'chunk';
        } elseif ($object instanceof modResource) {
            return 'res';
        } else {
            return false;
        }
    }

    /**
     * @param string $content
     * @param array $tags
     */
    public function findTags($content, &$tags)
    {
        $parser = $this->modx->getParser();
        $collectedTags = [];
        $parser->collectElementTags($content, $collectedTags);
        foreach ($collectedTags as $tag) {
            $tagName = $tag[1];
            if (substr($tagName, 0, 1) == '!') {
                $tagName = substr($tagName, 1);
            }

            $token = substr($tagName, 0, 1);

            $tagParts = xPDO::escSplit('?', $tagName, '`', 2);
            $tagName = trim($tagParts[0]);

            $tagName = trim($this->modx->stripTags($tagName));
            if (in_array($token, ['$', '+', '~', '#', '%', '-', '*'])) {
                $tagName = substr($tagName, 1);
            }

            switch ($token) {
                case '$':
                    $class = 'modChunk';
                    break;
                case '+':
                case '~':
                case '#':
                case '%':
                case '-':
                case '*':
                    continue 2;
                default:
                    $class = 'modSnippet';
                    break;
            }

            if (isset($tagParts[1])) {
                $tagPropString = trim($tagParts[1]);
                $this->findTags($tagPropString, $tags);
                $element = $parser->getElement($class, $tagName);
                if ($element) {
                    $properties = $element->getProperties($tagPropString);
                } else {
                    $properties = [];
                }
            } else {
                $properties = [];
            }

            $this->debug('Found ' . $class . ' ' . $tagName . ' with properties ' . print_r($properties, 1));

            $tagName = $parser->realname($tagName);
            if (empty($tagName)) {
                continue;
            }

            $tags[$tagName] = [
                'name' => $tagName,
                'class' => $class,
            ];

            foreach ($properties as $property) {
                $prop = trim($property);
                if (!empty($prop) && !is_numeric($prop) && is_string($prop)) {
                    $tags[$prop] = [
                        'name' => $prop,
                        'class' => 'modChunk',
                        'isProperty' => true,
                    ];
                }
            }
        }
    }

    /**
     * @param modAccessibleObject $object
     */
    public function parseContent(&$object)
    {
        $t = microtime(true);
        $objLink = $this->getLinkParentType($object);
        if ($objLink === false) {
            return;
        }

        $this->clearLinks($objLink, $object->get('id'));

        $tags = [];
        $this->findTags($object->get('content'), $tags);
        $this->debug('All found tags: ' . print_r($tags, 1));
        foreach ($tags as $tag) {
            $this->findLink($object, $tag, $objLink);
        }
        $this->debug('Total time: ' . (microtime(true) - $t));
    }

    /**
     * @param xPDOObject $parent
     * @param array $tag
     * @param string $linkType
     */
    public function findLink($parent, $tag, $linkType)
    {
        if (isset($tag['class'], $tag['name'])) {
            switch ($tag['class']) {
                case 'modSnippet':
                    $type = 'snip';
                    break;
                case 'modChunk':
                    $type = 'chunk';
                    break;
                default:
                    return;
            }
            /** @var bool|xPDOObject $child */
            $child = $this->findObject($tag['class'], $tag['name']);
            if ($child !== false) {
                $this->createLink($parent, $child, $linkType . '-' . $type);
            }
        }
    }

    /**
     * @param $parent xPDOObject
     * @param $child xPDOObject
     * @param $linkType
     */
    public function createLink($parent, $child, $linkType)
    {
        $c = [
            'parent' => $parent->get('id'),
            'child' => $child->get('id'),
            'link_type' => $linkType,
        ];
        $link = $this->modx->getObject('modDevToolsLink', $c);

        if (!$link) {
            $this->debug('Try to create link with criteria ' . print_r($c, 1));
            $link = $this->modx->newObject('modDevToolsLink', $c);
            $link->save();
        } else {
            $this->debug('Link is already exists with criteria ' . print_r($c, 1));
        }
    }

    /**
     * @param string $class
     * @param string $name
     * @return bool|null|object
     */
    public function findObject($class, $name)
    {
        if (!empty($class) && !empty($name)) {
            $obj = $this->modx->getObject($class, ['name' => $name]);
            if (!empty($obj)) {
                $this->debug('Object exists of class ' . $class);
                return $obj;
            } else {
                $this->debug('Object doesnt exist of class ' . $class);
                return false;
            }
        }
        return false;
    }

    /**
     * @param $content
     * @param $search
     * @param int $offset
     * @return string
     */
    public function getSearchContent($content, $search, $offset = 0)
    {
        // clean content
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        $offsetString = substr($content, 0, $offset);
        $lineLeftString = '';

        if ($offset) {
            $this->options['highlightCount'] = 1;
            $pattern = '/' . preg_quote($search, '/') . '/' . $this->options['pcreModifier'];
            $count = preg_match_all($pattern, $content);

            if ($count > 1) {
                $linesLeft = $this->mb_strrposnth(mb_substr($offsetString, 0, $offset), "\n", $this->options['extractLines'] / 2);
                $lineLeftString = (($linesLeft) ? $this->options['extractEllips'] . "\n" : '') . ltrim(mb_substr($offsetString, $linesLeft), "\n");
                $lineLeftString = preg_replace_callback($pattern, [$this, 'highlight'], $lineLeftString);
            } else {
                $offset = 0;
            }
        }

        return '<pre>' . $lineLeftString . $this->getExtract(substr($content, $offset), $search) . '</pre>';
    }

    /**
     * Returns extracts with highlighted searchterms
     *
     * @param string $text
     * @param string $searchString
     * @return string
     */
    function getExtract($text, $searchString)
    {
        $output = '';

        if (($text !== '') && ($searchString !== '')) {
            $extracts = [];

            $textLength = mb_strlen($text);
            $wordLength = mb_strlen($searchString);

            $pattern = '/' . preg_quote($searchString, '/') . '/' . $this->options['pcreModifier'];

            // Collect matches
            $matches = [];
            $nbr = preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
            for ($i = 0; $i < $nbr && $i < $this->options['extractQuantity']; $i++) {
                $wordLeft = mb_strlen(mb_substr($text, 0, $matches[0][$i][1]));
                $wordRight = $wordLeft + $wordLength - 1;

                $linesLeft = $this->mb_strrposnth(mb_substr($text, 0, $wordLeft), "\n", $this->options['extractLines'] / 2);
                $left = ($linesLeft !== false) ? $linesLeft + 1 : 0;
                $left = ($left < 0) ? 0 : $left;
                $linesRight = $this->mb_strposnth(mb_substr($text, $wordRight), "\n", $this->options['extractLines'] / 2);
                $right = ($linesRight !== false) ? $wordRight + $linesRight - 1 : $textLength;
                $right = ($right > $textLength) ? $textLength : $right;
                $extracts[] = [
                    'word' => $searchString,
                    'wordLeft' => $wordLeft,
                    'wordRight' => $wordRight,
                    'left' => $left,
                    'right' => $right,
                    'etcLeft' => ($left == 0) ? '' : $this->options['extractEllips'] . "\n",
                    'etcRight' => ($right == $textLength) ? '' : "\n" . $this->options['extractEllips']
                ];
            }

            // Join overlapping extracts
            $nbExtr = count($extracts);
            if ($nbExtr > 1) {
                for ($i = 0; $i < $nbExtr; $i++) {
                    $lft[$i] = $extracts[$i]['left'];
                    $rght[$i] = $extracts[$i]['right'];
                }
                array_multisort($lft, SORT_ASC, $rght, SORT_ASC, $extracts);
                for ($i = 0; $i < $nbExtr; $i++) {
                    $begin = mb_substr($text, 0, $extracts[$i]['left']);
                    if ($begin != '') {
                        $extracts[$i]['left'] = intval(mb_strrpos($begin, ' '));
                    }
                    $end = mb_substr($text, $extracts[$i]['right'] + 1, $textLength - $extracts[$i]['right']);
                    if ($end != '') {
                        $dr = intval(mb_strpos($end, ' '));
                        $extracts[$i]['right'] += $dr + 1;
                    }
                }
                for ($i = 1; $i < $nbExtr; $i++) {
                    if ($extracts[$i]['left'] < $extracts[$i - 1]['wordRight']) {
                        $extracts[$i - 1]['right'] = $extracts[$i - 1]['wordRight'];
                        $extracts[$i]['left'] = $extracts[$i - 1]['right'] + 1;
                        $extracts[$i - 1]['etcRight'] = $extracts[$i]['etcLeft'] = '';
                    } else {
                        if ($extracts[$i]['left'] < $extracts[$i - 1]['right']) {
                            $extracts[$i - 1]['right'] = $extracts[$i]['left'];
                            $extracts[$i - 1]['etcRight'] = $extracts[$i]['etcLeft'] = '';
                        }
                    }
                }
            }

            // Highlight extracts
            for ($i = 0; $i < $nbExtr; $i++) {
                $this->options['highlightCount'] = 0;
                $separation = ($extracts[$i]['etcRight'] != '') ? $this->options['extractSeparator'] : '';

                $extract = htmlspecialchars(mb_substr($text, $extracts[$i]['left'], $extracts[$i]['right'] - $extracts[$i]['left'] + 1));
                $extract = preg_replace_callback($pattern, [$this, 'highlight'], $extract);

                $output .= $extracts[$i]['etcLeft'] . $extract . $extracts[$i]['etcRight'] . $separation;
            }
        }
        return $output;
    }

    /**
     * @param array $matches
     * @return string
     */
    private function highlight(array $matches)
    {
        $class = ($this->options['highlightCount'] == 0) ? 'first-string' : 'found-string';
        $this->options['highlightCount']++;
        return '<span class="' . $class . '">' . $matches[0] . '</span>';
    }

    /**
     * @param string $message
     */
    public function debug($message)
    {
        if ($this->options['debug']) {
            if ($message instanceof xPDOObject) {
                $message = $message->toArray();
            }
            if (is_array($message)) {
                $message = print_r($message, 1);
            }
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, $message);
        }
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param int $nth
     * @return bool|int
     */
    function mb_strposnth($haystack, $needle, $nth = 1)
    {
        $count = mb_substr_count($haystack, $needle);
        if ($count < 1 || $nth > $count) {
            return false;
        }
        for ($i = 0, $pos = 0, $len = 0; $i < $nth; $i++) {
            $pos = mb_strpos($haystack, $needle, $pos + $len);
            if ($i == 0) {
                $len = mb_strlen($needle);
            }
        }
        return $pos;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param int $nth
     * @return bool|int
     */
    function mb_strrposnth($haystack, $needle, $nth = 1)
    {
        $count = mb_substr_count($haystack, $needle);
        if ($count < 1 || $nth > $count) {
            return false;
        }
        for ($i = 0, $pos = $hlen = mb_strlen($haystack), $len = 0; $i < $nth; $i++) {
            $test = $hlen - ($pos - $len);
            $pos = mb_strrpos($haystack, $needle, -$test);
            if ($i == 0) {
                $len = mb_strlen($needle);
            }
        }
        return $pos;
    }
}
