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

use Craft;
use craft\db\Query;
use craft\controllers\ElementIndexesController;
use craft\helpers\ArrayHelper;
use craft\helpers\ChartHelper;
use craft\helpers\DateTimeHelper;

use owldesign\qarr\QARR;

class ChartsController extends ElementIndexesController
{
    // Public Methods
    // =========================================================================


    /**
     * Get status stats
     *
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetStatusStats()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $variables = [];
        $entries = $this->getElementQuery()->all();

        $this->_setCount($variables, $entries);
        $this->_setHandle($variables);
        $this->_setStatColors($variables);

        if ($variables['total'] > 0) {
            $this->_setPercentages($variables);
        }

        return $this->asJson([
            'success' => true,
            'data' => $variables
        ]);
    }

    /**
     * Reviews stats
     *
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetReviewsStats()
    {
        $this->requireAcceptsJson();
        $reviews = QARR::$plugin->reviews->getAllReviews();

        $pending = count(ArrayHelper::where($reviews, 'status', 'pending', true));
        $approved = count(ArrayHelper::where($reviews, 'status', 'approved', true));
        $rejected = count(ArrayHelper::where($reviews, 'status', 'rejected', true));

        $total = count($reviews);

        if ($total > 0) {
            $pendingPercent = ($pending / $total);
            $approvedPercent = ($approved / $total);
            $rejectedPercent = ($rejected / $total);
            $data = [
                ['title' => 'Approved', 'handle' => 'approved', 'percent' => $approvedPercent, 'count' => $approved, 'color' => '#2fec94'],
                ['title' => 'Rejected', 'handle' => 'rejected', 'percent' => $rejectedPercent, 'count' => $rejected, 'color' => '#f07575'],
                ['title' => 'Pending', 'handle' => 'pending', 'percent' => $pendingPercent, 'count' => $pending, 'color' => '#4da1ff'],
            ];
        } else {
            $data = [
                ['title' => 'Empty', 'handle' => 'empty', 'percent' => 1, 'count' => 0, 'color' => '#E9EFF4']
            ];
        }

        return $this->asJson([
            'success' => true,
            'data' => $data,
            'total' => $total
        ]);
    }

    /**
     * Get all reviews for specified date
     *
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetEntriesCount()
    {
        $startDateParam = $this->request->getRequiredBodyParam('startDate');
        $endDateParam = $this->request->getRequiredBodyParam('endDate');
        $elementType = $this->request->getRequiredBodyParam('elementType');

        $startDate = DateTimeHelper::toDateTime($startDateParam);
        $endDate = DateTimeHelper::toDateTime($endDateParam);

        // Start at midnight on the start date, end at midnight after the end date
        $timeZone = new \DateTimeZone(Craft::$app->getTimeZone());
        $startDate = new \DateTime($startDate->format('Y-m-d'), $timeZone);
        $endDate = new \DateTime($endDate->modify('+1 day')->format('Y-m-d'), $timeZone);

        $intervalUnit = 'day';

        if ($elementType == 'owldesign\\qarr\\elements\\Review') {
            $table = '{{%qarr_reviews}} db';
        } else {
            $table = '{{%qarr_questions}} db';
        }

        $query = (new Query())
            ->from([$table]);

        $dataTable = ChartHelper::getRunChartDataFromQuery($query, $startDate, $endDate, 'db.dateCreated', 'count', '*', [
            'intervalUnit' => $intervalUnit,
            'valueLabel' => QARR::t('Entries'),
        ]);

        $total = 0;

        foreach ($dataTable['rows'] as $row) {
            $total += $row[1];
        }

        return $this->asJson([
            'dataTable' => $dataTable,
            'total' => $total,

            'formats' => ChartHelper::formats(),
            'orientation' => Craft::$app->locale->getOrientation(),
            'scale' => $intervalUnit
        ]);
    }

    // Private Methods
    // =========================================================================

    /**
     * Set count
     *
     * @param $variables
     * @param $entries
     */
    private function _setCount(&$variables, $entries)
    {
        $variables['total'] = $this->getElementQuery()->count();

        $variables['entries']['0']['count'] = count(ArrayHelper::where($entries, 'status', 'pending', true));
        $variables['entries']['1']['count'] = count(ArrayHelper::where($entries, 'status', 'approved', true));
        $variables['entries']['2']['count'] = count(ArrayHelper::where($entries, 'status', 'rejected', true));

        // Set empty percentages
        $variables['entries']['0']['percent'] = 0;
        $variables['entries']['1']['percent'] = 0;
        $variables['entries']['2']['percent'] = 0;
    }

    /**
     * Set color
     *
     * @param $variables
     */
    private function _setStatColors(&$variables)
    {
        $variables['entries']['0']['color'] = '#4da1ff';
        $variables['entries']['1']['color'] = '#2fec94';
        $variables['entries']['2']['color'] = '#f07575';
    }

    /**
     * Set handle
     *
     * @param $variables
     */
    private function _setHandle(&$variables)
    {
        $variables['entries']['0']['handle'] = 'pending';
        $variables['entries']['1']['handle'] = 'approved';
        $variables['entries']['2']['handle'] = 'rejected';
    }

    /**
     * Set percentages
     *
     * @param $variables
     */
    private function _setPercentages(&$variables)
    {
        $variables['entries']['0']['percent'] = ($variables['entries']['0']['count'] / $variables['total']);
        $variables['entries']['1']['percent'] = ($variables['entries']['1']['count'] / $variables['total']);
        $variables['entries']['2']['percent'] = ($variables['entries']['2']['count'] / $variables['total']);
    }
}