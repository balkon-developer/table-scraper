<?php

namespace BalkonDeveloper\TableScraper\Exceptions;

use Exception;

class NoTableException extends Exception
{
    public function __construct($message = 'No table is found')
    {
        parent::__construct($message);
    }
}
