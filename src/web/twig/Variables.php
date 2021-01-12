<?php

namespace owldesign\qarr\web\twig;

use craft\elements\db\ElementQueryInterface;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\Question;
use owldesign\qarr\elements\Review;

use Craft;
use craft\web\View;
use craft\helpers\Template;
use yii\base\Behavior;
use yii\base\Exception;

class Variables extends Behavior
{
    public $defaultTemplateExtensions = ['html', 'twig'];

    /**
     * Render pre-build templates
     *
     * @param $element
     * @param null $variables
     * @throws Exception
     */
    public function display($element, $variables = null)
    {
        $limit = null;
        $pagination = 'arrows';
        $removePagination = false;
        $removeReviews = false;
        $removeQuestions = false;
        $showButtons = true;
        $showTabs = true;
        $showSort = true;
        $showAbuseReport = true;

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

        // Hide Buttons
        if (isset($variables['showButtons']) && $variables['showButtons'] === false) {
            $showButtons = false;
        }

        // Show Tabs
        if (isset($variables['showTabs']) && $variables['showTabs'] === false) {
            $showTabs = false;
        }

        // Show Sort
        if (isset($variables['showSort']) && $variables['showSort'] === false) {
            $showSort = false;
        }

        // Show Abuse Reporting
        if (isset($variables['abuse']) && $variables['abuse'] === false) {
            $showAbuseReport = false;
        }


        // Reviews & Questions
        if ($removePagination) {
            if ($element === '*') {
                $reviews = $removeReviews ? null : QARR::$plugin->elements->queryElements('reviews', null, null, 'approved');
                $questions = $removeQuestions ? null : QARR::$plugin->elements->queryElements('questions', null, null, 'approved');
            } else {
                $reviews = $removeReviews ? null : QARR::$plugin->elements->queryElements('reviews', $element->id, null, 'approved');
                $questions = $removeQuestions ? null : QARR::$plugin->elements->queryElements('questions', $element->id, null, 'approved');
            }
        } else {
            if ($element === '*') {
                $reviews = $removeReviews ? null : QARR::$plugin->elements->queryElements('reviews', null, $limit, null, 'approved');
                $questions = $removeQuestions ? null : QARR::$plugin->elements->queryElements('questions', null, $limit, null, 'approved');
            } else {
                $reviews = $removeReviews ? null : QARR::$plugin->elements->queryElements('reviews', $element->id, $limit, null, 'approved');
                $questions = $removeQuestions ? null : QARR::$plugin->elements->queryElements('questions', $element->id, $limit, null, 'approved');
            }
        }

        // Displays
        $reviewsDisplay = isset($variables['reviews']['display']) && $variables['reviews']['display'] != '' ? QARR::$plugin->displays->getDisplayByHandle($variables['reviews']['display']) : null;
        $questionsDisplay = isset($variables['questions']['display']) && $variables['questions']['display'] != '' ? QARR::$plugin->displays->getDisplayByHandle($variables['questions']['display']) : null;

        $oldPath = Craft::$app->view->getTemplateMode();
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        if ($element === '*') {
            $html = Craft::$app->view->renderTemplate('qarr/frontend/index-all', [
                'element' => $element,
                'includeReviews' => $removeReviews ? false : true,
                'includeQuestions' => $removeQuestions ? false : true,
                'includePagination' => $removePagination ? false : true,
                'pagination' => $pagination,
                'limit' => $limit,
                'reviews' => $reviews,
                'questions' => $questions,
                'reviewsDisplay' => $reviewsDisplay,
                'questionsDisplay' => $questionsDisplay,
                'showButtons' => $showButtons,
                'showTabs' => $showTabs,
                'showSort' => $showSort,
                'showAbuseReporting' => $showAbuseReport,
            ]);
        } else {
            $html = Craft::$app->view->renderTemplate('qarr/frontend/index', [
                'element' => $element,
                'includeReviews' => $removeReviews ? false : true,
                'includeQuestions' => $removeQuestions ? false : true,
                'includePagination' => $removePagination ? false : true,
                'pagination' => $pagination,
                'limit' => $limit,
                'reviews' => $reviews,
                'questions' => $questions,
                'reviewsDisplay' => $reviewsDisplay,
                'questionsDisplay' => $questionsDisplay,
                'showButtons' => $showButtons,
                'showTabs' => $showTabs,
                'showSort' => $showSort,
            ]);
        }

        Craft::$app->view->setTemplateMode($oldPath);

        return Template::raw($html);
    }

    /**
     * Get all reviews
     *
     * @param null $criteria
     * @return ElementQueryInterface
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
     * @return ElementQueryInterface
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
     * Display rating template
     *
     * @param $element
     * @param bool $markup
     * @return array|Markup
     * @throws Exception
     */
    public function displayRating($element, $markup = true)
    {
        $view = Craft::$app->getView();
        $path = $view->getTemplatesPath() . DIRECTORY_SEPARATOR . 'qarr';
        $customFile = $this->_resolveTemplate($path, 'rating');

        $variables = [
            'averageRating' => $this->getAverageRating($element->id),
            'total' => $this->getCount('reviews', 'approved', $element->id)
        ];

        if ($customFile) {
            $html = Craft::$app->view->renderTemplate($customFile, $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = Craft::$app->view->renderTemplate('qarr/frontend/custom/rating', $variables);
            Craft::$app->view->setTemplateMode($oldPath);
        }

        if ($markup) {
            return Template::raw($html);
        } else {
            return $variables;
        }

    }

    /**
     * Display reviews
     *
     * @param $element
     * @param bool $markup
     * @throws Exception
     */
    public function displayReviews($element, $markup = true)
    {
        $view = Craft::$app->getView();
        $path = $view->getTemplatesPath() . DIRECTORY_SEPARATOR . 'qarr';
        $customFile = $this->_resolveTemplate($path, 'reviews');

        $variables = [];

        if ($element === '*') {
            $query = QARR::$app->elements->queryElements('reviews');
        } else {
            $query = QARR::$plugin->elements->queryElements('reviews', $element->id, null, null, 'approved');
            $variables['averageRating'] = $this->getAverageRating($element->id);
        }

        $variables = [
            'reviews' => $query,
            'total' => $query->count()
        ];

        if ($markup) {
            if ($customFile) {
                $html = Craft::$app->view->renderTemplate($customFile, $variables);
            } else {
                $oldPath = Craft::$app->view->getTemplateMode();
                Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
                $html = Craft::$app->view->renderTemplate('qarr/frontend/custom/reviews', $variables);
                Craft::$app->view->setTemplateMode($oldPath);
            }

            return Template::raw($html);
        } else {
            $variables['reviews'] = $query->all();

            return $variables;
        }

    }

    /**
     * Display questions
     *
     * @param $element
     * @throws Exception
     */
    public function displayQuestions($element, $markup = true)
    {
        $view = Craft::$app->getView();
        $path = $view->getTemplatesPath() . DIRECTORY_SEPARATOR . 'qarr';
        $customFile = $this->_resolveTemplate($path, 'questions');

        $query = QARR::$plugin->elements->queryElements('questions', $element->id, null, null, 'approved');

        $variables = [
            'questions' => $query,
            'total' => $query->count()
        ];

        if ($customFile) {
            $html = Craft::$app->view->renderTemplate($customFile, $variables);
        } else {
            $oldPath = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = Craft::$app->view->renderTemplate('qarr/frontend/custom/questions', $variables);
            Craft::$app->view->setTemplateMode($oldPath);
        }

        if ($markup) {
            return Template::raw($html);
        } else {
            $variables['questions'] = $query->all();

            return $variables;
        }

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
                return 'qarr' . DIRECTORY_SEPARATOR . $name . '.' . $extension;
            }
        }
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
    public function getCount(string $type, string $status, int $elementId = null, $elementType = null, $elementTypeId = null)
    {
        return QARR::$plugin->elements->getCount($type, $status, $elementId, $elementType, $elementTypeId);
    }

    /**
     * Entries by rating
     *
     * @param $elementId
     * @return null
     */
    public function getEntriesByRating($elementId)
    {
        if (!$elementId) {
            return null;
        }

        return QARR::$plugin->elements->getEntriesByRating('approved', $elementId);
    }

    /**
     * Average rating
     *
     * @param $elementId
     * @return null
     */
    public function getAverageRating($elementId)
    {
        if (!$elementId) {
            return null;
        }

        return QARR::$plugin->elements->getAverageRating($elementId);
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
            'Assets',
        ];
    }
}
