<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;

class Info extends AssetBundle
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
            'css/info.css',
        ];

        $this->js = [
            'js/info.js',
        ];

        parent::init();
    }
}