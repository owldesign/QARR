<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class VelocityUI extends AssetBundle
{
    public function init()
    {
        Craft::setAlias('@odlib', '@vendor/owldesign/qarr/lib/');

        $this->sourcePath = "@odlib";

        $this->js = [
            'velocity/velocity.ui.min.js',
        ];

        parent::init();
    }
}
