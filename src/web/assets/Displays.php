<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;

class Displays extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__;

        $this->css = [
            'css/displays.css',
        ];

        $this->js = [
            'js/displays.js',
        ];

        parent::init();
    }
}