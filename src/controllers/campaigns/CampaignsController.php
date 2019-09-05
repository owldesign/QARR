<?php

namespace owldesign\qarr\controllers\campaigns;

use Craft;
use craft\web\Controller;
use owldesign\qarr\QARR;
use yii\web\Response;

class CampaignsController extends Controller
{
    // Protected Properties
    // =========================================================================

    /**
     * @var array
     */
    protected $allowAnonymous = true;

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * Index
     *
     * @param array $variables
     */
    public function actionIndex(array $variables = [])
    {
        $variables = [];

        QARR::$plugin->routeTemplate('campaigns/index', $variables);
    }

    // Private Methods
    // =========================================================================

}