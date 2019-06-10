<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\d3\D3Asset;

class Charts extends AssetBundle
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
            D3Asset::class
        ];

        $this->js = [
            'js/charts.js',
        ];

        parent::init();
    }
}