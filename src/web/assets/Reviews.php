<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;

class Reviews extends AssetBundle
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
            'css/reviews.css',
        ];

        $this->js = [
            'js/correspondence.js',
            'js/reviews.js',
        ];

        parent::init();
    }
}