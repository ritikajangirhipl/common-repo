<?php

namespace Vendor\CommonPackageAmlBot\Traits;

use Exception;
use Throwable;

class AMLBotException extends Exception
{
    protected $json;

    public function __construct($message, $code = 0, Throwable $previous = null, $json = [])
    {
        $this->json = $json;
        parent::__construct($message, $code, $previous);
    }

    public function getJson()
    {
        return $this->json;
    }
}
