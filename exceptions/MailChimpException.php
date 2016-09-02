<?php

namespace sammaye\mailchimp\exceptions;

class MailChimpException extends \Exception
{
    public $errors;

    public function __construct($message = null, $code = 0, $errors = [], \Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }
}