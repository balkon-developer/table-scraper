<?php

namespace BalkonDeveloper\TableScraper;

use DOMDocument;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Symfony\Component\CssSelector\CssSelectorConverter;

class Scrape extends DOMDocument
{
    /**
     * start scraping from $url
     *
     * @param string $url
     * @return self
     */
    public static function fromUrl(string $url)
    {
        return (new static)->loadUrl($url);
    }

    /**
     * load html from $url
     *
     * @param string $url
     * @return self
     */
    public function loadUrl(string $url)
    {
        $response = (new Client([
            'verify' => false,
        ]))->get($url);

        $html = $response->getBody();
        @$this->loadHTML($html);

        return $this;
    }

    /**
     * Filtering DOM
     *
     * @param string $selector
     * @return \Illuminate\Support\Collection
     */
    public function filter(string $selector)
    {
        return Helper::query($this, $selector);
    }

    /**
     * Into Table mode
     *
     * @param string $selector
     * @return \BalkonDeveloper\TableScraper\ScrapeTable
     */
    public function table(string $selector = null)
    {
        return ScrapeTable::fromSelector($this, $selector);
    }

    /**
     * Get collection of tables
     *
     * @return \Illuminate\Support\Collection
     */
    public function tables($selector = null)
    {
        $query = 'table'.Helper::sanitizeQuery($selector ?? '', 'table');
        return Collection::make($this->filter($query))
            ->map(function ($table) {
                return new ScrapeTable($this, $table);
            });
    }

    /**
     * Show as HTML
     *
     * @return string
     */
    public function saveHTML()
    {
        return $this->C14N();
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
}
