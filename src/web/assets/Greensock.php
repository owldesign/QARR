<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class Greensock extends AssetBundle
{
    public function init()
    {
        Craft::setAlias('@odlib', '@vendor/owldesign/qarr/lib/');

        $this->sourcePath = "@odlib";

        $this->js = [
            'greensock/TweenMax.min.js',
        ];

        parent::init();
    }
}
