<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ElementStyles extends AssetBundle
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
        ];

        $this->css = [
            'css/element-shared.css',
            'css/element-edit.css'
        ];

        parent::init();
    }
}