<?php

namespace owldesign\qarr\controllers\campaigns;

use Craft;
use craft\errors\MissingComponentException;
use craft\helpers\Json;
use craft\helpers\StringHelper;
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
        $variables = [];
        $variables['brandNewDirect'] = false;

        if ($directId !== null) {
            if ($direct == null) {
                $direct = QARR::$plugin->links->getLinkById($directId);

                if ($direct->options) {
                    $variables['direct']['options'] = Json::decode($direct->options);
                }

                if ($direct->settings) {
                    $variables['direct']['settings'] = Json::decode($direct->settings);
                }

                if (!$direct) {
                    throw new NotFoundHttpException(QARR::t('Direct link not found'));
                }

                $variables['targetElementType'] = get_class($direct->element);
                $variables['targetUserElementType'] = get_class($direct->user);

                $variables['targetElement'] = $direct->element;
                $variables['targetUser'] = $direct->user;
            }

            $variables['subTitle'] = trim($direct->title) ?: QARR::t('Edit Direct Link');
        } else {
            if ($direct === null) {
                $direct = new DirectLink();
                $variables['brandNewDirect'] = true;
            }

            $variables['subTitle'] = QARR::t('New Direct Link');
        }

        $variables['directId']  = $directId;
        $variables['direct']    = $direct;


        $this->_enforceEditRulePermissions($direct);

        $type                       = Craft::$app->getRequest()->getQueryString();
        $variables['type']          = $type;
        $variables['elementType']   = QARR::$plugin->elements->getElementTypeByName($type);

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

        $model              = new DirectLink();
        $model->id          = $request->getBodyParam('directId');
        $model->elementId   = $request->getBodyParam('element')[0];
        $model->userId      = $request->getBodyParam('user')[0];
        $model->enabled     = $request->getBodyParam('enabled');
        $model->type        = $request->getBodyParam('type');
        $model->link        = $request->getBodyParam('link');

        if ($request->getBodyParam('options')) {
            $model->options = Json::encode($request->getBodyParam('options'));
        }

        if ($request->getBodyParam('settings')) {
            $model->settings = Json::encode($request->getBodyParam('settings'));
        }

        if ($model->userId) {
            $model->title       = $this->_buildTitle($model);
        }

        // Permission enforcement
        $this->requirePermission('qarr:editCampaigns');

        // Validate
        $model->validate();

        if (!$model->hasErrors() && QARR::$plugin->links->save($model)) {
            Craft::$app->getSession()->setNotice(QARR::t('Direct link saved.'));
            return $this->redirectToPostedUrl($model);
        }

        Craft::$app->getSession()->setError(QARR::t('Cannot save direct link.'));

        Craft::$app->getUrlManager()->setRouteParams([
            'direct' => $model,
            'errors' => $model->getErrors(),
        ]);

        return null;
    }

    /**
     * Display for Direct Links
     *
     * @return Response
     * @throws UserNotAllowedException
     */
    public function actionDisplay(): Response
    {
        $params = Craft::$app->getRequest()->getQueryParams();

        $user       = Craft::$app->getUsers()->getUserById($params['userId']);
        $element    = Craft::$app->getElements()->getElementById($params['elementId']);

        $this->_enforceActionPermissions($user);


        $variables = [
            'user' => $user,
            'element' => $element
        ];

        // Site Url
        return $this->renderTemplate('_qarr/direct/_display', $variables);

        // CP
//        return $this->renderTemplate('qarr/campaigns/direct/_display', $variables);
    }

    // Private Methods
    // =========================================================================

    private function _buildTitle($model)
    {
        $string = QARR::t('Link for') . ' ' . $model->user->fullName;

        return $string;
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

        if ($currentUser->id === $user->id) {
            return true;
        }


        throw new UserNotAllowedException();
    }
}