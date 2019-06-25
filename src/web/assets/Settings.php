<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;

class Settings extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__;

//        $this->depends = [
//            Tippy::class
//        ];

        $this->css = [
            'css/configuration.css',
        ];

        $this->js = [
        ];

        parent::init();
    }
}