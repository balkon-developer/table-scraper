<?php

namespace BalkonDeveloper\TableScraper;

use DOMElement;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\CssSelector\CssSelectorConverter;

class Helper
{
    /**
     * Sanitize query
     *
     * @param string $query
     * @return string
     */
     public static function sanitizeQuery(string $query, string $dirty = null)
     {
         $query = trim($query);

         if ($dirty !== null && strpos($query, $dirty) === 0) {
             $query = Str::replaceFirst($dirty, '', $query);
         }

         return $query;
     }

    /**
     * Evaluates the given XPath expression and returns a typed result if possible
     *
     * @param Scrape $scrape
     * @param string $selector
     * @param DOMElement $reff
     * @return mixed
     */
    public static function query(Scrape $scrape, string $selector, DOMElement $reff = null)
    {
        $query = (new CssSelectorConverter)->toXPath($selector);

        return Collection::make(
            (new DOMXPath($scrape))->evaluate($query, $reff)
        );
    }

    /**
     * iterate childNodes from a DOMElement
     *
     * @param DOMElement $base
     * @param string|array $tagName
     * @param callable $func
     * @return void
     */
    public static function iterateNodes(DOMElement $base, $tagName = null, callable $func)
    {
        $index = 0;
        foreach ($base->childNodes as $node) {
            $tagNames = is_array($tagName)
                ? $tagName
                : ($tagName ? [$tagName] : null);

            if ($tagNames === null || in_array($node->nodeName, $tagNames)) {
                $func($node, $index);
                $index++;
            }
        }
    }

    /**
     * Unmerge cells
     *
     * @param DOMElement $base
     * @return void
     */
    public static function unmerge(DOMElement $base)
    {
        $toBeCloned = [];

        static::iterateNodes($base, ['tr', 'thead', 'tbody'], function (DOMElement $el, $y) use ($base) {
            if ($el->nodeName !== 'tr') {
                static::unmerge($el);
            } else {
                static::iterateNodes($el, ['th', 'td'], function (DOMElement $cell, $x) use ($base, $el) {
                    if ($rowspan = $cell->getAttribute('rowspan')) {
                        $cell->removeAttribute('rowspan');
                        while($rowspan > 1) {
                            $cloned = $cell->cloneNode();
                            $cloned->textContent = $cell->textContent;
                            $el = static::cloneToNextSibling($el, $cloned, $x);
                            $rowspan--;
                        }
                    }
                });
            }
        });

        static::iterateNodes($base, 'tr', function (DOMElement $tr, $y) use ($base) {
            static::iterateNodes($tr, ['th', 'td'], function (DOMElement $cell, $x) use ($base, $tr) {
                if ($colspan = $cell->getAttribute('colspan')) {
                    $cell->removeAttribute('colspan');
                    while ($colspan > 1) {
                        $cloned = $cell->cloneNode();
                        $cloned->textContent = $cell->textContent;
                        $tr->insertBefore($cloned, $cell);
                        $colspan--;
                    }
                }
            });
        });
    }

    protected static function cloneToNextSibling(DOMElement $tr, DOMElement $cloned, int $posX)
    {
        $tag = $tr->nodeName;
        do {
            $tr = $tr->nextSibling;
        } while ($tr->nodeName !== $tag);

        $index = 0;
        static::iterateNodes($tr, ['th', 'td'], function (DOMElement $cell) use (&$index, $posX, $tr, $cloned) {
            if ($index === $posX) {
                $tr->insertBefore($cloned, $cell);
            }
            $index += max($cell->getAttribute('colspan'), 1);
        });

        return $tr;
    }
}
