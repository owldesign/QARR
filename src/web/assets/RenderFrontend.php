<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\axios\AxiosAsset;
use craft\web\assets\garnish\GarnishAsset;
use craft\web\assets\velocity\VelocityAsset;
use yii\web\JqueryAsset;

class RenderFrontend extends AssetBundle
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
//            VelocityAsset::class,
            AxiosAsset::class,
        ];

        $this->js = [
//            'js/qarr-plugin.js',
//            'js/utilities-web.js',
            'js/store.min.js',
            'js/render-api.js',
            'js/render-frontend.js',
        ];

        $this->css = [
            'css/render-frontend.css',
        ];

        parent::init();
    }
}
