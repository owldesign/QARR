<?php

namespace owldesign\qarr\web\twig;

use owldesign\qarr\QARR;
use owldesign\qarr\elements\Question;
use owldesign\qarr\elements\db\QuestionQuery;
use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\db\ReviewQuery;
use owldesign\qarr\services\Rules;

use Craft;
use craft\web\View;
use craft\helpers\Template;
use craft\commerce\elements\Product;
use craft\events\DefineComponentsEvent;
use yii\base\Behavior;

class Variables extends Behavior
{
    /**
     * Displays reviews & questions form and entries on the page
     *
     * At minimum you must provide commerce product model.
     *
     * Basic usage:
     *
     * {{ craft.qarr.display(product) }}
     *
     * Advanced usage:
     *
     *
     * {{ craft.qarr.display(product, {
     *     limit: 3,
     *     pagination: 'infinite',
     *     reviews: {
     *         display: 'displayForProductReviews'
     *     },
     *     questions: false
     * }) }}
     *
     *
     * @param $product
     * @param null $variables
     * @return string|\Twig_Markup
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function display($product, $variables = null)
    {
        if(!$product instanceof Product) {
            return QARR::t('Product required, please provide craft\commerce\elements\Product instance.');
        }

        $limit              = null;
        $pagination         = 'arrows';
        $removePagination   = false;
        $removeReviews      = false;
        $removeQuestions    = false;

        // Remove Pagination
        if (isset($variables['pagination']) && $variables['pagination'] === false) {
            $removePagination = true;
        }

        // Pagination Style
        if (isset($variables['pagination']) && is_string($variables['pagination'])) {
            $pagination = $variables['pagination'];
        }

        // Remove Reviews
        if (isset($variables['reviews']) && $variables['reviews'] === false) {
            $removeReviews = true;
        }

        // Remove Questions
        if (isset($variables['questions']) && $variables['questions'] === false) {
            $removeQuestions = true;
        }

        // Limit
        if (isset($variables['limit']) && $variables['limit'] != '') {
            $limit = $variables['limit'];
        }

        // Reviews & Questions
        if ($removePagination) {
            $reviews = $removeReviews ? null : QARR::$plugin->elements->queryElements('reviews', $product->id, null, 'approved');
            $questions = $removeQuestions ? null : QARR::$plugin->elements->queryElements('questions', $product->id, null, 'approved');
        } else {
            $reviews = $removeReviews ? null : QARR::$plugin->elements->queryElements('reviews', $product->id, $limit, null, 'approved');
            $questions = $removeQuestions ? null : QARR::$plugin->elements->queryElements('questions', $product->id, $limit, null, 'approved');
        }

        // Displays
        $reviewsDisplay = isset($variables['reviews']['display']) && $variables['reviews']['display'] != '' ? QARR::$plugin->displays->getDisplayByHandle($variables['reviews']['display']) : null;
        $questionsDisplay = isset($variables['questions']['display']) && $variables['questions']['display'] != '' ? QARR::$plugin->displays->getDisplayByHandle($variables['questions']['display']) : null;

        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        $html = Craft::$app->view->renderTemplate('qarr/frontend/index', [
            'product' => $product,
            'includeReviews' => $removeReviews ? false : true,
            'includeQuestions' => $removeQuestions ? false : true,
            'includePagination' => $removePagination ? false : true,
            'pagination' => $pagination,
            'limit' => $limit,
            'reviews' => $reviews,
            'questions' => $questions,
            'reviewsDisplay' => $reviewsDisplay,
            'questionsDisplay' => $questionsDisplay,
        ]);

        Craft::$app->view->setTemplateMode($oldPath);

        return Template::raw($html);
    }

    /**
     * Get all reviews
     *
     * @param null $criteria
     * @return \craft\elements\db\ElementQueryInterface
     */
    public function reviews($criteria = null)
    {
        $query = Review::find();

        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query;
    }

    /**
     * Get all questions
     *
     * @param null $criteria
     * @return \craft\elements\db\ElementQueryInterface
     */
    public function questions($criteria = null)
    {
        $query = Question::find();

        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query;
    }

    /**
     * * Get count of elements by **productId**, **productTypeId** and **status**
     *
     * You can also include **Product Type ID** to get count specific to commerce product type.
     *
     * @param string $type
     * @param string $status
     * @param int|null $productId
     * @param int|null $productTypeId
     * @return mixed
     */
    public function getCount(string $type, string $status, int $productId = null, int $productTypeId = null)
    {
        $count = QARR::$plugin->elements->getCount($type, $status, $productId, $productTypeId);

        return $count;
    }

    /**
     * Entries by rating
     *
     * @param $productId
     * @return null
     */
    public function getEntriesByRating($productId)
    {
        if (!$productId) {
            return null;
        }

        $ratings = QARR::$plugin->elements->getEntriesByRating('approved', $productId);

        return $ratings;
    }

    /**
     * Average rating
     *
     * @param $productId
     * @return null
     */
    public function getAverageRating($productId)
    {
        if (!$productId) {
            return null;
        }

        return QARR::$plugin->elements->getAverageRating($productId);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getCookie($name)
    {
        return QARR::$plugin->cookies->get($name);
    }

    /**
     * Get url for the plugin
     *
     * @return mixed
     */
    public function pluginUrl()
    {
        return QARR::$plugin->pluginUrl;
    }

    /**
     * Get array value of given options
     *
     * @param $array
     * @return mixed
     */
    public function getArrayValue($array)
    {
        foreach ($array as $option) {
            if ($option->selected) {
                return $option->label;
            }
        }

        $result = null;
    }

    /**
     * List of allowed field types
     *
     * @return array
     */
    public function allowedFields()
    {
        return [
          'PlainText',
          'Checkboxes',
          'RadioButtons',
          'Dropdown',
          'MultiSelect',
          'Url',
        ];
    }
}
