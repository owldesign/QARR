<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class Wavify extends AssetBundle
{
    public function init()
    {
        Craft::setAlias('@odlib', '@vendor/owldesign/qarr/lib/');

        $this->sourcePath = "@odlib";

        $this->js = [
            'wavify/wavify.js',
            'wavify/jquery.wavify.js',
        ];

        parent::init();
    }
}
