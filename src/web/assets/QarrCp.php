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
            'css/qarrcp.css',
        ];

        $this->js = [
            'js/main.js',
            'js/dashboard.js',
            'js/reviews.js',
            'js/questions.js',
            'js/displays.js',
            'js/rules.js',
            'js/utilities.js',
        ];

        parent::init();
    }
}