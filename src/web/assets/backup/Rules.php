<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;

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
//            Select2::class,
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