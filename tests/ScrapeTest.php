<?php

use BalkonDeveloper\TableScraper\Exceptions\NoTableException;
use BalkonDeveloper\TableScraper\Scrape;
use BalkonDeveloper\TableScraper\ScrapeTable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class ScrapeTest extends TestCase
{
    protected $url = 'https://developer.mozilla.org/en-US/docs/Learn/HTML/Tables/Basics';

    /** @test */
    public function it_can_load_html_from_url()
    {
        $this->assertInstanceOf(
            Scrape::class,
            Scrape::fromUrl($this->url)
        );
    }

    /** @test */
    public function it_can_filter_dom_and_get_collection_of_doms()
    {
        $tableCollection = Scrape::fromUrl($this->url)->filter('table');

        $this->assertInstanceOf(Collection::class, $tableCollection);

        foreach ($tableCollection as $table) {
            $this->assertTrue($table->nodeName === 'table');
        }
    }

    /** @test */
    public function it_can_get_a_table_by_selector()
    {
        $scrape = Scrape::fromUrl($this->url);
        $table = $scrape->table('.learn-box.standard-table');

        $this->assertInstanceOf(ScrapeTable::class, $table);

        $this->expectException(NoTableException::class);
        $errTable = $scrape->table('.not-table');
    }

    /** @test */
    public function it_can_get_collection_of_tables_by_selector()
    {
        $scrape = Scrape::fromUrl($this->url);
        $tables = $scrape->tables('.learn-box.standard-table');

        $this->assertInstanceOf(Collection::class, $tables);
        $this->assertInstanceOf(ScrapeTable::class, $tables->first());
    }

    /** @test */
    public function it_can_get_collection_of_all_tables()
    {
        $scrape = Scrape::fromUrl($this->url);
        $tables = $scrape->tables();

        $this->assertInstanceOf(Collection::class, $tables);
        $this->assertInstanceOf(ScrapeTable::class, $tables->first());
        $this->assertTrue($tables->count() > 2);
    }

    /** @test */
    public function it_can_export_to_html()
    {
        $html = '<p>Hello World</p>';

        $scape = new Scrape;
        $scape->loadHTML($html);

        $this->assertTrue($scape->saveHTML() === '<html><body>' . $html . '</body></html>');
    }

    /** @test */
    public function it_can_cast_as_string()
    {
        $html = '<p>Hello World</p>';

        $scape = new Scrape;
        $scape->loadHTML($html);

        $strHtml = (string) $scape;
        $this->assertTrue($strHtml === '<html><body>' . $html . '</body></html>');
    }
}
