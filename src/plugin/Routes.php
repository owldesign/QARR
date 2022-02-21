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
                $event->rules['qarr'] = 'qarr/dashboard/index';

                $event->rules['qarr/displays'] = 'qarr/displays/index';
                $event->rules['qarr/displays/new'] = 'qarr/displays/edit';
                $event->rules['qarr/displays/<displayId:\d+>'] = 'qarr/displays/edit';

                $event->rules['qarr/reviews'] = 'qarr/reviews/index';
                $event->rules['qarr/reviews/<reviewId:\d+>'] = 'qarr/reviews/edit';

                $event->rules['qarr/questions'] = 'qarr/questions/index';
                $event->rules['qarr/questions/<questionId:\d+>'] = 'qarr/questions/edit';

                $event->rules['qarr/rules'] = 'qarr/rules/index';
                $event->rules['qarr/rules/new'] = 'qarr/rules/edit';
                $event->rules['qarr/rules/<ruleId:\d+>'] = 'qarr/rules/edit';

                $event->rules['qarr/campaigns'] = 'qarr/campaigns/campaigns/index';
                $event->rules['qarr/campaigns/direct'] = 'qarr/campaigns/direct-links/index';
                $event->rules['qarr/campaigns/direct/new'] = 'qarr/campaigns/direct-links/edit';
                $event->rules['qarr/campaigns/direct/<directId:\d+>'] = 'qarr/campaigns/direct-links/edit';
                $event->rules['qarr/campaigns/email-templates'] = 'qarr/campaigns/email-templates/index';
                $event->rules['qarr/campaigns/email-templates/new'] = 'qarr/campaigns/email-templates/edit';
                $event->rules['qarr/campaigns/email-templates/<templateId:\d+>'] = 'qarr/campaigns/email-templates/edit';

                $event->rules['qarr/settings'] = 'qarr/settings/settings/index';
                $event->rules['qarr/settings/general'] = 'qarr/settings/general/index';
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
                $event->rules['qarr/direct/<slug:[^\\/]+>'] = 'qarr/campaigns/direct-links/form';
                $event->rules['c/r'] = 'qarr/campaigns/direct/review';
                $event->rules['c/q'] = 'qarr/campaigns/direct/question';
                $event->rules['qarr/correspondence'] = 'qarr/correspondence/gate-keeper';
            }
        );
    }
}

