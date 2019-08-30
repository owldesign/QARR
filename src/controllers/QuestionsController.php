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
use owldesign\qarr\elements\Question;

class QuestionsController extends Controller
{

    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = [
        'save' => self::ALLOW_ANONYMOUS_LIVE,
    ];

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * Edit
     *
     * @param int|null $questionId
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit(int $questionId = null): Response
    {
        if ($questionId) {
            $variables['entry'] = QARR::$plugin->questions->getEntryById($questionId);
            $variables['title'] = $variables['entry']->title;
        } else {
            throw new NotFoundHttpException(QARR::t('Entry not found'));
        }

        $entry = $variables['entry'];

        if ($entry->displayId) {
            $variables['fieldLayoutTabs'] = $entry->getFieldLayout()->getTabs();
        }

        $variables['correspondences'] = QARR::$plugin->correspondence->getCorrespondenceByParams($entry->emailAddress, 'questions', $entry->id);

        $this->_enforceEditPermissions($variables['entry']);

        $variables['fullPageForm'] = false;
        $variables['continueEditingUrl'] = 'qarr/reviews/{id}';
        $variables['saveShortcutRedirect'] = $variables['continueEditingUrl'];

        return $this->renderTemplate('qarr/questions/_edit', $variables);
    }

    /**
     * Save
     *
     * @return Response
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $question = new Question();
        Craft::$app->getContent()->populateElementContent($question);
        $fields                   = $request->getBodyParams()['fields'];
        $question->fullName       = $fields['fullName'];
        $question->emailAddress   = $fields['emailAddress'];
        $question->question       = $fields['question'];
        $question->ipAddress      = $request->getUserIP();
        $question->userAgent      = $request->getHeaders()->get('user-agent');

        // Get Display
        QARR::$plugin->elements->getDisplay($request, $fields, $question);

        // Set Element Data
        QARR::$plugin->elements->setElementData($request, $question);

        // Set Direct Link Data
        $directLinkId = $request->getBodyParam('directLinkId');

        $fieldsLocation = $request->getParam('fieldsLocation', 'fields');
        $question->setFieldValuesFromRequest($fieldsLocation);

        $question->validate();

        if (Craft::$app->getElements()->saveElement($question)) {
            $saved = true;
        } else {
            $saved = false;
        }

        if ($saved) {

            // Is this Direct Link Submission?
            if ($directLinkId) {
                QARR::$plugin->links->completeById($directLinkId, $question);
            }

            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => true,
                    'errors' => $question->getErrors(),
                    'message' => QARR::t('Submission successful.')
                ]);
            } else {
                $this->redirectToPostedUrl($question);
            }
        } else {
            if (Craft::$app->getRequest()->getIsAjax()) {
                return $this->asJson([
                    'success' => false,
                    'question' => $question,
                    'errors' => $question->getErrors(),
                    'message' => QARR::t('Submission failed.')
                ]);
            } else {
                Craft::$app->getUrlManager()->setRouteParams([
                    'question' => $question,
                    'errors' => $question->getErrors(),
                ]);
            }

        }
    }

    /**
     * Delete
     *
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
        $question = QARR::$plugin->questions->getEntryById($elementId);

        $this->_enforceEditPermissions();

        $answersIsDeleted = QARR::$plugin->answers->deleteAnswersByElement($question);
        $correspondenceIsDeleted = QARR::$plugin->correspondence->deleteCorrespondenceByElement($question);
        $questionIsDeleted = QARR::$plugin->questions->deleteEntry($question);

        if ($answersIsDeleted && $correspondenceIsDeleted && $questionIsDeleted) {
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
        $this->requirePermission('qarr:accessQuestions');
    }

}
