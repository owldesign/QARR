<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;

class Dashboard extends AssetBundle
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
            'css/dashboard.css',
        ];

        $this->js = [
            'js/dashboard.js',
        ];

        parent::init();
    }
}