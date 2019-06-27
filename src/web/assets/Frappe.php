<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class Frappe extends AssetBundle
{
    public function init()
    {
        Craft::setAlias('@odlib', '@vendor/owldesign/qarr/lib/');

        $this->sourcePath = "@odlib";

        $this->css = [
            'frappe/frappe-charts.min.css',
        ];

        $this->js = [
            'frappe/frappe-charts.min.iife.js',
        ];

        parent::init();
    }
}
