<?php

namespace common\services;

class ValidateException extends ServiceException
{
    private $messages = [];

    public function __construct($messages = [])
    {
        $this->messages = array_merge($this->messages, $messages);
    }

    public function getName()
    {
        return 'ValidateException';
    }

    public function getMessages()
    {
        return $this->messages;
    }
}