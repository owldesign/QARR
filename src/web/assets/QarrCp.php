<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;

class QarrCp extends AssetBundle
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
            VueAsset::class,
            NProgress::class,
            Popper::class
        ];

        $this->css = [
            'dist/css/qarrcp.css',
        ];

        $this->js = [
            'dist/js/main.js',
            'dist/js/dashboard.js',
            'dist/js/reviews.js',
            'dist/js/questions.js',
            'dist/js/displays.js',
            'dist/js/rules.js',
            'dist/js/utilities.js',
        ];

        parent::init();
    }
}