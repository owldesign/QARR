<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class CounterUp extends AssetBundle
{
    public function init()
    {
        Craft::setAlias('@odlib', '@vendor/owldesign/qarr/lib/');

        $this->sourcePath = "@odlib";

        $this->depends = [
            Waypoints::class,
        ];

        $this->js = [
            'counterup/jquery.counterup.min.js',
        ];

        parent::init();
    }
}
