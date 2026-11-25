<?php
/**
 * Preview the replacements of a search in an element
 */

use TreehillStudio\ModDevTools\Processors\Processor;

/**
 * Class modDevToolsSearchPreviewProcessor
 */
class modDevToolsSearchPreviewProcessor extends Processor
{
    /** The maximum number of listed matches */
    const MAX_MATCHES = 500;
    /** The maximum number of characters shown left and right of a match and of the match itself */
    const CONTEXT_LENGTH = 40;
    const MATCH_LENGTH = 300;

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function checkPermissions()
    {
        $map = $this->moddevtools->getSearchElementMap();
        $class = $this->getProperty('class');
        return isset($map[$class]) && $this->moddevtools->getOption($map[$class]['view']);
    }

    /**
     * {@inheritDoc}
     * @return array|string
     */
    public function process()
    {
        $map = $this->moddevtools->getSearchElementMap();
        $class = $this->getProperty('class');
        $element = $this->modx->getObject($class, (int)$this->getProperty('id'));
        if (!$element) {
            return $this->failure($this->modx->lexicon('moddevtools.err_element_nf'));
        }

        $options = $this->moddevtools->getSearchOptions($this->getProperties());
        $pattern = $this->moddevtools->buildPattern($this->getProperty('search'), $options, $error);
        if ($pattern === false) {
            return $this->failure($this->modx->lexicon($error));
        }

        $replace = (string)$this->getProperty('replace', '');
        $content = $this->moddevtools->getElementContent($element, $map[$class]['content']);
        $matches = $this->moddevtools->findMatches($content, $pattern);
        if ($matches === false) {
            return $this->failure($this->modx->lexicon('moddevtools.err_pattern_limit'));
        }

        $list = [];
        foreach ($matches as $index => $match) {
            if ($index >= self::MAX_MATCHES) {
                break;
            }
            $lineStart = strrpos(substr($content, 0, $match['start']), "\n");
            $lineStart = ($lineStart === false) ? 0 : $lineStart + 1;
            $lineEnd = strpos($content, "\n", $match['end']);
            $lineEnd = ($lineEnd === false) ? strlen($content) : $lineEnd;
            $list[] = [
                'index' => $index,
                'line' => substr_count($content, "\n", 0, $match['start']) + 1,
                'before' => $this->cut(rtrim(substr($content, $lineStart, $match['start'] - $lineStart), "\r"), self::CONTEXT_LENGTH, true),
                'match' => $this->cut($match['text'], self::MATCH_LENGTH),
                'replacement' => $this->cut($this->moddevtools->expandReplacement($replace, $match, $options['mode']), self::MATCH_LENGTH),
                'after' => $this->cut(rtrim(substr($content, $match['end'], $lineEnd - $match['end']), "\r"), self::CONTEXT_LENGTH),
            ];
        }

        return $this->success('', [
            'hash' => md5($content),
            'name' => $element->get($map[$class]['nameField']),
            'total' => count($matches),
            'shown' => count($list),
            'matches' => $list,
        ]);
    }

    /**
     * Shorten a text to a maximum number of characters
     *
     * @param string $text
     * @param int $length
     * @param bool $left Cut at the left side
     * @return string
     */
    protected function cut($text, $length, $left = false)
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return $left ? '…' . mb_substr($text, -$length) : mb_substr($text, 0, $length) . '…';
    }
}

return 'modDevToolsSearchPreviewProcessor';
