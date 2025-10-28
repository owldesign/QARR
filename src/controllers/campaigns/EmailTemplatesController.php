<?php

namespace owldesign\qarr\controllers\campaigns;

use Craft;
use craft\errors\InvalidPluginException;
use craft\errors\MissingComponentException;
use craft\helpers\Json;
use craft\db\Query;
use craft\web\View;
use craft\helpers\Template;
use craft\web\Controller;
use owldesign\qarr\elements\Question;
use owldesign\qarr\elements\Review;
use owldesign\qarr\errors\UserNotAllowedException;
use owldesign\qarr\models\EmailTemplate;
use owldesign\qarr\QARR;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\Markdown;

class EmailTemplatesController extends Controller
{

    protected int|bool|array $allowAnonymous = true;

    // Public Properties
    // =========================================================================
    public array $defaultTemplateExtensions = ['html', 'twig'];
    public $_cachedElement;

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
        $variables['templates'] = QARR::$plugin->getEmailTemplates()->getAllEmailTemplates();

        return $this->renderTemplate('qarr/campaigns/email-templates/index', $variables);
    }

    /**
     * Edit
     * @param int|null $templateId
     * @param EmailTemplate|null $template
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     */
    public function actionEdit(int $templateId = null, EmailTemplate $template = null): Response
    {
        $variables = [
            'templateId' => $templateId,
            'brandNewTemplate' => false
        ];

        if ($templateId !== null) {
            if ($template == null) {
                $template = QARR::$plugin->getEmailTemplates()->getEmailTemplateById($templateId);

                $variables['bodyRaw'] = $template->bodyRaw;
                $variables['footerRaw'] = $template->footerRaw;

                if ($template->settings) {
                    $variables['settings'] = $template->settings;
                }

                if ($template->options) {
                    $variables['options'] = $template->options;
                }

                if (!$template) {
                    throw new NotFoundHttpException(QARR::t('Email template not found'));
                }
            }

            $variables['title'] = QARR::t('Edit Email Template');
        } else {
            if ($template === null) {
                $template = new EmailTemplate();
                $variables['brandNewTemplate'] = true;
            }

            $variables['title'] = QARR::t('New Email Template');
        }

        $this->_enforceEditRulePermissions($template);

        $variables['template'] = $template;
        $variables['fullPageForm'] = true;
        $variables['continueEditingUrl'] = 'qarr/campaigns/email-templates/{id}';
        $variables['saveShortcutRedirect'] = $variables['continueEditingUrl'];

        return $this->renderTemplate('qarr/campaigns/email-templates/_edit', $variables);
    }

    /**
     * Get email template preview
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws InvalidPluginException
     * @throws \Throwable
     */
    public function actionGetEmailTemplatePreview(): Response
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $variables = [
            'elementType'   => $request->getBodyParam('elementType'),
            'elementId'     => $request->getBodyParam('elementId'),
            'templatePath'  => $request->getBodyParam('templatePath'),
            'settings'      => $request->getBodyParam('settings'),
            'body'          => $request->getBodyParam('body'),
            'footer'        => $request->getBodyParam('footer'),
            'plugin'        => Craft::$app->getPlugins()->getPluginInfo('qarr'),
            'forceUpdate'   => $request->getBodyParam('forceUpdate'),
        ];

        $this->_getRandomElementByType($variables['elementType'], $variables['elementId'], $variables['forceUpdate']);
        $availableFields = $this->_cachedElement;

        $body = Craft::$app->getView()->renderObjectTemplate($variables['body'], $availableFields);
        $footer = Craft::$app->getView()->renderObjectTemplate($variables['footer'], $availableFields);
        $variables['body'] = Template::raw(Markdown::process($body));
        $variables['footer'] = Template::raw(Markdown::process($footer));

        if ($variables['templatePath'] == 'simple') {
            $template = Craft::$app->view->renderTemplate('qarr/campaigns/email-templates/_templates/simple', $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
            $template = Craft::$app->view->renderTemplate('_qarr/emails/' . $variables['templatePath'], $variables);
            Craft::$app->view->setTemplateMode($oldPath);
        }

        return $this->asJson([
            'template' => $template,
            'elementId' => $this->_cachedElement->id
        ]);
    }

    /**
     * Save
     *
     * @return Response|null
     * @throws MissingComponentException
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        $request                = Craft::$app->getRequest();
        $model                  = $this->_getEmailTemplateModel();
        $model->id              = $request->getBodyParam('id');
        $model->name            = $request->getBodyParam('name');
        $model->handle          = $request->getBodyParam('handle');
        $model->templatePath    = $request->getBodyParam('templatePath');
        $model->enabled         = $request->getBodyParam('enabled');
        $settings               = $request->getBodyParam('settings');
        $model->settings        = [
            'bgColor'           => $settings['bgColor'],
            'containerColor'    => $settings['containerColor'],
        ];

        $model->bodyRaw         = $request->getBodyParam('body');
        $model->footerRaw       = $request->getBodyParam('footer');
        $model->bodyHtml        = Template::raw(Markdown::process($model->bodyRaw));
        $model->footerHtml      = Template::raw(Markdown::process($model->footerRaw));

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

            Craft::$app->getSession()->setError(QARR::t('Couldn’t save email template.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'template' => $model
            ]);

            return null;
        }

        QARR::$plugin->getEmailTemplates()->save($model);

        if ($request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
            ]);
        }

        Craft::$app->getSession()->setNotice(QARR::t('Email template saved.'));

        return $this->redirectToPostedUrl($model);
    }

    /**
     * Delete
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
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

    public function actionGetAllEmailTemplates(): Response
    {
        $templates = QARR::$plugin->getEmailTemplates()->getAllEmailTemplates();

        $options = [];

        foreach ($templates as $template) {
            $options[] = [
                'id' => $template->id,
                'name' => $template->name,
                'handle' => $template->handle,
            ];
        }

        return $this->asJson([
            'success' => true,
            'options' => $options
        ]);
    }

    // Private Methods
    // =========================================================================
    private function _getRandomElementByType($type, $elementId, $forceUpdate): void
    {
        if ($forceUpdate) {
            if ($type == 'review') {
                $this->_cachedElement = Review::find()
                    ->orderBy('RAND()')
                    ->one();
            } else {
                $this->_cachedElement = Question::find()
                    ->orderBy('RAND()')
                    ->one();
            }
        } else {
            if ($elementId) {
                $this->_cachedElement = Craft::$app->getElements()->getElementById($elementId);

                return;
            }

            if ($type == 'review') {
                $this->_cachedElement = Review::find()
                    ->orderBy('RAND()')
                    ->one();
            } else {
                $this->_cachedElement = Question::find()
                    ->orderBy('RAND()')
                    ->one();
            }
        }
    }

    private function _getEmailTemplateModel(): EmailTemplate
    {
        $templateId = Craft::$app->getRequest()->getBodyParam('id');

        if ($templateId) {
            $template = QARR::$plugin->getEmailTemplates()->getEmailTemplateById($templateId);

            if (!$template) {
                throw new NotFoundHttpException('Email Template not found');
            }
        } else {
            $template = new EmailTemplate();
        }

        return $template;
    }

    /**
     * @param EmailTemplate $template
     * @throws ForbiddenHttpException
     */
    private function _enforceEditRulePermissions(EmailTemplate $template): void
    {
        $this->requirePermission('qarr:editCampaigns');
    }

    /**
     * @param $user
     * @return bool
     * @throws UserNotAllowedException
     */
    private function _enforceActionPermissions($user): bool
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
    private function _resolveTemplate(string $path, string $name): string
    {
        foreach ($this->defaultTemplateExtensions as $extension) {
            $testPath = $path . DIRECTORY_SEPARATOR . $name . '.' . $extension;

            if (is_file($testPath)) {
                return 'qarr' . DIRECTORY_SEPARATOR . 'direct' . DIRECTORY_SEPARATOR . $name . '.' . $extension;
            }
        }
    }
}