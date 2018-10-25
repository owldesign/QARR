<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\velocity\VelocityAsset;

class Frontend extends AssetBundle
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
            VelocityAsset::class,
        ];

        $this->js = [
            'js/utilities-web.js',
            'js/frontend.js',
        ];

        $this->css = [
            'css/frontend.css',
        ];

        parent::init();
    }
}