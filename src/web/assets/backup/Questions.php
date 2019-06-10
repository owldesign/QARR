<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;

class Questions extends AssetBundle
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
            'css/questions.css',
        ];

        $this->js = [
            'js/correspondence.js',
            'js/questions.js',
        ];

        parent::init();
    }
}