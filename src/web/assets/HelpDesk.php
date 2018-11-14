<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;

class HelpDesk extends AssetBundle
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
            Select2::class
        ];

        $this->css = [
            'css/help-desk.css',
        ];

        $this->js = [
            'js/help-desk.js',
        ];

        parent::init();
    }
}