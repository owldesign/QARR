<?php

namespace owldesign\qarr\services;

use Craft;
use craft\base\Component;
use craft\elements\User;

class Encrypt extends Component
{
    protected $length;
    protected $alphabet;
    protected $salt;
    protected $encoder;

    public function __construct()
    {
        $this->salt = 'I am secret';
        $this->alphabet = 'abcdefghijklmnopqrstuvwxyz';
        $this->encoder = new \Hashids\Hashids($this->salt, 24, $this->alphabet);
    }

    public function encodeById($id)
    {

        $this->encoder = new \Hashids\Hashids($this->salt, 24, $this->alphabet);

        $encodedId = $this->encoder->encode($id);

        return $encodedId;
    }

    public function decode($hash)
    {
        $this->encoder = new \Hashids\Hashids($this->salt, 24, $this->alphabet);
        $id = $this->encoder->decode($hash);

        return reset($id);
    }
}