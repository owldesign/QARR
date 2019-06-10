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
use craft\base\Element;
use craft\commerce\Plugin as CommercePlugin;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\web\Controller;
use craft\elements\db\ElementQueryInterface;
use craft\web\twig\variables\Paginate;
use yii\web\NotFoundHttpException;
use yii\web\Response;

use owldesign\qarr\QARR;
use owldesign\qarr\elements\Review;

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

//
//    public function actionGetAllReviews()
//    {
//        $formData       = Json::decode(Craft::$app->getRequest()->getRawBody());
//        // Entries
//        $limit          = $formData['reviews']['limit'];
//        $currentPage    = $formData['reviews']['currentPage'];
//
//        // Sources
//        $source         = $formData['source'];
//        $sourceArr      = explode(':', $source);
//        $sourceType     = $sourceArr[0];
//        if ($sourceType != '*') {
//            $sourceId       = $sourceArr[1];
//        } else {
//            $sourceId       = '*';
//        }
//
//        // Query
//        $query  = Review::find();
//        $query->limit($limit);
//
//        if ($sourceType == 'single') {
//            $sections = Craft::$app->getSections()->getAllSections();
//            $sectionIds = [];
//
//            foreach($sections as $section) {
//                if ($section->type == 'single') {
//                    $sectionIds[] = $section->id;
//                }
//            }
//
//            $query->where(['sectionId' => $sectionIds]);
//        } elseif ($sourceType == 'channel') {
//            $query->where(['sectionId' => $sourceId]);
//        } elseif ($sourceType == 'productType') {
//            $query->where(['productTypeId' => $sourceId]);
//        }
//
//        $result = self::paginateCriteria($query, $currentPage);
//        $meta = [
//            'totalUnread' => $query->where(['isNew' => 1])->count()
//        ];
//
//        return $this->asJson([
//            'data' => $result,
//            'meta' => $meta,
//            'formData' => $formData
//        ]);
//    }

    
//
//    /**
//     * Paginates an element query's results
//     *
//     * @param ElementQueryInterface $query
//     * @param $currentPage
//     * @return array
//     */
//    public static function paginateCriteria(ElementQueryInterface $query, $currentPage): array
//    {
//        /** @var ElementQuery $query */
////        $currentPage = Craft::$app->getRequest()->getPageNum();
//
//        // Get the total result count
//        $total = (int)$query->count() - ($query->offset ?? 0);
//
//        // Bail out early if there are no results. Also avoids a divide by zero bug in the calculation of $totalPages
//        if ($total === 0) {
//            return [new Paginate(), $query->all()];
//        }
//
//        // If they specified limit as null or 0 (for whatever reason), just assume it's all going to be on one page.
//        $limit = $query->limit ?: $total;
//
//        $totalPages = (int)ceil($total / $limit);
//
//        $paginateVariable = new Paginate();
//
//        if ($totalPages === 0) {
//            return [$paginateVariable, []];
//        }
//
//        if ($currentPage > $totalPages) {
//            $currentPage = $totalPages;
//        }
//
//        $offset = $limit * ($currentPage - 1);
//
//        // Is there already an offset set?
//        if ($query->offset) {
//            $offset += $query->offset;
//        }
//
//        $last = $offset + $limit;
//
//        if ($last > $total) {
//            $last = $total;
//        }
//
//        $paginateVariable->first = $offset + 1;
//        $paginateVariable->last = $last;
//        $paginateVariable->total = $total;
//        $paginateVariable->currentPage = $currentPage;
//        $paginateVariable->totalPages = $totalPages;
//
//        // Fetch the elements
//        $originalOffset = $query->offset;
//        $query->offset = (int)$offset;
//        $elements = $query->all();
//        $query->offset = $originalOffset;
//
//        foreach($elements as $element) {
//            $geolocation = Json::decode($element->geolocation);
//            if (isset($geolocation['country_code'])) {
//                $element->geolocation = $geolocation;
//            } else {
//                $element->geolocation = null;
//            }
//            $element['element'] = $element->getElement();
//            $element['elementType'] = $element->getElementType();
//            $element['elementSource'] = $element->getElementSource();
//        }
//
//        return [$paginateVariable, $elements];
//    }







































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
        $review->parentId       = $request->getRequiredBodyParam('parentId');
        $review->ipAddress      = $request->getUserIP();
        $review->userAgent      = $request->getHeaders()->get('user-agent');

        // Get Display
        QARR::$plugin->elements->getDisplay($request, $fields, $review);

        // Get Element
        QARR::$plugin->elements->getElementRecord($request, $review);

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
