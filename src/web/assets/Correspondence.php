<?php

namespace owldesign\qarr\web\assets;


use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class Correspondence extends AssetBundle
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
            'css/correspondence.css',
        ];

        parent::init();
    }
}