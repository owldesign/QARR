<?php

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;

class Counterup extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = "@libs";


        $this->js = [
            'counterup/jquery.counterup.min.js',
        ];

        parent::init();
    }
}
