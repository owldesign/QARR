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
use craft\web\View;
use craft\web\Controller;
use craft\helpers\Template;
use craft\controllers\ElementIndexesController;
use yii\web\Response;

use owldesign\qarr\QARR;

/**
 * Class ElementsController
 * @package owldesign\qarr\controllers
 */
class ElementsController extends Controller
{
    // Protected Properties
    // =========================================================================

    /**
     * @var array
     */
    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Query builder for elements
     *
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionQueryElements()
    {
        $this->requirePostRequest();

        $request    = Craft::$app->getRequest();
        $type       = $request->getBodyParam('type');
        $limit      = $request->getBodyParam('limit');
        $offset     = $request->getBodyParam('offset');
        $productId  = $request->getBodyParam('productId');

        $variables['entries'] = QARR::$plugin->elements->queryElements($type, $productId, $limit, $offset);

        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $template = Craft::$app->view->renderTemplate('qarr/frontend/_'. $type .'/entries', $variables);
        Craft::$app->view->setTemplateMode($oldPath);

        return $this->asJson([
            'success' => true,
            'template'   => Template::raw($template)
        ]);
    }

    public function actionQuerySortElements()
    {
        $this->requirePostRequest();

        $request    = Craft::$app->getRequest();
        $type       = $request->getBodyParam('type');
        $value      = $request->getBodyParam('value');
        $limit      = $request->getBodyParam('limit');
        $productId  = $request->getBodyParam('productId');

        $variables['entries'] = QARR::$plugin->elements->querySortElements($type, $productId, $value, $limit);

        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $template = Craft::$app->view->renderTemplate('qarr/frontend/_'. $type .'/entries', $variables);
        Craft::$app->view->setTemplateMode($oldPath);

        return $this->asJson([
            'success' => true,
            'template'   => Template::raw($template)
        ]);

    }

    /**
     * Star filtered elements
     *
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionQueryStarFilteredElements()
    {
        $this->requirePostRequest();

        $request    = Craft::$app->getRequest();
        $type       = $request->getBodyParam('type');
        $rating     = $request->getBodyParam('rating');
        $limit      = $request->getBodyParam('limit');
        $productId  = $request->getBodyParam('productId');

        $variables['entries'] = QARR::$plugin->elements->queryStarFilteredElements($type, $productId, $rating, $limit);
        
        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $template = Craft::$app->view->renderTemplate('qarr/frontend/_'. $type .'/entries', $variables);
        Craft::$app->view->setTemplateMode($oldPath);

        return $this->asJson([
            'success' => true,
            'template'   => Template::raw($template)
        ]);
    }

    /**
     * Check total pending count
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCheckPending()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $reviewsPending = QARR::$plugin->elements->queryElements('reviews', null, null, null, 'pending')->count();
        $reviewsApproved = QARR::$plugin->elements->queryElements('reviews', null, null, null, 'approved')->count();
        $reviewsRejected = QARR::$plugin->elements->queryElements('reviews', null, null, null, 'rejected')->count();
        $reviewsTotal =  $reviewsPending + $reviewsApproved + $reviewsRejected;

        $questionsPending = QARR::$plugin->elements->queryElements('questions', null, null, null, 'pending')->count();
        $questionsApproved = QARR::$plugin->elements->queryElements('questions', null, null, null, 'approved')->count();
        $questionsRejected = QARR::$plugin->elements->queryElements('questions', null, null, null, 'rejected')->count();
        $questionsTotal = $questionsPending + $questionsApproved + $questionsRejected;

        $totalPending = $reviewsPending + $questionsPending;
        
        $variable['reviews'] = [
            'total' => $reviewsTotal,
            'pending' =>  (int) $reviewsPending,
            'approved' => (int) $reviewsApproved,
            'rejected' => (int) $reviewsRejected
        ];

        $variable['questions'] = [
            'total' => $questionsTotal,
            'pending' => (int) $questionsPending,
            'approved' => (int) $questionsApproved,
            'rejected' => (int) $questionsRejected,
        ];
        

        $data = [
            'success'       => true,
            'variables'     => $variable,
            'totalPending'  => $totalPending,
        ];
        
        return $this->asJson($data);
    }

    public function actionFetchPendingItems()
    {
        $this->requirePostRequest();

        $request    = Craft::$app->getRequest();
        $type       = $request->getBodyParam('type');
        $limit      = $request->getBodyParam('limit');
        $exclude    = $request->getBodyParam('exclude');

        $variables['type'] = $type;
        $variables['entries'] = QARR::$plugin->elements->queryElements($type, null, $limit, null, 'pending', $exclude);

        $template = Craft::$app->view->renderTemplate('qarr/dashboard/_includes/pending-items', $variables);

        return $this->asJson([
            'success' => true,
            'template'   => Template::raw($template),
            'count' => $variables['entries']->count()
        ]);
    }

    /**
     * @return bool|Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionReportAbuse()
    {
        $this->requirePostRequest();

        $request    = Craft::$app->getRequest();
        $elementId  = $request->getBodyParam('id');
        $type       = $request->getBodyParam('type');

        if (!$elementId && !$type) {
            return false;
        }

        $result = QARR::$plugin->elements->reportAbuse($elementId, $type);
        $entry  = QARR::$plugin->reviews->getEntryById($elementId);

        if ($result) {
            // TODO: Setup up sometype of front end cookies
            // QARR::$plugin->cookies->set('reported', $elementId);

            return $this->asJson([
                'success' => true,
                'entry' => $entry
            ]);
        } else {
            return $this->asJson([
                'success' => false
            ]);
        }
    }

    /**
     * @return bool|Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionClearAbuse()
    {
        $this->requirePostRequest();

        $request    = Craft::$app->getRequest();
        $elementId  = $request->getBodyParam('id');
        $type       = $request->getBodyParam('type');

        if (!$elementId && !$type) {
            return false;
        }

        $result = QARR::$plugin->elements->clearAbuse($elementId, $type);

        if ($result) {
            return $this->asJson([
                'success' => true
            ]);
        } else {
            return $this->asJson([
                'success' => false
            ]);
        }
    }

    /**
     * @return null|Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdateStatus()
    {
        $this->requirePostRequest();

        $request    = Craft::$app->getRequest();
        $elementId  = $request->getBodyParam('id');
        $status     = $request->getBodyParam('status');
        $type       = $request->getBodyParam('type');

        if (!$elementId && !$type) {
            return null;
        }

        $result = QARR::$plugin->elements->updateStatus($elementId, $status, $type);
        $entry  = QARR::$plugin->elements->getElement($type, $elementId);

        if (!$result) {
            return null;
        }

        return $this->asJson([
            'success' => true,
            'entry' => $entry
        ]);
    }
}