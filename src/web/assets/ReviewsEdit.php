<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;

class ReviewsEdit extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__;

        $this->js = [
            'js/ReviewsEdit.js',
        ];

        parent::init();
    }
}