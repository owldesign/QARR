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

use craft\helpers\UrlHelper;
use owldesign\qarr\QARR;

use Craft;
use craft\web\Controller;
use craft\web\View;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CorrespondenceController extends Controller
{
    protected $allowAnonymous = ['actionGateKeeper', 'actionCheckPassword', 'actionIndex', 'actionIndex', 'actionSendMail'];

    public function actionGateKeeper($variables = null)
    {
        $params = Craft::$app->request->queryParams;

        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $template = $this->renderTemplate('qarr/correspondence/gatekeeper');
        Craft::$app->view->setTemplateMode($oldPath);

        return $template;
    }

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

    public function actionSendMail()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $variables = [];
        $variables['type']          = Craft::$app->getRequest()->getBodyParam('type');
        $variables['entryId']       = Craft::$app->getRequest()->getBodyParam('entryId');
        $variables['subject']       = Craft::$app->getRequest()->getBodyParam('subject');
        $variables['message']       = Craft::$app->getRequest()->getBodyParam('message');
        $variables['allowReplies']  = Craft::$app->getRequest()->getBodyParam('allowReplies');

        $entry = QARR::$plugin->reviews->getEntryById($variables['entryId']);
        $variables['entry'] = $entry;
        $variables['element'] = $entry->element;

        $variables['websiteName'] = Craft::$app->sites->currentSite->name;

        if (!$entry) {
            return false;
        }

        $variables['password'] = Craft::$app->getSecurity()->generateRandomString(8);
        $variables['privateUrl'] = UrlHelper::siteUrl('/qarr/correspondence?email='.$entry->emailAddress.'&type='.$variables['type'].'&elementId='.$entry->id);

        $template = Craft::$app->view->renderTemplate('qarr/_components/correspondence/email-template', $variables);

        if (QARR::$plugin->correspondence->sendMail($variables, $entry, $template, $variables['subject'], $entry->emailAddress)) {
            return $this->asJson([
                'success' => true,
            ]);

        } else {
            return $this->asJson([
                'success' => false,
            ]);
        }

    }
}