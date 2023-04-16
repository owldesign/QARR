<?php

namespace owldesign\qarr\controllers\campaigns;

use Craft;
use craft\errors\MissingComponentException;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\web\View;
use owldesign\qarr\errors\UserNotAllowedException;
use craft\web\Controller;
use owldesign\qarr\elements\Campaign;
use owldesign\qarr\models\DirectLink;
use owldesign\qarr\models\Display;
use owldesign\qarr\models\Rule;
use owldesign\qarr\QARR;
use yii\base\ExitException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DirectLinksController extends Controller
{

    /**
     * @var array
     */
    protected $allowAnonymous = true;

    // Public Properties
    // =========================================================================
    public $defaultTemplateExtensions = ['html', 'twig'];
    
    // Public Methods
    // =========================================================================

    /**
     * Index
     *
     * @param array $variables
     * @return Response
     */
    public function actionIndex(array $variables = []): Response
    {
        $variables['directLinks'] = QARR::$plugin->links->getAllLinks();

        return $this->renderTemplate('qarr/campaigns/direct/index', $variables);
    }

    /**
     * Edit
     *
     * @param int|null $directId
     * @param DirectLink|null $direct
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEdit(int $directId = null, DirectLink $direct = null): Response
    {
        $variables = [
            'directId' => $directId,
            'brandNewDirect' => false,
            'elementType' => Craft::$app->getRequest()->getQueryParam('elementType')
        ];

        if ($directId !== null) {
            if ($direct == null) {
                $direct = QARR::$plugin->getLinks()->getLinkById($directId);

                if ($direct->options) {
                    $variables['direct']['options'] = Json::decode($direct->options);
                }

                if ($direct->settings) {
                    $variables['direct']['settings'] = Json::decode($direct->settings);
                }

                if (!$direct) {
                    throw new NotFoundHttpException(QARR::t('Direct link not found'));
                }
            }

            $variables['elementType'] = get_class($direct->element);
            $variables['elementId'] = $direct->elementId;
            $variables['userId'] = $direct->userId;

            $variables['title'] = QARR::t('Edit Direct Link');
        } else {
            if ($direct === null) {
                $direct = new DirectLink();
                $variables['elementType'] = QARR::$plugin->elements->getElementTypeByName($variables['elementType']);
                $variables['brandNewDirect'] = true;
            }

            $variables['title'] = QARR::t('New Direct Link');
        }
        

        $this->_enforceEditRulePermissions($direct);

        $variables['direct']                = $direct;
        $variables['fullPageForm']          = true;
        $variables['continueEditingUrl']    = 'qarr/campaigns/direct/{id}';
        $variables['saveShortcutRedirect']  = $variables['continueEditingUrl'];

        return $this->renderTemplate('qarr/campaigns/direct/_edit', $variables);
    }

    /**
     * Save
     *
     * @return Response|null
     * @throws MissingComponentException
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $model              = $this->_getDirectLinkModel();
        $model->id          = $request->getBodyParam('id');
        $model->elementId   = isset($request->getBodyParam('elementId')[0]) ? $request->getBodyParam('elementId')[0] : null;
        $model->userId      = isset($request->getBodyParam('userId')[0]) ? $request->getBodyParam('userId')[0] : null;
        $model->enabled     = $request->getBodyParam('enabled');
        $model->type        = $request->getBodyParam('type');
        $model->slug        = $request->getBodyParam('slug');

        if ($request->getBodyParam('options')) {
            $model->options = Json::encode($request->getBodyParam('options'));
        }
        
        if ($request->getBodyParam('settings')) {
            $model->settings = Json::encode($request->getBodyParam('settings'));
        }

        // Permission enforcement
        $this->requirePermission('qarr:editCampaigns');

        // Validate
        $model->validate();


        if ($model->hasErrors()) {
            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $model->getErrors(),
                ]);
            }

            Craft::$app->getSession()->setError(QARR::t('Couldn’t save direct link.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'direct' => $model
            ]);

            return null;
        }

        QARR::$plugin->getLinks()->save($model);

        if ($request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'id' => $model->id,
                'slug' => $model->slug,
                'enabled' => $model->enabled,
                'options' => $model->options,
                'settings' => $model->settings,
            ]);
        }

        Craft::$app->getSession()->setNotice(QARR::t( 'Direct link saved.'));

        return $this->redirectToPostedUrl($model);
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

        $this->requirePermission('qarr:editCampaigns');

        $directLinkId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        QARR::$plugin->getLinks()->deleteLinkById($directLinkId);

        return $this->asJson(['success' => true]);
    }

    public function actionForm($slug = null): Response
    {
        if (!$slug) {
            return $this->redirect(Craft::$app->getRequest()->baseUrl);
        }

        $view       = Craft::$app->getView();
        $model      = QARR::$plugin->links->getLinkById(QARR::$plugin->encrypt->decode($slug));
        $user       = $model->user;
        $element    = $model->element;

        if (!$model->enabled) {
            return $this->redirect(Craft::$app->getRequest()->baseUrl);
        }

        $this->_enforceActionPermissions($user);

        $variables = [
            'model'     => $model,
            'user'      => $user,
            'element'   => $element
        ];

        $path = $view->getTemplatesPath() . DIRECTORY_SEPARATOR . 'qarr' . DIRECTORY_SEPARATOR . 'direct';

        if ($model->type == 'review') {
            $customFile = $this->_resolveTemplate($path, 'review');
        } else {
            $customFile = $this->_resolveTemplate($path, 'question');
        }

        if ($customFile) {
            return $this->renderTemplate($customFile, $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            $view->setTemplateMode(View::TEMPLATE_MODE_CP);
            if ($model->type == 'review') {
                return $this->renderTemplate('qarr/campaigns/direct/review', $variables);
            } else {
                return $this->renderTemplate('qarr/campaigns/direct/question', $variables);
            }
            $view->setTemplateMode($oldPath);
        }

    }

    public function actionGetElementInfo(): Response
    {
        $this->requirePostRequest();

        $elementId  = Craft::$app->getRequest()->getParam('elementId');
        $element    = Craft::$app->getElements()->getElementById($elementId);

        return $this->asJson([
            'success' => true,
            'elementId' => $elementId,
            'element' => $element,
            'class' => $element->displayName(),
        ]);
    }

    // Private Methods
    // =========================================================================

    private function _getDirectLinkModel(): DirectLink
    {
        $directId = Craft::$app->getRequest()->getBodyParam('id');

        if ($directId) {
            $direct = QARR::$plugin->getLinks()->getLinkById($directId);

            if (!$direct) {
                throw new NotFoundHttpException('Direct link not found');
            }
        } else {
            $direct = new DirectLink();
        }

        return $direct;
    }

    /**
     * @param DirectLink $campaign
     * @throws ForbiddenHttpException
     */
    private function _enforceEditRulePermissions(DirectLink $direct)
    {
        $this->requirePermission('qarr:editCampaigns');
    }

    /**
     * @param $user
     * @return bool
     * @throws UserNotAllowedException
     */
    private function _enforceActionPermissions($user)
    {
        $currentUser = Craft::$app->getUser()->getIdentity();

        if ($currentUser->id === $user->id || $currentUser->admin) {
            return true;
        }


        throw new UserNotAllowedException();
    }

    /**
     * Function to get custom templates path
     *
     * @param string $path
     * @param string $name
     * @return string
     */
    private function _resolveTemplate(string $path, string $name)
    {
        foreach ($this->defaultTemplateExtensions as $extension) {
            $testPath = $path . DIRECTORY_SEPARATOR . $name . '.' . $extension;

            if (is_file($testPath)) {
                return 'qarr' . DIRECTORY_SEPARATOR . 'direct' . DIRECTORY_SEPARATOR . $name . '.' . $extension;
            }
        }
    }
}