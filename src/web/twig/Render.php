<?php

namespace owldesign\qarr\web\twig;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use craft\web\View;
use owldesign\qarr\QARR;
use owldesign\qarr\web\assets\RenderFrontend;

class Render
{
    public array $defaultTemplateExtensions = ['html', 'twig'];
    public string $customTemplatesPath;

    public function __construct()
    {
        $view = Craft::$app->getView();
        $js = 'var QRAPI = {}; var QR = {}; QRAPI.actionUrl = "'. UrlHelper::actionUrl() .'"; QRAPI.csrfTokenName = "' . Craft::$app->getConfig()->general->csrfTokenName . '"; QRAPI.csrfTokenValue = "' . Craft::$app->getRequest()->csrfToken . '";';
        $view->registerJs($js, View::POS_HEAD);
        $view->registerAssetBundle(RenderFrontend::class);

        $this->customTemplatesPath = Craft::$app->view->getTemplatesPath() . DIRECTORY_SEPARATOR . 'qarr';
    }

    /**
     * Stars rating
     *
     * @param $model
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function stars($model)
    {
        $customFile = $this->_resolveTemplate($this->customTemplatesPath, 'stars');
        $variables = [];

        if (isset($model->rating)) {
            $variables['averageRating'] = $model->rating;
        } else {
            $variables['averageRating'] = $this->_getAverageRating($model->id);
        }

        if ($customFile) {
            $html = Craft::$app->view->renderTemplate($customFile, $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = Craft::$app->view->renderTemplate('qarr/frontend/render/stars', $variables);
            Craft::$app->view->setTemplateMode($oldPath);
        }

        echo Template::raw($html);
    }

    /**
     * Reviews count
     *
     * @param $model
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function reviewsCount($model)
    {
        $customFile = $this->_resolveTemplate($this->customTemplatesPath, 'reviews-count');

        $variables = [
            'total' => $this->_getCount('reviews', 'approved', $model->id),
        ];

        if ($customFile) {
            $html = Craft::$app->view->renderTemplate($customFile, $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = Craft::$app->view->renderTemplate('qarr/frontend/render/reviews-count', $variables);
            Craft::$app->view->setTemplateMode($oldPath);
        }

        echo Template::raw($html);
    }

    /**
     * Average rating block
     *
     * @param $model
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function averageRating($model)
    {
        $customFile = $this->_resolveTemplate($this->customTemplatesPath, 'average-rating');

        $variables = [
            'averageRating' => $this->_getAverageRating($model->id),
        ];

        if ($customFile) {
            $html = Craft::$app->view->renderTemplate($customFile, $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = Craft::$app->view->renderTemplate('qarr/frontend/render/average-rating', $variables);
            Craft::$app->view->setTemplateMode($oldPath);
        }

        echo Template::raw($html);
    }

    public function sort($type = 'reviews') {
        $customFile = $this->_resolveTemplate($this->customTemplatesPath, 'sort');

        $variables = [
            'type' => $type,
        ];

        if ($customFile) {
            $html = Craft::$app->view->renderTemplate($customFile, $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = Craft::$app->view->renderTemplate('qarr/frontend/render/sort', $variables);
            Craft::$app->view->setTemplateMode($oldPath);
        }

        echo Template::raw($html);
    }

    public function questions($model, $criteria = null)
    {
        $customFile = $this->_resolveTemplate($this->customTemplatesPath, 'questions');

        $element = 'owldesign\qarr\elements\Question';
        $order = $variables['order'] ?? 'dateCreated desc';
        $limit = $variables['limit'] ?? 12;
        $offset = $variables['offset'] ?? null;
        $status = 'approved';

        $query = QARR::$plugin->elements->queryElements($element, $order, $model->id ?? null, $limit, $offset, $status);

        $variables = [
            'model' => $model,
            'elements' => $query,
            'pagination' => $variables['pagination'] ?? true,
            'paginationStyle' => $variables['paginationStyle'] ?? 'arrows',
            'template' => $customFile,
        ];

        if ($customFile) {
            $html = Craft::$app->view->renderTemplate($customFile, $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = Craft::$app->view->renderTemplate('qarr/frontend/render/questions', $variables);
            Craft::$app->view->setTemplateMode($oldPath);
        }

        echo Template::raw($html);
    }

    /**
     * Reviews
     *
     * @param null $model
     * @param null $settings
     */
    public function reviews($model = null, $settings = null)
    {
        $id = 'qarr-elements-' . Html::id();
        $elementType = 'owldesign\\\qarr\\\elements\\\Review';

        if ($model) {
            $settings['criteria']['elementId'] = $model->id;
        }

        $js = "new QR.ElementIndex('". $id ."', '". $elementType ."', '". Json::encode($settings) . "')";

        $view = Craft::$app->getView();
        $view->registerJs($js);

        echo '<div id="'. $id .'" data-qarr-elements></div>';
    }

    /**
     * Get pagination html
     *
     * @param $pageInfo
     * @param $entriesContainer
     * @param string $paginationStyle
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    public function pagination($paginationStyle = 'arrows')
    {
        $customFile = $this->_resolveTemplate($this->customTemplatesPath, 'pagination');

        $variables = [
            'paginationStyle' => $paginationStyle,
        ];

        if ($customFile) {
            $html = Craft::$app->view->renderTemplate($customFile, $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = Craft::$app->view->renderTemplate('qarr/frontend/render/pagination', $variables);
            Craft::$app->view->setTemplateMode($oldPath);
        }

        echo Template::raw($html);
    }

    /**
     * * Get count of elements by **elementId**, **productTypeId** and **status**
     *
     * You can also include **Product Type ID** to get count specific to commerce product type.
     *
     * @param string $type
     * @param string $status
     * @param int|null $elementId
     * @param int|null $elementType
     * @param int|null $elementTypeId
     * @return mixed
     */
    public function _getCount(string $type, string $status, int $elementId = null, $elementType = null, $elementTypeId = null)
    {
        return QARR::$plugin->elements->getCount($type, $status, $elementId, $elementType, $elementTypeId);
    }

    /**
     * Average rating
     *
     * @param $elementId
     * @return null
     */
    public function _getAverageRating($elementId)
    {
        if (!$elementId) {
            return null;
        }

        return QARR::$plugin->elements->getAverageRating($elementId);
    }

    /**
     * Function to get custom templates path
     *
     * @param string $path
     * @param string $name
     * @return string
     */
    public function _resolveTemplate(string $path, string $name): string
    {
        foreach ($this->defaultTemplateExtensions as $extension) {
            $testPath = $path . DIRECTORY_SEPARATOR . $name . '.' . $extension;

            if (is_file($testPath)) {
                return 'qarr' . DIRECTORY_SEPARATOR . $name . '.' . $extension;
            }
        }

        return false;
    }
}