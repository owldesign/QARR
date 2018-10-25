<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class Waypoints extends AssetBundle
{
    public function init()
    {
        Craft::setAlias('@odlib', '@vendor/owldesign/qarr/lib/');

        $this->sourcePath = "@odlib";

        $this->js = [
            'waypoints/waypoints.min.js',
        ];

        parent::init();
    }
}
