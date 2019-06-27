<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;

class UI extends AssetBundle
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
            'css/ui.css',
            'css/styles.css',
        ];

        $this->js = [
        ];

        parent::init();
    }
}