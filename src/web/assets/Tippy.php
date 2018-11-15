<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class Tippy extends AssetBundle
{
    public function init()
    {
        Craft::setAlias('@odlib', '@vendor/owldesign/qarr/lib/');

        $this->sourcePath = "@odlib";

        $this->css = [
            'tippy/light.css',
        ];

        $this->js = [
            'tippy/tippy.all.min.js',
        ];

        parent::init();
    }
}
