<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\controllers;

use Craft;
use craft\web\Controller;
use owldesign\qarr\QARR;
use yii\web\Response;

class DashboardController extends Controller
{
    protected int|bool|array $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Index page
     * @param array $variables
     * @return Response
     */
    public function actionIndex(array $variables = []): Response
    {
        $variables = [];

        return $this->renderTemplate('qarr/index', $variables);
    }
}