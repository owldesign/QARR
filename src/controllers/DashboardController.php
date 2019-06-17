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
use craft\web\View;
use craft\web\Controller;
use craft\helpers\ArrayHelper;
use craft\helpers\ChartHelper;
use craft\helpers\DateTimeHelper;
use craft\db\Query;
use yii\web\Request;
use yii\web\Response;
use yii\web\NotFoundHttpException;

use owldesign\qarr\models\Answer;
use owldesign\qarr\QARR;

class DashboardController extends Controller
{
    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Index page
     * @param array $variables
     * @return Response
     */
    public function actionIndex(array $variables = []): Response
    {
        $variables = [];

        return $this->renderTemplate('qarr/index', $variables);
    }

    /**
     * New user data
     *
     * @return Response
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetNewUsersData(): Response
    {
        $userGroupId = Craft::$app->getRequest()->getRequiredBodyParam('userGroupId');
        $startDateParam = Craft::$app->getRequest()->getRequiredBodyParam('startDate');
        $endDateParam = Craft::$app->getRequest()->getRequiredBodyParam('endDate');

        $startDate = DateTimeHelper::toDateTime($startDateParam);
        $endDate = DateTimeHelper::toDateTime($endDateParam);

        if ($startDate === false || $endDate === false) {
            throw new Exception('There was a problem calculating the start and end dates');
        }

        // Start at midnight on the start date, end at midnight after the end date
        $timeZone = new DateTimeZone(Craft::$app->getTimeZone());
        $startDate = new DateTime($startDate->format('Y-m-d'), $timeZone);
        $endDate = new DateTime($endDate->modify('+1 day')->format('Y-m-d'), $timeZone);

        $intervalUnit = 'day';

        // Prep the query
        $query = (new Query())
            ->from(['{{%users}} users']);

        if ($userGroupId) {
            $query->innerJoin('{{%usergroups_users}} usergroups_users', '[[usergroups_users.userId]] = [[users.id]]');
            $query->where(['usergroups_users.groupId' => $userGroupId]);
        }

        // Get the chart data table
        $dataTable = ChartHelper::getRunChartDataFromQuery($query, $startDate, $endDate, 'users.dateCreated', 'count', '*', [
            'intervalUnit' => $intervalUnit,
            'valueLabel' => Craft::t('app', 'New Users'),
        ]);

        // Get the total number of new users
        $total = 0;

        foreach ($dataTable['rows'] as $row) {
            $total += $row[1];
        }

        // Return everything
        return $this->asJson([
            'dataTable' => $dataTable,
            'total' => $total,

            'formats' => ChartHelper::formats(),
            'orientation' => Craft::$app->getLocale()->getOrientation(),
            'scale' => $intervalUnit,
        ]);
    }

}