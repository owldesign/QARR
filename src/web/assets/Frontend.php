<?php

namespace owldesign\qarr\web\assets;

use craft\web\AssetBundle;
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
            JqueryAsset::class,
            VelocityAsset::class,
        ];

        $this->js = [
            'js/qarr-plugin.js',
            'js/element-resize-detector.min.js',
            'js/garnish.min.js',
            'js/utilities-web.js',
            'js/frontend.js',
        ];

        $this->css = [
            'css/frontend.css',
        ];

        parent::init();
    }
}
