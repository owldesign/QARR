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
use craft\db\Paginator;
use craft\helpers\AdminTable;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\web\View;
use craft\web\Controller;
use craft\helpers\Template;
use craft\controllers\ElementIndexesController;
use owldesign\qarr\elements\Question;
use owldesign\qarr\elements\Review;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\data\Pagination;
use yii\log\Logger;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
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

//    public function actionQueryPaginatedElements()
//    {
//        $this->requirePostRequest();
//
//        $pageInfo = Craft::$app->getRequest()->getBodyParam('pageInfo');
//        $page = Craft::$app->getRequest()->getQueryParam('page');
//        $pageSize = Craft::$app->getRequest()->getQueryParam('per-page');
//
//        $pageSize = 1;
//
//        $query = Review::find()->status('approved');
//        $countQuery = clone $query;
//
//        $pages = new Pagination([
//            'totalCount' => $countQuery->count(),
//            'pageSize' => $pageSize,
//        ]);
//
//        $entries = $query->offset($pages->offset)->limit($pages->limit)->all();
//
//        $variables = [
//            'entries' => $entries,
//            'pages' => $pages,
//        ];
//
//        $oldPath = Craft::$app->view->getTemplateMode();
//        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
//        $template = Craft::$app->view->renderTemplate('qarr/frontend/_reviews/entries-ajax', $variables);
//        $paginationTemplate = Craft::$app->view->renderTemplate('qarr/frontend/_reviews/_pagination', $variables);
//        Craft::$app->view->setTemplateMode($oldPath);
//
//        return $this->asJson([
//            'pageInfo' => $pageInfo,
//            'entries' => $entries,
//            'template' => $template,
//            'paginationTemplate' => $paginationTemplate,
//        ]);
//
////        $variables['entries'] = QARR::$plugin->elements->queryElements($type, $elementId, $limit, $offset);
////
////
////        return $this->asJson([
////            'success' => true,
////            'template' => Template::raw($template)
////        ]);
//    }

    /**
     * Query builder for elements
     *
     * @return Response
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionQueryElements()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $type = $request->getBodyParam('type');
        $order = $request->getBodyParam('order', 'dateCreated desc');
        $limit = $request->getBodyParam('limit');
        $offset = $request->getBodyParam('offset');
        $elementId = $request->getBodyParam('elementId');

        $templatePath = $this->_getTemplatePathByElementType($type);

        $variables['entries'] = QARR::$plugin->elements->queryElements($type, $order, $elementId, $limit, $offset);

        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $template = Craft::$app->view->renderTemplate('qarr/frontend/_' . $templatePath . '/entries-ajax', $variables);
        Craft::$app->view->setTemplateMode($oldPath);

        return $this->asJson([
            'success' => true,
            'template' => Template::raw($template)
        ]);
    }

    /**
     * Query sort elements
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function actionQuerySortElements()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $type = $request->getBodyParam('type');
        $order = $request->getBodyParam('order');
        $limit = $request->getBodyParam('limit');
        $elementId = $request->getBodyParam('elementId');

        $variables['entries'] = QARR::$plugin->elements->querySortElements($type, $order, $elementId, $limit);

        $templatePath = $this->_getTemplatePathByElementType($type);

        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $template = Craft::$app->view->renderTemplate('qarr/frontend/_' . $templatePath . '/entries-ajax', $variables);
        Craft::$app->view->setTemplateMode($oldPath);

        return $this->asJson([
            'success' => true,
            'template' => Template::raw($template)
        ]);
    }

    public function actionQueryRenderElements($kind = 'reviews')
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $type = $request->getBodyParam('type');
        $order = $request->getBodyParam('order', 'dateCreated desc');
        $limit = $request->getBodyParam('limit');
        $offset = $request->getBodyParam('offset');
        $elementId = $request->getBodyParam('elementId');
        $template = $request->getBodyParam('template');

        $variables['records'] = QARR::$plugin->elements->queryElements($type, $order, $elementId, $limit, $offset);
        $variables['template'] = $template;
        $variables['pagination'] = $variables['pagination'] ?? true;

        if ($elementId) {
            $variables['model'] = Craft::$app->getElements()->getElementById($elementId);
        }

        if ($template !== '') {
            $html = Craft::$app->view->renderTemplate($template, $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = Craft::$app->view->renderTemplate('qarr/frontend/render/' . $kind . '-ajax', $variables);
            Craft::$app->view->setTemplateMode($oldPath);
        }

        return $this->asJson([
            'success' => true,
            'template' => Template::raw($html)
        ]);
    }

    /**
     * Star filtered elements
     *
     * @return Response
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionQueryStarFilteredElements()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $type = $request->getBodyParam('type');
        $rating = $request->getBodyParam('rating');
        $limit = $request->getBodyParam('limit', null);
        $offset = $request->getBodyParam('offset');
        $elementId = $request->getBodyParam('elementId');
        $order = $request->getBodyParam('order');

        if ($limit === '') {
            $limit = null;
        }

        $variables['entries'] = QARR::$plugin->elements->queryStarFilteredElements($type, $elementId, $rating, $order, $limit, $offset);

        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $template = Craft::$app->view->renderTemplate('qarr/frontend/_' . $type . '/entries-ajax', $variables);
        Craft::$app->view->setTemplateMode($oldPath);

        return $this->asJson([
            'success' => true,
            'pageInfo' => [
                'total' => $variables['entries']->count(),
            ],
            'template' => Template::raw($template)
        ]);
    }

    /**
     * Check total pending count
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionCheckPending()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $reviewsPending = QARR::$plugin->elements->queryElements('owldesign\qarr\elements\Review', null, null, null, 'pending')->count();
        $reviewsApproved = QARR::$plugin->elements->queryElements('owldesign\qarr\elements\Review', null, null, null, 'approved')->count();
        $reviewsRejected = QARR::$plugin->elements->queryElements('owldesign\qarr\elements\Review', null, null, null, 'rejected')->count();
        $reviewsTotal = $reviewsPending + $reviewsApproved + $reviewsRejected;

        $questionsPending = QARR::$plugin->elements->queryElements('owldesign\qarr\elements\Question', null, null, null, 'pending')->count();
        $questionsApproved = QARR::$plugin->elements->queryElements('owldesign\qarr\elements\Question', null, null, null, 'approved')->count();
        $questionsRejected = QARR::$plugin->elements->queryElements('owldesign\qarr\elements\Question', null, null, null, 'rejected')->count();
        $questionsTotal = $questionsPending + $questionsApproved + $questionsRejected;

        $totalPending = $reviewsPending + $questionsPending;

        $variable['reviews'] = [
            'total' => $reviewsTotal,
            'pending' => (int)$reviewsPending,
            'approved' => (int)$reviewsApproved,
            'rejected' => (int)$reviewsRejected
        ];

        $variable['questions'] = [
            'total' => $questionsTotal,
            'pending' => (int)$questionsPending,
            'approved' => (int)$questionsApproved,
            'rejected' => (int)$questionsRejected,
        ];


        $data = [
            'success' => true,
            'variables' => $variable,
            'totalPending' => $totalPending,
        ];

        return $this->asJson($data);
    }

    /**
     * Fetch pending items
     *
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws BadRequestHttpException
     */
    public function actionFetchPendingItems()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $type = $request->getBodyParam('type');
        $limit = $request->getBodyParam('limit');
        $exclude = $request->getBodyParam('exclude');
        $elementId = $request->getBodyParam('elementId');

        $variables['type'] = $type;
        $variables['entries'] = QARR::$plugin->getElements()->queryElements($type, 'dateCreated desc', $elementId, $limit, null, 'pending', $exclude);

        $template = Craft::$app->view->renderTemplate('qarr/dashboard/_includes/pending-items', $variables);


        return $this->asJson([
            'success' => true,
            'template' => Template::raw($template),
            'count' => $variables['entries']->count()
        ]);
    }

    /**
     * Report abuse
     *
     * @return bool|Response
     * @throws BadRequestHttpException
     */
    public function actionReportAbuse()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $elementId = $request->getBodyParam('id');
        $type = $request->getBodyParam('type');

        if (!$elementId && !$type) {
            return false;
        }

        $result = QARR::$plugin->elements->reportAbuse($elementId, $type);
        $entry = QARR::$plugin->reviews->getEntryById($elementId);

        if ($result) {
            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => true,
                    'entry' => $entry
                ]);
            } else {
                Craft::$app->getSession()->setNotice(QARR::t('Abuse reported'));

                return $this->redirectToPostedUrl($entry);
            }
        } else {
            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => false
                ]);
            } else {
                Craft::$app->getSession()->setError(QARR::t('Cannot report abuse'));

                Craft::$app->getUrlManager()->setRouteParams([
                    'message' => QARR::t('Cannot report abuse'),
                    'errors' => $result,
                ]);
            }
        }

        return null;
    }

    /**
     * Clear abuse
     *
     * @return bool|Response
     * @throws BadRequestHttpException
     */
    public function actionClearAbuse()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $elementId = $request->getBodyParam('id');
        $type = $request->getBodyParam('type');

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
     * Update status
     *
     * @return null|Response
     * @throws BadRequestHttpException
     */
    public function actionUpdateStatus()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $elementId = $request->getBodyParam('id');
        $status = $request->getBodyParam('status');
        $type = $request->getBodyParam('type');

        if (!$elementId && !$type) {
            return null;
        }

        $result = QARR::$plugin->elements->updateStatus($elementId, $status, $type);
        $entry = QARR::$plugin->elements->getElement($type, $elementId);

        if (!$result) {
            return null;
        }

        return $this->asJson([
            'success' => true,
            'entry' => $entry
        ]);
    }

    /**
     * Delete
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionDelete()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();
        $elementId = $request->getBodyParam('id');

        $element = Craft::$app->elements->getElementById($elementId);

        $this->_enforceEditPermissions();

        if ($element instanceof Question) {
            $type = 'question';
            $responseDeleted = QARR::$plugin->answers->deleteAnswersByElement($element);
            $elementDeleted = QARR::$plugin->questions->deleteEntry($element);
        } else {
            $responseDeleted = QARR::$plugin->replies->deleteRepliesByElement($element);
            $elementDeleted = QARR::$plugin->reviews->deleteEntry($element);
        }

        $correspondenceIsDeleted = QARR::$plugin->correspondence->deleteCorrespondenceByElement($element);

        if ($elementDeleted && $correspondenceIsDeleted && $responseDeleted) {
            return $this->asJson([
                'success' => true
            ]);
        }

        return $this->asJson([
            'success' => false
        ]);
    }

    // Private Methods
    // =========================================================================

    /**
     * @throws ForbiddenHttpException
     */
    private function _enforceEditPermissions()
    {
        $this->requirePermission('qarr:accessReviews');
    }

    /**
     * Return element type path
     *
     * @param $elementType
     * @return string
     */
    private function _getTemplatePathByElementType($elementType): string
    {
        if (new $elementType instanceof Review) {
            return 'reviews';
        } else {
            return 'questions';
        }
    }
}
