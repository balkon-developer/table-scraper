<?php

use BalkonDeveloper\TableScraper\Exceptions\NoTableException;
use BalkonDeveloper\TableScraper\Helper;
use BalkonDeveloper\TableScraper\Scrape;
use BalkonDeveloper\TableScraper\ScrapeTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class ScrapeTableTest extends TestCase
{
    protected $firstCell = 'Row spam';

    protected function html()
    {
        return <<<HTML
        <table id="unmerged-table">
            <thead>
                <tr>
                    <th rowspan="2">{$this->firstCell}</th>
                    <th colspan="3">Col span</th>
                </tr>
                <tr>
                    <th>X</th>
                    <th>Y</th>
                    <td>Z</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td rowspan="2">{$this->firstCell}</td>
                    <td colspan="3">Col span</td>
                </tr>
                <tr>
                    <td>X</td>
                    <td>Y</td>
                    <td>Z</td>
                </tr>
            </tbody>
        </table>
HTML;
    }

    protected function scrape()
    {
        $html = $this->html();
        $scrape = new Scrape;
        $scrape->loadHTML($html);

        return $scrape;
    }

    protected function table()
    {
        $scrape = $this->scrape();
        $table = Helper::query($scrape, 'table')->first();

        return new ScrapeTable($scrape, $table);
    }

    /** @test */
    public function it_can_created_from_a_table_element()
    {
        $scrape = new Scrape;
        $table = $this->table();

        $this->assertInstanceOf(
            ScrapeTable::class,
            $table
        );
    }

    /** @test */
    public function it_can_get_a_table_from_selector()
    {
        $scrape = $this->scrape();

        $tableId = 'unmerged-table';
        $table = ScrapeTable::fromSelector($scrape, '#' . $tableId);

        $this->assertTrue($table->getAttribute('id') === $tableId);
    }

    /** @test */
    public function it_unmerged_table_automatically()
    {
        $table = $this->table();
        
        $this->assertFalse(strpos($table->C14N(), 'rowspan'));
        $this->assertFalse(strpos($table->C14N(), 'colspan'));
    }

    /** @test */
    public function it_can_get_header()
    {
        $table = $this->table();
        $header = $table->header();

        $this->assertInstanceOf(DOMElement::class, $header);
        $this->assertTrue($header->nodeName === 'thead');
    }

    /** @test */
    public function it_can_get_header_data()
    {
        $table = $this->table();
        $headerData = $table->headerData();

        $this->assertInstanceOf(Collection::class, $headerData);
        $this->assertTrue($headerData->first()->first() === $this->firstCell);

        $headerDataWithFilter = $table->headerData(function ($value, $index, $key) {
            return Str::slug($value . ' ' . $index . ' ' . $key);
        });

        $this->assertTrue($headerDataWithFilter->first()->first() === Str::slug($this->firstCell . ' 0 0'));
    }

    /** @test */
    public function it_can_get_body()
    {
        $table = $this->table();
        $body = $table->body();

        $this->assertInstanceOf(DOMElement::class, $body);
        $this->assertTrue($body->nodeName === 'tbody');
    }

    /** @test */
    public function it_can_get_table_data_from_body()
    {
        $table = $this->table();
        $data = $table->data();

        $this->assertInstanceOf(Collection::class, $data);
        $this->assertTrue($data->first()->first() === $this->firstCell);

        $customHeader = ['A', 'B', 'C', 'D'];
        $dataWithFilter = $table->data($customHeader, function ($value, $index, $key) {
            return Str::slug($value . ' ' . $index . ' ' . $key);
        });

        $this->assertTrue($dataWithFilter->first()->first() === Str::slug($this->firstCell . ' 0 A'));
    }

    /** @test */
    public function it_owns_node_elements_properties()
    {
        $table = $this->table();

        $this->assertTrue($table->nodeName === 'table');
        $this->assertInstanceOf(DOMNodeList::class, $table->childNodes);
    }

    /** @test */
    public function it_can_export_to_html()
    {
        $table = $this->table();
        $html = $this->html();

        $this->assertTrue(strpos($table->saveHTML(), '<table') === 0);
    }

    /** @test */
    public function it_can_cast_as_string()
    {
        $strTable = (string) $this->table();
        $html = $this->html();

        $this->assertTrue(strpos($strTable, '<table') === 0);
    }
}
