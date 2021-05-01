<?php

namespace common\controllers;

class ActionResult
{
    private $data = [];
    private $messages = [];

    public function putData($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function addMessage($message, $type)
    {
        $this->messages = array_merge($this->messages, ['type' => $type, 'text' => $message]);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

}