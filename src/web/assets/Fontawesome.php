<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class Fontawesome extends AssetBundle
{
    public function init()
    {
        Craft::setAlias('@odlib', '@vendor/owldesign/qarr/lib/');

        $this->sourcePath = "@odlib";

        $this->js = [
            'fontawesome/light.min.js',
            'fontawesome/regular.min.js',
            'fontawesome/solid.min.js',
            'fontawesome/fontawesome.min.js'
        ];

        parent::init();
    }
}
