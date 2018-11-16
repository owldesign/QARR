<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;

class Utilities extends AssetBundle
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
            Tippy::class
        ];

        $this->css = [
            'css/utilities.css',
        ];

        $this->js = [
            'js/utilities.js',
        ];

        parent::init();
    }
}