<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\plugin;

use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

trait Routes
{
    // Private Methods
    // =========================================================================

    private function _registerCpRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['qarr'] = ['template' => 'qarr/index'];
                $event->rules['qarr'] = 'qarr/dashboard/index';
                $event->rules['qarr/displays'] = 'qarr/displays/index';
                $event->rules['qarr/displays/new'] = 'qarr/displays/edit';
                $event->rules['qarr/displays/<displayId:\d+>'] = 'qarr/displays/edit';
                $event->rules['qarr/reviews/<reviewId:\d+>'] = 'qarr/reviews/edit';
                $event->rules['qarr/questions/<questionId:\d+>'] = 'qarr/questions/edit';
                $event->rules['qarr/rules'] = 'qarr/rules/index';
                $event->rules['qarr/rules/new'] = 'qarr/rules/edit';
                $event->rules['qarr/rules/<ruleId:\d+>'] = 'qarr/rules/edit';
                $event->rules['qarr/settings'] = ['template' => 'qarr/settings/index'];
                $event->rules['qarr/settings/helpdesk'] = 'qarr/settings/help-desk/index';
                $event->rules['qarr/settings/utilities'] = 'qarr/settings/utilities/index';
            }
        );
    }

    private function _registerSiteRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['qarr/correspondence'] = 'qarr/correspondence/gate-keeper';
            }
        );
    }
}