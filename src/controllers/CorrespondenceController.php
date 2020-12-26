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

use craft\errors\MissingComponentException;
use craft\helpers\DateTimeHelper;
use craft\helpers\Html;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use owldesign\qarr\QARR;

use Craft;
use craft\web\Controller;
use craft\web\View;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\helpers\Markdown;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CorrespondenceController extends Controller
{
    protected $allowAnonymous = [
        'index' => self::ALLOW_ANONYMOUS_LIVE,
        'gate-keeper' => self::ALLOW_ANONYMOUS_LIVE,
        'check-password' => self::ALLOW_ANONYMOUS_LIVE,
        'send-mail' => self::ALLOW_ANONYMOUS_LIVE,
    ];

    /**
     * Gate keeper
     * TODO: Under development
     *
     * @param null $variables
     * @return Response
     * @throws Exception
     */
    public function actionGateKeeper($variables = null)
    {
        $params = Craft::$app->request->queryParams;

        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $template = $this->renderTemplate('qarr/correspondence/gatekeeper');
        Craft::$app->view->setTemplateMode($oldPath);

        return $template;
    }

    /**
     * Check password
     * TODO: Under development
     *
     * @return bool|Response
     * @throws MissingComponentException
     * @throws Exception
     * @throws BadRequestHttpException
     */
    public function actionCheckPassword()
    {
        $this->requirePostRequest();

        $request    = Craft::$app->getRequest();
        $password   = Craft::$app->getRequest()->getBodyParam('password');
        $email      = Craft::$app->getRequest()->getBodyParam('email');
        $type       = Craft::$app->getRequest()->getBodyParam('type');
        $elementId  = Craft::$app->getRequest()->getBodyParam('elementId');

        $correspondence = QARR::$plugin->correspondence->getCorrespondenceByParams($email, $type, $elementId);

        if (!$correspondence) {
            return false;
        }

        $allowedAccess = $correspondence['password'] === $password;

        $session = Craft::$app->getSession();
        $session->set('correspondence', ['email' => $email, 'type' => $type, 'elementId' => $elementId]);

        if ($allowedAccess) {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $template = $this->renderTemplate('qarr/correspondence/index', ['correspondence' => $correspondence]);
            Craft::$app->view->setTemplateMode($oldPath);
            return $template;
        }

    }

    /**
     * Send Mail
     *
     * @return bool|Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \Throwable
     * @throws \craft\errors\InvalidPluginException
     */
    public function actionSendMail()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $variables = [];
        $variables['type']          = Craft::$app->getRequest()->getBodyParam('type');
        $variables['entryId']       = Craft::$app->getRequest()->getBodyParam('entryId');
        $templateId                 = Craft::$app->getRequest()->getBodyParam('templateId');
        $variables['allowReplies']  = Craft::$app->getRequest()->getBodyParam('allowReplies');
        $entry                      = Craft::$app->getElements()->getElementById($variables['entryId']);
        $variables['entry']         = $entry;
        $variables['element']       = $entry->element;
        $variables['websiteName']   = Craft::$app->sites->currentSite->name;
        $variables['plugin']        = Craft::$app->getPlugins()->getPluginInfo('qarr');
        $variables['settings']      = null;

        if (!$entry) { return false; }

        if ($variables['type'] == 'reviews') {
            $variables['feedback'] = $entry->feedback;
        } else {
            $variables['feedback'] = $entry->question;
        }

        $variables['password']      = Craft::$app->getSecurity()->generateRandomString(8);
        $variables['privateUrl']    = UrlHelper::siteUrl('/qarr/correspondence?email='.$entry->emailAddress.'&type='.$variables['type'].'&elementId='.$entry->id);
        $variables['sentDate']      = DateTimeHelper::currentUTCDateTime();

        // Render Entry Variables for Subject & Message
        $subject                    = Craft::$app->getRequest()->getBodyParam('subject');
        $message                    = Craft::$app->getRequest()->getBodyParam('message');
        $variables['subject']       = Craft::$app->getView()->renderObjectTemplate($subject, $entry);
        $variables['message']       = Template::raw(Markdown::process(Craft::$app->getView()->renderObjectTemplate($message, $entry)));

        // Templating
        $emailTemplate              = QARR::$plugin->getEmailTemplates()->getEmailTemplateById($templateId);

        if ($emailTemplate) {
            $variables['settings']  = $emailTemplate->settings;
            $variables['emailTemplateId'] = $templateId;

            $body                   = Craft::$app->getView()->renderObjectTemplate($emailTemplate->bodyRaw, $entry);
            $footer                 = Craft::$app->getView()->renderObjectTemplate($emailTemplate->footerRaw, $entry);
            $variables['body']      = Template::raw(Markdown::process($body));
            $variables['footer']    = Template::raw(Markdown::process($footer));

            if ($emailTemplate->templatePath) {
                // Custom Email Template
                $oldPath = Craft::$app->view->getTemplateMode();
                Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
                $template = Craft::$app->view->renderTemplate('_qarr/emails/' . $emailTemplate->templatePath, $variables);
                Craft::$app->view->setTemplateMode($oldPath);
            } else {
                // Customized Template
                $template = Craft::$app->view->renderTemplate('qarr/campaigns/email-templates/_templates/simple', $variables);
            }
        } else {
            $template = Craft::$app->view->renderTemplate('qarr/campaigns/email-templates/_templates/simple', $variables);
        }


        if (QARR::$plugin->correspondence->sendMail($variables, $entry, $template, $variables['subject'], $entry->emailAddress)) {
            return $this->asJson([
                'success' => true,
                'entry' => $variables
            ]);

        } else {
            return $this->asJson([
                'success' => false,
            ]);
        }
    }
}