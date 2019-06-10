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

use craft\commerce\elements\Product;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use owldesign\qarr\models\Reply;
use owldesign\qarr\QARR;

use Craft;
use craft\web\View;
use craft\web\Controller;
use yii\web\NotFoundHttpException;

class FrontendController extends Controller
{
    protected $allowAnonymous = true;

    public function actionGetModalContent()
    {
        $this->requirePostRequest();

        $variables              = [];
        $variables['type']      = Craft::$app->getRequest()->getBodyParam('type');
        $variables['displayId'] = Craft::$app->getRequest()->getBodyParam('displayId');
        $variables['elementId'] = Craft::$app->getRequest()->getBodyParam('elementId');
        
        $element = Craft::$app->getElements()->getElementById($variables['elementId']);
        $type = $this->getElementType($element);
        
        if ($type == 'entry') {
            $variables['parentId']  = $element->sectionId;
        } elseif ($type == 'product') {
            $variables['parentId']  = $element->typeId;
        }
        
        // Display
        if ($variables['displayId']) {
            $variables['display'] = QARR::$plugin->displays->getDisplayById($variables['displayId']);
        }

        if ($variables['type'] === 'review') {
            $variables['title'] = QARR::t('Leave Review');
        } else {
            $variables['title'] = QARR::t('Ask Question');
        }
        
        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        $template = Craft::$app->view->renderTemplate('qarr/frontend/_includes/_feedback-modal', $variables);

        Craft::$app->view->setTemplateMode($oldPath);

        return $this->asJson([
            'success' => true,
            'template'   => $template
        ]);
    }

    private function getElementType($element)
    {
        switch (true) {
            case $element instanceof Entry:
                return 'entry';
                break;
            case $element instanceof Category:
                return 'category';
                break;
            case $element instanceof Asset:
                return 'asset';
                break;
            case $element instanceof Product:
                return 'product';
                break;
        }
    }
}