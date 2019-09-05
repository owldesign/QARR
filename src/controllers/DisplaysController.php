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

use owldesign\qarr\QARR;
use owldesign\qarr\elements\Display;

use Craft;
use craft\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class DisplaysController
 * @package owldesign\qarr\controllers
 */
class DisplaysController extends Controller
{
    // Protected Properties
    // =========================================================================

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * Index page
     *
     * @param array $variables
     */
    public function actionIndex(array $variables = [])
    {
        $variables['displays'] = QARR::$plugin->displays->getAllDisplays();

        QARR::$plugin->routeTemplate('displays/index', $variables);
    }

    /**
     * Edit
     *
     * @param int|null $displayId
     * @param Display|null $display
     * @throws NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit(int $displayId = null, Display $display = null)
    {
        $variables = [
            'displayId' => $displayId,
            'brandNewDisplay' => false
        ];

        if ($displayId !== null) {
            if ($display === null) {
                $display = QARR::$plugin->displays->getDisplayById($displayId);

                if (!$display) {
                    throw new NotFoundHttpException(QARR::t('Display not found'));
                }
            }

            $variables['title'] = $display->name;

        } else {
            if ($display === null) {
                $display = new Display();
                $variables['brandNewDisplay'] = true;
            }

            $variables['title'] = QARR::t('Create a new display');
        }

        $this->_enforceEditDisplayPermissions($display);

        $variables['display'] = $display;
        $variables['fullPageForm'] = true;
        $variables['continueEditingUrl'] = 'qarr/displays/{id}';
        $variables['saveShortcutRedirect'] = $variables['continueEditingUrl'];

        QARR::$plugin->routeTemplate('displays/_edit', $variables);
    }

    /**
     * Save
     *
     * @return Response|null
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $display                = new Display();
        $display->id            = $request->getBodyParam('displayId');
        $display->name          = $request->getBodyParam('name');
        $display->handle        = $request->getBodyParam('handle');
        $display->titleFormat   = $request->getBodyParam('titleFormat');
        $display->enabled       = (bool)$request->getBodyParam('enabled');
        $display->options       = $request->getBodyParam('options');
        $display->settings      = $request->getBodyParam('settings');

        // Permission enforcement
        $this->_enforceEditDisplayPermissions($display);

        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = Display::class;
        $display->setFieldLayout($fieldLayout);

        if (!QARR::$plugin->displays->saveDisplay($display)) {
            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $display->getErrors(),
                ]);
            }

            Craft::$app->getSession()->setError(QARR::t('Couldn’t save display.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'display' => $display
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(QARR::t('Display saved.'));

        return $this->redirectToPostedUrl($display);
    }

    /**
     * Delete
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $this->requirePermission('qarr:deleteDisplays');

        $displayId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        QARR::$plugin->displays->deleteDisplayById($displayId);

        return $this->asJson(['success' => true]);
    }

    // Private Methods
    // =========================================================================

    /**
     * @param Display $display
     * @throws \yii\web\ForbiddenHttpException
     */
    private function _enforceEditDisplayPermissions(Display $display)
    {
        $this->requirePermission('qarr:editDisplays');
    }

    /**
     * @return Display
     * @throws NotFoundHttpException
     */
    private function _getDisplayModel(): Display
    {
        $displayId = Craft::$app->getRequest()->getBodyParam('displayId');

        if ($displayId) {
            $display = QARR::$plugin->displays->getDisplayById($displayId);

            if (!$display) {
                throw new NotFoundHttpException('Display not found');
            }
        } else {
            $display = new Display();
        }

        return $display;
    }

    /**
     * @param Display $display
     */
    private function _populateDisplayModel(Display $display)
    {
        $request = Craft::$app->getRequest();

        $display->enabled           = (bool)$request->getBodyParam('enabled', $display->enabled);
        $display->name              = $request->getBodyParam('name', $display->name);
        $display->handle            = $request->getBodyParam('handle', $display->handle);
        $display->titleFormat       = $request->getBodyParam('titleFormat', $display->titleFormat);
        $display->options           = $request->getBodyParam('options', $display->options);
        $display->settings          = $request->getBodyParam('settings', $display->settings);
    }

}
