<?php

use BalkonDeveloper\TableScraper\Helper;
use BalkonDeveloper\TableScraper\Scrape;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /** @test */
    public function it_can_sanitize_query_from_whitespace()
    {
        $before = ' .table ';
        $after = Helper::sanitizeQuery($before);

        $this->assertTrue($after === '.table');
    }

    /** @test */
    public function it_can_sanitize_query_from_unsued_dirty_tag()
    {
        $before = ' table.table ';
        $after = Helper::sanitizeQuery($before, 'table');

        $this->assertTrue($after === '.table');
    }

    /** @test */
    public function it_can_find_dom_node_list_by_css_selector()
    {
        $scrape = new Scrape;
        $cssSelector = 'table.table';
        $nodeList = Helper::query($scrape, $cssSelector);

        $this->assertInstanceOf(Collection::class, $nodeList);
    }

    /** @test */
    public function it_can_iterate_nodes_from_dom_element()
    {
        $html = <<<HTML
            <ul>
                <li>Foo</li>
                <li>Bar</li>
            </ul>
HTML;
        $scrape = new Scrape;
        $scrape->loadHTML($html);
        $domElement = Helper::query($scrape, 'ul')->first();
        Helper::iterateNodes($domElement, 'li', function ($li, $index) {
            $this->assertInstanceOf(DOMElement::class, $li);
            if ($index == 0) {
                $this->assertTrue($li->textContent === 'Foo');
            } elseif ($index == 1) {
                $this->assertTrue($li->textContent === 'Bar');
            }
        });
    }

    /** @test */
    public function it_can_unmerge_table_cells()
    {
        $html = <<<HTML
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">Row spam</th>
                        <th colspan="2">Col span</th>
                    </tr>
                    <tr>
                        <th>X</th>
                        <th>Y</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td rowspan="2">Row spam</td>
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
        $scrape = new Scrape;
        $scrape->loadHTML($html);
        $table = Helper::query($scrape, 'table')->first();
        Helper::unmerge($table);

        $head = Helper::query($scrape, 'thead')->first();
        Helper::iterateNodes($head, 'tr', function ($tr) {
            $thCounter = 0;
            Helper::iterateNodes($tr, 'th', function ($th) use (&$thCounter) {
                $thCounter++;
            });
            $this->assertTrue($thCounter === 3);
        });

        $body = Helper::query($scrape, 'tbody')->first();
        Helper::iterateNodes($body, 'tr', function ($tr) {
            $tdCounter = 0;
            Helper::iterateNodes($tr, 'td', function ($td) use (&$tdCounter) {
                $tdCounter++;
            });
            $this->assertTrue($tdCounter === 4);
        });
    }
}
