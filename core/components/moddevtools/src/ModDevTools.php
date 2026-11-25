<?php
/**
 * ModDevTools
 *
 * Copyright 2014-2019 by Vitaly Kireev <kireevvit@gmail.com>
 * Copyright 2019-2026 by Thomas Jakobi <office@treehillstudio.com>
 *
 * @package moddevtools
 * @subpackage classfile
 */

namespace TreehillStudio\ModDevTools;

use modAccessibleObject;
use modChunk;
use modDevToolsLink;
use modElement;
use modResource;
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
    public $version = '1.4.0';

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

        $lexicon = $this->modx->getService('lexicon', 'modLexicon');
        $lexicon->load($this->namespace . ':default');

        $this->packageName = $this->modx->lexicon('moddevtools');

        $this->modx->addPackage($this->namespace, $this->getOption('modelPath'));

        // The service is created in the setup too, where no user or context is initialized
        $sudo = $this->modx->user && $this->modx->user->get('sudo');
        $checkPermissions = $this->modx->user && is_object($this->modx->context);

        // Add default options
        $this->options = array_merge($this->options, [
            'debug' => (bool)$this->getOption('debug', $options, false),
            'modxversion' => $modxversion['version'],
            'is_admin' => $this->modx->user && $this->modx->context && ($this->modx->user->get('sudo') || $modx->hasPermission('settings') || $modx->hasPermission($this->namespace . '_settings')),
            'accessRegenerate' => $sudo || $checkPermissions && $this->modx->hasPermission('system_perform_maintenance_tasks'),
            'viewChunk' => $sudo || $checkPermissions && $this->modx->hasPermission('view_chunk'),
            'saveChunk' => $sudo || $checkPermissions && $this->modx->hasPermission('save_chunk'),
            'viewTemplate' => $sudo || $checkPermissions && $this->modx->hasPermission('view_template'),
            'saveTemplate' => $sudo || $checkPermissions && $this->modx->hasPermission('save_template'),
            'viewSnippet' => $sudo || $checkPermissions && $this->modx->hasPermission('view_snippet'),
            'editSnippet' => $sudo || $checkPermissions && $this->modx->hasPermission('edit_snippet'),
            'saveSnippet' => $sudo || $checkPermissions && $this->modx->hasPermission('save_snippet'),
            'viewResource' => $sudo || $checkPermissions && $this->modx->hasPermission('view_document'),
            'saveResource' => $sudo || $checkPermissions && $this->modx->hasPermission('save_document'),
            'extractLines' => 6,
            'extractEllips' => '...',
            'extractSeparator' => '<br>',
            'extractQuantity' => 1,
            'pcreModifier' => 'u',
            'chunkSuffix' => '.chunk.tpl'
        ]);
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
     * The supported search modes
     *
     * - text: The search string is searched literally
     * - wildcard: `*` matches any text in a line and `?` any single character
     * - regex: The search string is a PCRE regular expression (without delimiters)
     */
    const SEARCH_MODES = ['text', 'wildcard', 'regex'];

    /**
     * Get the element types, that can be searched
     *
     * @return array
     */
    public function getSearchElementMap()
    {
        return [
            'modChunk' => [
                'nameField' => 'name',
                'content' => 'snippet',
                'type' => 'chunk',
                'view' => 'viewChunk',
                'save' => 'saveChunk',
            ],
            'modTemplate' => [
                'nameField' => 'templatename',
                'content' => 'content',
                'type' => 'template',
                'view' => 'viewTemplate',
                'save' => 'saveTemplate',
            ],
        ];
    }

    /**
     * Get the content of an element. The content of static elements is read from the file, since the file may
     * have been changed outside of MODX and the content in the database is only a copy.
     *
     * @param modElement $element
     * @param string $contentField The content field of the element
     * @return string
     */
    public function getElementContent($element, $contentField)
    {
        if ($element->isStatic()) {
            $file = $element->getSourceFile();
            if ($file && file_exists($file)) {
                return (string)$element->getFileContent();
            }
        }
        return (string)$element->get($contentField);
    }

    /**
     * Get the normalized search options from request properties
     *
     * @param array $properties
     * @return array
     */
    public function getSearchOptions($properties)
    {
        $true = [true, 1, '1', 'true'];
        $mode = isset($properties['mode']) && in_array($properties['mode'], self::SEARCH_MODES, true) ? $properties['mode'] : 'text';
        // Ignore case and whole words are only available for the plain text search
        $text = ($mode === 'text');
        return [
            'mode' => $mode,
            'ignoreCase' => $text && isset($properties['ignorecase']) && in_array($properties['ignorecase'], $true, true),
            'wholeWord' => $text && isset($properties['wholeword']) && in_array($properties['wholeword'], $true, true),
        ];
    }

    /**
     * Build a PCRE pattern from the search string and the search options
     *
     * @param string $search The search string
     * @param array $options The search options, see getSearchOptions()
     * @param string $error Receives the lexicon key of the error, if the pattern is not usable
     * @return string|false The pattern or false, if the pattern is not usable
     */
    public function buildPattern($search, $options = [], &$error = '')
    {
        $error = '';
        $search = (string)$search;
        $mode = isset($options['mode']) ? $options['mode'] : 'text';
        if ($search === '') {
            $error = 'moddevtools.err_pattern_empty';
            return false;
        }

        switch ($mode) {
            case 'wildcard':
                $parts = preg_split('/([*?])/', $search, -1, PREG_SPLIT_DELIM_CAPTURE);
                $body = '';
                foreach ($parts as $part) {
                    if ($part === '*') {
                        $body .= '[^\r\n]*?';
                    } elseif ($part === '?') {
                        $body .= '[^\r\n]';
                    } else {
                        $body .= preg_quote($part, "\x01");
                    }
                }
                break;
            case 'regex':
                $body = str_replace("\x01", '', $search);
                break;
            default:
                $body = preg_quote($search, "\x01");
        }
        if (!empty($options['wholeWord'])) {
            $body = '(?<!\w)(?:' . $body . ')(?!\w)';
        }

        $modifiers = (string)$this->getOption('pcreModifier', [], 'u');
        if (!empty($options['ignoreCase'])) {
            $modifiers .= 'i';
        }
        if ($mode === 'regex') {
            $modifiers .= 'm';
        }
        $pattern = "\x01" . $body . "\x01" . $modifiers;

        // Check the pattern and that it doesn't match an empty text
        $warning = '';
        set_error_handler(function ($no, $str) use (&$warning) {
            $warning = $str;
            return true;
        });
        $result = preg_match($pattern, '');
        restore_error_handler();
        if ($result === false || $warning !== '') {
            $this->debug('Invalid pattern ' . $pattern . ': ' . $warning);
            $error = 'moddevtools.err_pattern_invalid';
            return false;
        }
        if ($result === 1) {
            $error = 'moddevtools.err_pattern_empty';
            return false;
        }

        return $pattern;
    }

    /**
     * Run preg_match_all with a pattern and retry without the unicode modifier, if the text is not valid UTF-8
     *
     * @param string $pattern
     * @param string $text
     * @param array $matches
     * @param int $flags
     * @return int|false
     */
    protected function pregAll($pattern, $text, &$matches, $flags = PREG_OFFSET_CAPTURE)
    {
        $count = @preg_match_all($pattern, $text, $matches, $flags);
        if ($count === false && preg_last_error() === PREG_BAD_UTF8_ERROR) {
            $position = strrpos($pattern, "\x01");
            $pattern = substr($pattern, 0, $position + 1) . str_replace('u', '', substr($pattern, $position + 1));
            $count = @preg_match_all($pattern, $text, $matches, $flags);
        }
        return $count;
    }

    /**
     * Find all matches of a pattern in a content. The positions are byte offsets.
     *
     * @param string $content
     * @param string $pattern
     * @return array|false List of matches with start, end, text and groups or false on a regex error
     */
    public function findMatches($content, $pattern)
    {
        $sets = [];
        if ($this->pregAll($pattern, $content, $sets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) === false) {
            return false;
        }
        $matches = [];
        foreach ($sets as $set) {
            if ($set[0][0] === '') {
                continue;
            }
            $groups = [];
            foreach ($set as $group) {
                $groups[] = $group[0];
            }
            $matches[] = [
                'start' => $set[0][1],
                'end' => $set[0][1] + strlen($set[0][0]),
                'text' => $set[0][0],
                'groups' => $groups,
            ];
        }
        return $matches;
    }

    /**
     * Get the replacement for a match. In the regex mode the groups can be used with $1, ${1} or \1.
     *
     * @param string $replace
     * @param array $match
     * @param string $mode
     * @return string
     */
    public function expandReplacement($replace, $match, $mode = 'text')
    {
        if ($mode !== 'regex') {
            return $replace;
        }
        $groups = $match['groups'];
        return preg_replace_callback('/\\\\(\\\\|n|t|\d{1,2})|\$\{(\d{1,2})\}|\$(\d{1,2})/', function ($m) use ($groups) {
            if ($m[1] === '\\') {
                return '\\';
            } elseif ($m[1] === 'n') {
                return "\n";
            } elseif ($m[1] === 't') {
                return "\t";
            }
            $index = (int)implode('', array_slice($m, 1));
            return isset($groups[$index]) ? $groups[$index] : '';
        }, $replace);
    }

    /**
     * Replace matches in a content
     *
     * @param string $content
     * @param array $matches The matches from findMatches()
     * @param string $replace
     * @param string $mode
     * @param array|null $only The indexes of the matches to replace or null for all matches
     * @return string
     */
    public function applyReplacements($content, $matches, $replace, $mode = 'text', $only = null)
    {
        $result = '';
        $position = 0;
        foreach ($matches as $index => $match) {
            if ($only !== null && !in_array($index, $only, true)) {
                continue;
            }
            $result .= substr($content, $position, $match['start'] - $position) .
                $this->expandReplacement($replace, $match, $mode);
            $position = $match['end'];
        }
        return $result . substr($content, $position);
    }

    /**
     * Highlight the matches of a pattern in a text and escape the text for the HTML output
     *
     * @param string $text
     * @param string $pattern
     * @return string
     */
    protected function highlightText($text, $pattern)
    {
        $output = '';
        $position = 0;
        $matches = [];
        if ($this->pregAll($pattern, $text, $matches) !== false) {
            foreach ($matches[0] as $match) {
                if ($match[0] === '') {
                    continue;
                }
                $class = ($this->options['highlightCount'] == 0) ? 'first-string' : 'found-string';
                $this->options['highlightCount']++;
                $output .= $this->escape(substr($text, $position, $match[1] - $position)) .
                    '<span class="' . $class . '">' . $this->escape($match[0]) . '</span>';
                $position = $match[1] + strlen($match[0]);
            }
        }
        return $output . $this->escape(substr($text, $position));
    }

    /**
     * @param string $text
     * @return string
     */
    protected function escape($text)
    {
        return htmlspecialchars($text, ENT_COMPAT | ENT_SUBSTITUTE);
    }

    /**
     * @param string $content The element content
     * @param string $search The search string
     * @param int $offset The character offset in the content, where the next match is searched
     * @param array $options The search options, see getSearchOptions()
     * @return string
     */
    public function getSearchContent($content, $search, $offset = 0, $options = [])
    {
        $pattern = $this->buildPattern($search, $options);
        if ($pattern === false) {
            return '';
        }

        // The offset is a character offset into the raw content, so it is translated to the cleaned content
        $offset = max(0, (int)$offset);
        $offsetString = str_replace(["\r\n", "\r"], "\n", mb_substr($content, 0, $offset));
        $content = $offsetString . str_replace(["\r\n", "\r"], "\n", mb_substr($content, $offset));
        $offset = mb_strlen($offsetString);
        $lineLeftString = '';

        if ($offset) {
            $this->options['highlightCount'] = 1;
            $matches = [];
            $count = $this->pregAll($pattern, $content, $matches);

            if ($count > 1) {
                $linesLeft = $this->mb_strrposnth(mb_substr($offsetString, 0, $offset), "\n", $this->options['extractLines'] / 2);
                $lineLeft = mb_substr($offsetString, (int)$linesLeft);
                $lineLeftString = (($linesLeft) ? $this->options['extractEllips'] . "\n" : '') .
                    $this->highlightText(ltrim($lineLeft, "\n"), $pattern);
            } else {
                $offset = 0;
            }
        }

        return '<pre>' . $lineLeftString . $this->getExtract(mb_substr($content, $offset), $pattern) . '</pre>';
    }

    /**
     * Returns extracts with highlighted searchterms
     *
     * @param string $text
     * @param string $pattern
     * @return string
     */
    public function getExtract($text, $pattern)
    {
        $output = '';

        if (($text !== '') && ($pattern !== '')) {
            $extracts = [];

            $textLength = mb_strlen($text);

            // Collect matches
            $matches = [];
            $nbr = (int)$this->pregAll($pattern, $text, $matches);
            for ($i = 0; $i < $nbr && count($extracts) < $this->options['extractQuantity']; $i++) {
                if ($matches[0][$i][0] === '') {
                    continue;
                }
                // PREG_OFFSET_CAPTURE returns byte offsets
                $wordLength = mb_strlen($matches[0][$i][0]);
                $wordLeft = mb_strlen(substr($text, 0, $matches[0][$i][1]));
                $wordRight = $wordLeft + $wordLength - 1;

                $linesLeft = $this->mb_strrposnth(mb_substr($text, 0, $wordLeft), "\n", $this->options['extractLines'] / 2);
                $left = ($linesLeft !== false) ? $linesLeft + 1 : 0;
                $left = ($left < 0) ? 0 : $left;
                $linesRight = $this->mb_strposnth(mb_substr($text, $wordRight), "\n", $this->options['extractLines'] / 2);
                $right = ($linesRight !== false) ? $wordRight + $linesRight - 1 : $textLength;
                $right = ($right > $textLength) ? $textLength : $right;
                $extracts[] = [
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

                $extract = $this->highlightText(mb_substr($text, $extracts[$i]['left'], $extracts[$i]['right'] - $extracts[$i]['left'] + 1), $pattern);

                $output .= $extracts[$i]['etcLeft'] . $extract . $extracts[$i]['etcRight'] . $separation;
            }
        }
        return $output;
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

    /**
     * Get the options used by the manager scripts, so that no server paths are output in the manager
     *
     * @param array|null $options Options to filter instead of the class options
     * @return array
     */
    public function getManagerOptions(?array $options = null): array
    {
        return array_intersect_key($options ?? $this->options, array_flip([
            'assetsUrl',
            'connectorUrl',
            'debug',
            'is_admin',
            'modxversion',
            'saveChunk',
            'saveResource',
            'saveSnippet',
            'saveTemplate',
            'version',
        ]));
    }
}
