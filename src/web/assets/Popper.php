<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class Popper extends AssetBundle
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

        $this->js = [
            'popper/popper.min.js',
        ];

        parent::init();
    }
}