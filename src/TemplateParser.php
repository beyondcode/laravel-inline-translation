<?php

namespace BeyondCode\InlineTranslation;

use Exception;

class TemplateParser
{
    const DATA_TRANSLATE = 'data-translate';

    /**
     * @var array
     */
    private $viewData;

    /**
     * List of simple tags
     *
     * @var array
     */
    protected $allowedTags = [
        'legend' => 'Caption for the fieldset element',
        'label' => 'Label for an input element.',
        'button' => 'Push button',
        'a' => 'Link label',
        'b' => 'Bold text',
        'strong' => 'Strong emphasized text',
        'i' => 'Italic text',
        'em' => 'Emphasized text',
        'u' => 'Underlined text',
        'sup' => 'Superscript text',
        'sub' => 'Subscript text',
        'span' => 'Span element',
        'small' => 'Smaller text',
        'big' => 'Bigger text',
        'address' => 'Contact information',
        'blockquote' => 'Long quotation',
        'q' => 'Short quotation',
        'cite' => 'Citation',
        'caption' => 'Table caption',
        'abbr' => 'Abbreviated phrase',
        'acronym' => 'An acronym',
        'var' => 'Variable part of a text',
        'dfn' => 'Term',
        'strike' => 'Strikethrough text',
        'del' => 'Deleted text',
        'ins' => 'Inserted text',
        'h1' => 'Heading level 1',
        'h2' => 'Heading level 2',
        'h3' => 'Heading level 3',
        'h4' => 'Heading level 4',
        'h5' => 'Heading level 5',
        'h6' => 'Heading level 6',
        'center' => 'Centered text',
        'select' => 'List options',
        'img' => 'Image',
        'input' => 'Form element',
    ];

    /**
     * TemplateParser constructor.
     * @param array $viewData
     */
    public function __construct(array $viewData = [])
    {
        $this->viewData = $viewData;
    }

    public function parseTranslationTags(string &$viewContent)
    {
        $nextTag = 0;

        $tags = implode('|', array_keys($this->allowedTags));

        $tagRegExp = '#<(' . $tags . ')(/?>| \s*[^>]*+/?>)#iSU';

        $tagMatch = [];

        while (preg_match($tagRegExp, $viewContent, $tagMatch, PREG_OFFSET_CAPTURE, $nextTag)) {
            $tagName = strtolower($tagMatch[1][0]);

            if (substr($tagMatch[0][0], -2) == '/>') {
                $tagClosurePos = $tagMatch[0][1] + strlen($tagMatch[0][0]);
            } else {
                $tagClosurePos = $this->findEndOfTag($viewContent, $tagName, $tagMatch[0][1]);
            }

            if ($tagClosurePos === false) {
                $nextTag += strlen($tagMatch[0][0]);
                continue;
            }

            $tagLength = $tagClosurePos - $tagMatch[0][1];
            $tagStartLength = strlen($tagMatch[0][0]);

            $tagHtml = $tagMatch[0][0] . substr(
                    $viewContent,
                    $tagMatch[0][1] + $tagStartLength,
                    $tagLength - $tagStartLength
                );

            $tagClosurePos = $tagMatch[0][1] + strlen($tagHtml);

            $trArr = $this->getTranslateData(
                '/({{|{!!)\s*(__|\@lang|\@trans|\@choice|trans|trans_choice)\((.*?)\)\s*(}}|!!})/m',
                $tagHtml,
                ['tagName' => $tagName, 'tagList' => $this->allowedTags]
            );

            if (!empty($trArr)) {
                $trArr = array_unique($trArr);

                $tagHtml = $this->applyTranslationTags($tagHtml, $tagName, $trArr);

                $tagClosurePos = $tagMatch[0][1] + strlen($tagHtml);
                $viewContent = substr_replace($viewContent, $tagHtml, $tagMatch[0][1], $tagLength);
            }

            $nextTag = $tagClosurePos;
        }
    }

    /**
     * Format translation for simple tags.  Added translate mode attribute for vde requests.
     *
     * @param string $tagHtml
     * @param string $tagName
     * @param array $trArr
     * @return string
     */
    protected function applyTranslationTags($tagHtml, $tagName, $trArr)
    {
        $simpleTags = substr(
                $tagHtml,
                0,
                strlen($tagName) + 1
            ) . ' ' . $this->getHtmlAttribute(
                self::DATA_TRANSLATE,
                htmlspecialchars('[' . join(',', $trArr) . ']')
            );

        $simpleTags .= substr($tagHtml, strlen($tagName) + 1);
        return $simpleTags;
    }

    /**
     * Get html element attribute
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    private function getHtmlAttribute($name, $value)
    {
        return $name . '="' . $value . '"';
    }

    /**
     * Get translate data by regexp
     *
     * @param string $regexp
     * @param string $text
     * @param array $options
     * @return array
     */
    private function getTranslateData($regexp, $text, $options = [])
    {
        $trArr = [];
        $nextRegexOffset = 0;
        while (preg_match($regexp, $text, $matches, PREG_OFFSET_CAPTURE, $nextRegexOffset)) {

            extract($this->viewData, EXTR_OVERWRITE);
            $translationParameters = $this->extractVariablesFromLocalizationParameters($matches[3][0]);

            try {
                $translated = eval('return ' . $matches[2][0] . '(' . $matches[3][0] . ');');
            } catch (Exception $e) {
                $translated = '';
            }

            $trArr[] = json_encode(
                [
                    'translated' => $translated,
                    'original' => $translationParameters[0],
                    'parameters' => $translationParameters[1] ?? [],
                    'location' => htmlspecialchars_decode($this->getTagLocation($matches, $options)),
                ]
            );
            $nextRegexOffset = $matches[4][1];
        }
        return $trArr;
    }

    /**
     * Get tag location
     *
     * @param array $matches
     * @param array $options
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getTagLocation($matches, $options)
    {
        $tagName = strtolower($options['tagName']);
        if (isset($options['tagList'][$tagName])) {
            return $options['tagList'][$tagName];
        }
        return ucfirst($tagName) . ' Text';
    }

    private function _returnViewData()
    {
        $args = func_get_args();
        $return = [];

        foreach ($args as $argument) {
            $return[] = $argument;
        }

        return $return;
    }

    /**
     * @param string $localizationParameters
     * @return array|mixed
     */
    private function extractVariablesFromLocalizationParameters($localizationParameters)
    {
        try {
            extract($this->viewData, EXTR_OVERWRITE);
            return eval('return $this->_returnViewData(' . $localizationParameters . ');');
        } catch (Exception $e) {
            return [
                $localizationParameters
            ];
        }
    }

    /**
     * Find end of tag
     *
     * @param string $body
     * @param string $tagName
     * @param int $from
     * @return bool|int return false if end of tag is not found
     */
    private function findEndOfTag($body, $tagName, $from)
    {
        $openTag = '<' . $tagName;
        $closeTag = '</' . $tagName;
        $tagLength = strlen($tagName);
        $length = $tagLength + 1;
        $end = $from + 1;

        while (substr_count($body, $openTag, $from, $length) !== substr_count($body, $closeTag, $from, $length)) {
            $end = strpos($body, $closeTag, $end + $tagLength + 1);
            if ($end === false) {
                return false;
            }
            $length = $end - $from + $tagLength + 3;
        }

        if (preg_match('#<\\\\?\/' . $tagName . '\s*?>#i', $body, $tagMatch, null, $end)) {
            return $end + strlen($tagMatch[0]);
        }
        return false;
        
    }
}
