<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class Rules extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__;

        $this->depends = [
            CpAsset::class,
            Tags::class
        ];

        $this->css = [
            'css/rules.css',
        ];

        $this->js = [
            'js/rules.js',
        ];

        parent::init();
    }
}