<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\garnish\GarnishAsset;
use craft\web\assets\velocity\VelocityAsset;
use yii\web\JqueryAsset;

class Frontend extends AssetBundle
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
            GarnishAsset::class,
            JqueryAsset::class,
            VelocityAsset::class,
        ];

        $this->js = [
            'js/qarr-plugin.js',
            'js/utilities-web.js',
            'js/frontend.js',
        ];

        $this->css = [
            'css/frontend.css',
        ];

        parent::init();
    }
}
