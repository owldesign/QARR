<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class NProgress extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        Craft::setAlias('@odlib', '@vendor/owldesign/qarr/lib/');

        $this->sourcePath = "@odlib";

        $this->css = [
            'nprogress/nprogress.css',
        ];

        $this->js = [
            'nprogress/nprogress.js',
        ];

        parent::init();
    }
}