<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\web\assets;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ReviewsAssets extends AssetBundle
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
            'js/reviews.js',
        ];

        parent::init();
    }
}