<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class Geolocation extends AssetBundle
{
    public function init()
    {
        Craft::setAlias('@odlib', '@vendor/owldesign/qarr/lib/');

        $this->sourcePath = "@odlib";

        $this->css = [
            'geolocation/mapbox-gl.css',
        ];

        $this->js = [
            'geolocation/mapbox-gl.js',
        ];

        parent::init();
    }
}
