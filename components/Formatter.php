<?php

namespace app\components;

use yii\helpers\HtmlPurifier;
use yii\helpers\Markdown;

class Formatter extends \yii\i18n\Formatter
{
    public $purifierConfig = [
        'HTML' => [
            'AllowedElements' => [
                'div',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'strong', 'em', 'b', 'i', 'u', 's', 'span',
                'pre', 'code',
                'table', 'tr', 'td', 'th',
                'a', 'p', 'br',
                'blockquote',
                'ul', 'ol', 'li',
                'img'
            ],
        ],
        'Attr' => [
            'EnableID' => true,
        ],
    ];

    public function asMarkdown($markdown)
    {
        $html = Markdown::process($markdown, 'gfm');
        $output = HtmlPurifier::process($html, $this->purifierConfig);
        return '<div class="markdown">' . $this->katex($output) . '</div>';
    }

    public function asHtml($value, $config = NULL)
    {
        $value = str_replace(array("<pre>", "</pre>"), array("<div class='pre'>", "</div>"), $value);
        $value = HtmlPurifier::process($value, $this->purifierConfig);
        return '<div class="html-output">' . $this->katex($value) . '</div>';
    }

    public function katex($content)
    {
        return $this->katex_markup_single($this->katex_markup_double($content));
    }

    public function katex_markup_single( $content ) {

        $regexTeXInline = '
		%
		\$
			((?:
				[^$]+ # Not a dollar
				|
				(?<=(?<!\\\\)\\\\)\$ # Dollar preceded by exactly one slash
				)+)
			(?<!\\\\)
		\$ # Dollar preceded by zero slashes
		%ix';

        $textarr = $this->wp_html_split( $content );

        $count = 0;
        $preg  = true;

        foreach ($textarr as &$element) {

            if ($count > 0) {
                ++ $count;
            }

            if ( htmlspecialchars_decode( $element ) == "<pre>" ) {
                $count = 1;
            }

            if ( $count == 3 && strpos( htmlspecialchars_decode( $element ), "<code class=" ) === 0 ) {
                $preg = false;
            }

            if ( htmlspecialchars_decode( $element ) == "</pre>" ) {
                $preg = true;
            }

            if ( ! $preg ) {
                continue;
            }

            if ( '' === $element || '<' === $element[0] ) {
                continue;
            }

            if ( false === stripos( $element, '$' ) ) {
                continue;
            }

            $element = preg_replace_callback( $regexTeXInline, array( $this, 'katex_src_inline' ), $element );
        }

        return implode( '', $textarr );
    }

    public function katex_src_inline( $matches ) {

        $katex = $matches[1];

        $katex = $this->katex_entity_decode_editormd( $katex );

        return '<span class="katex math inline">' . $katex . '</span>';
    }

    public function katex_markup_double( $content ) {

        $regexTeXInline = '
		%
		\$\$
			((?:
				[^$]+ # Not a dollar
				|
				(?<=(?<!\\\\)\\\\)\$ # Dollar preceded by exactly one slash
				)+)
			(?<!\\\\)
		\$\$ # Dollar preceded by zero slashes
		%ix';

        $textarr = $this->wp_html_split( $content );

        $count = 0;
        $preg  = true;

        foreach ( $textarr as &$element ) {

            if ( $count > 0 ) {
                ++ $count;
            }

            if ( htmlspecialchars_decode( $element ) == "<pre>" ) {
                $count = 1;
            }

            if ( $count == 3 && strpos( htmlspecialchars_decode( $element ), "<code class=" ) === 0 ) {
                $preg = false;
            }

            if ( htmlspecialchars_decode( $element ) == "</pre>" ) {
                $preg = true;
            }

            if ( ! $preg ) {
                continue;
            }

            if ( '' === $element || '<' === $element[0] ) {
                continue;
            }

            if ( false === stripos( $element, '$$' ) ) {
                continue;
            }

            $element = preg_replace_callback( $regexTeXInline, array( $this, 'katex_src_multiline' ), $element );
        }

        return implode( '', $textarr );
    }

    public function katex_src_multiline( $matches ) {

        $katex = $matches[1];

        $katex = $this->katex_entity_decode_editormd( $katex );

        return '<span class="katex math multi-line">' . $katex . '</span>';
    }

    public function katex_entity_decode_editormd( $katex ) {
        return str_replace(
            array( '&lt;', '&gt;', '&quot;', '&#039;', '&#038;', '&amp;', "\n", "\r", '&#60;', '&#62;', "&#40;", "&#41;", "&#95;", "&#33;", "&#123;", "&#125;", "&#94;", "&#43;","&#92;" ),
            array( '<', '>', '"', "'", '&', '&', ' ', ' ', '<', '>', '(', ')', '_', '!', '{', '}', '^', '+','\\\\' ),
            $katex );
    }

    public function wp_html_split( $input ) {
        return preg_split($this->get_html_split_regex(), $input, -1, PREG_SPLIT_DELIM_CAPTURE );
    }

    public function get_html_split_regex() {
        static $regex;

        if ( ! isset( $regex ) ) {
            $comments =
                '!'           // Start of comment, after the <.
                . '(?:'         // Unroll the loop: Consume everything until --> is found.
                .     '-(?!->)' // Dash not followed by end of comment.
                .     '[^\-]*+' // Consume non-dashes.
                . ')*+'         // Loop possessively.
                . '(?:-->)?';   // End of comment. If not found, match all input.

            $cdata =
                '!\[CDATA\['  // Start of comment, after the <.
                . '[^\]]*+'     // Consume non-].
                . '(?:'         // Unroll the loop: Consume everything until ]]> is found.
                .     '](?!]>)' // One ] not followed by end of comment.
                .     '[^\]]*+' // Consume non-].
                . ')*+'         // Loop possessively.
                . '(?:]]>)?';   // End of comment. If not found, match all input.

            $escaped =
                '(?='           // Is the element escaped?
                .    '!--'
                . '|'
                .    '!\[CDATA\['
                . ')'
                . '(?(?=!-)'      // If yes, which type?
                .     $comments
                . '|'
                .     $cdata
                . ')';

            $regex =
                '/('              // Capture the entire match.
                .     '<'           // Find start of element.
                .     '(?'          // Conditional expression follows.
                .         $escaped  // Find end of escaped element.
                .     '|'           // ... else ...
                .         '[^>]*>?' // Find end of normal element.
                .     ')'
                . ')/';
        }

        return $regex;
    }
}
