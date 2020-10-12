<?php

namespace BalkonDeveloper\TableScraper;

use BalkonDeveloper\TableScraper\Exceptions\NoTableException;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\CssSelector\CssSelectorConverter;

class ScrapeTable
{
    protected $scrape;
    protected $table;

    public function __construct(Scrape $scrape, DOMElement $table)
    {
        $this->scrape = $scrape;
        $this->setTable($table);
    }

    /**
     * Create Table DOM Element from selector
     *
     * @param Scrape $scrape
     * @param string $query
     * @return static
     */
    public static function fromSelector(Scrape $scrape, string $query = null)
    {
        $query = 'table'.Helper::sanitizeQuery($query, 'table');
        $table = $scrape->filter($query)->first();

        if (!$table) {
            throw new NoTableException;
        }
        return new static($scrape, $table);
    }

    protected function setTable(DOMElement $table)
    {
        Helper::unmerge($table);
        $this->table = $table;

        return $this;
    }

    /**
     * Filtering DOM
     *
     * @param string $selector
     * @return DOMNodeList
     */
    protected function filter(string $selector)
    {
         return Helper::query($this->scrape, $selector, $this->table);
    }

    protected function toCollection($node, array $headers = [], callable $filter = null)
    {
        $collection = Collection::make();

        if ($node) {
            Helper::iterateNodes($node, 'tr', function ($tr) use ($collection, $headers, $filter) {
                $row = Collection::make();
                Helper::iterateNodes($tr, ['th', 'td'], function ($cell, $index) use ($row, $headers, $filter) {
                    $key = $headers[$index] ?? $index;
                    $value = $cell->textContent;
                    $row->put($key, is_callable($filter) ? $filter($value, $index, $key) : $value);
                });
                $collection->push($row);
            });
        }

        return $collection;
    }

    /**
     * Get table header
     *
     * @return DOMElement
     */
    public function header()
    {
        return $this->filter('thead')->first();
    }

    public function headerData(callable $filter = null)
    {
        return $this
            ->toCollection($this->header(), [], $filter);
    }

    public function body()
    {
        return $this->filter('tbody')->first();
    }

    public function data(array $headers = [], callable $filter = null)
    {
        return $this
            ->toCollection($this->body(), $headers, $filter);
    }

    /**
     * Getter
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->table->{$name};
    }

    /**
     * Show as HTML
     *
     * @return string
     */
    public function saveHTML()
    {
        return $this->table->C14N();
    }

    /**
     * To String
     *
     * @return string
     */
    public function __toString()
    {
          return $this->saveHTML();
    }

    /**
     * Call method
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->table, $name], $arguments);
    }
}
