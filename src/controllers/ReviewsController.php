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
use yii\web\NotFoundHttpException;
use yii\web\Response;

use owldesign\qarr\QARR;
use owldesign\qarr\elements\Review;
use owldesign\qarr\rules\ProfanityCheck;

/**
 * Class ReviewsController
 * @package owldesign\qarr\controllers
*/
class ReviewsController extends Controller
{
    // Protected Properties
    // =========================================================================

    /**
     * @var array
     */
    protected $allowAnonymous = ['actionSave', 'actionPaginate'];

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @param int|null $reviewId
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit(int $reviewId = null): Response
    {
        if ($reviewId) {
            $variables['entry'] = QARR::$plugin->reviews->getEntryById($reviewId);
            $variables['title'] = $variables['entry']->title;
        } else {
            throw new NotFoundHttpException(QARR::t('Entry not found'));
        }

        $entry = $variables['entry'];

        if ($entry->displayId) {
            $variables['fieldLayoutTabs'] = $entry->getFieldLayout()->getTabs();
        }

        $variables['correspondences'] = QARR::$plugin->correspondence->getCorrespondenceByParams($entry->emailAddress, 'reviews', $entry->id);

        $this->_enforceEditPermissions($variables['entry']);

        $variables['fullPageForm'] = true;
        $variables['continueEditingUrl'] = 'qarr/reviews/{id}';
        $variables['saveShortcutRedirect'] = $variables['continueEditingUrl'];

        return $this->renderTemplate('qarr/reviews/_edit', $variables);
    }

    /**
     * @return Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $review = new Review();
        Craft::$app->getContent()->populateElementContent($review);
        $fields                 = $request->getBodyParams()['fields'];
        $review->fullName       = $fields['fullName'];
        $review->emailAddress   = $fields['emailAddress'];
        $review->rating         = $fields['rating'];
        $review->feedback       = $fields['feedback'];
        $review->ipAddress      = $request->getUserIP();
        $review->userAgent      = $request->getHeaders()->get('user-agent');

        // Get Display
        QARR::$plugin->elements->getDisplay($request, $fields, $review);

        // Get Product
        QARR::$plugin->elements->getProduct($request, $review);

        $fieldsLocation = $request->getParam('fieldsLocation', 'fields');
        $review->setFieldValuesFromRequest($fieldsLocation);

        $success = $review->validate();

        if ($success && QARR::$plugin->reviews->saveReview($review)) {
            $saved = true;
        } else {
            $saved = false;
        }

        if ($saved) {
            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => true,
                    'message' => QARR::t('Submission successful.')
                ]);
            } else {
                Craft::$app->getUrlManager()->setRouteParams([
                    'review' => $review
                ]);
            }
        } else {
            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => false,
                    'review' => $review,
                    'errors' => $review->getErrors(),
                    'message' => QARR::t('Submission failed.')
                ]);
            } else {
                Craft::$app->getUrlManager()->setRouteParams([
                    'review' => $review
                ]);
            }

        }
    }

    /**
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDelete()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();
        $elementId = $request->getBodyParam('id');
        $review = QARR::$plugin->reviews->getEntryById($elementId);

        $this->_enforceEditPermissions();

        $repliesIsDeleted = QARR::$plugin->replies->deleteRepliesByElement($review);
        $correspondenceIsDeleted = QARR::$plugin->correspondence->deleteCorrespondenceByElement($review);
        $reviewIsDeleted = QARR::$plugin->reviews->deleteEntry($review);

        if ($repliesIsDeleted && $correspondenceIsDeleted && $reviewIsDeleted) {
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
     * @throws \yii\web\ForbiddenHttpException
     */
    private function _enforceEditPermissions()
    {
        $this->requirePermission('qarr:accessReviews');
    }

}
