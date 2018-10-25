<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

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
            VelocityUI::class,
            Anime::class
        ];

        $this->js = [
            'js/qarrcp.js',
        ];

        $this->css = [
            'css/qarrcp.css',
        ];

        parent::init();
    }
}