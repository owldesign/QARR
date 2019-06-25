<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class Select2 extends AssetBundle
{
    public function init()
    {
        Craft::setAlias('@odlib', '@vendor/owldesign/qarr/lib/');

        $this->sourcePath = "@odlib";

        $this->css = [
            'select2/select2.min.css',
        ];

        $this->js = [
            'select2/select2.full.min.js',
        ];

        parent::init();
    }
}
