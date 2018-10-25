<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class Qarr extends AssetBundle
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
            Fontawesome::class,
            Greensock::class,
            Wavify::class,
            Select2::class,
            VelocityUI::class,
        ];

        $this->js = [
            'js/utilities-cp.js',
            'js/qarr.js',
        ];

        $this->css = [
            'css/layout.css',
        ];

        parent::init();
    }
}