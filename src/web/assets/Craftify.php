<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\garnish\GarnishAsset;
use craft\web\assets\velocity\VelocityAsset;
use yii\web\JqueryAsset;

class Craftify extends AssetBundle
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
            'css/craftify.css',
        ];

        parent::init();
    }
}
