<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr;

use craft\web\Controller;
use owldesign\qarr\elements\actions\SetStatus;
use owldesign\qarr\elements\Review as ReviewElement;
use owldesign\qarr\fields\QARRField as QARRFieldField;
use owldesign\qarr\services\campaigns\EmailTemplates;
use owldesign\qarr\services\Rules;
use owldesign\qarr\services\Geolocations;
use owldesign\qarr\services\Elements as QarrElements;
use owldesign\qarr\utilities\QARRUtility as QARRUtilityUtility;
//use owldesign\qarr\web\assets\QarrCp;
use owldesign\qarr\widgets\Stats;
use owldesign\qarr\widgets\Pending;
use owldesign\qarr\plugin\Routes;
use owldesign\qarr\plugin\Services;
use owldesign\qarr\web\twig\Variables;
use owldesign\qarr\web\twig\Extensions;
use owldesign\qarr\models\Settings;

use Craft;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;
use craft\helpers\ArrayHelper;
use craft\base\Plugin;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Utilities;
use craft\services\Dashboard;
use craft\services\UserPermissions;
use craft\events\TemplateEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUserPermissionsEvent;

use yii\base\Event;
use yii\web\Response;

class QARR extends Plugin
{
    /// Static Properties
    // =========================================================================

    public static $plugin;

    // Public Properties
    // =========================================================================

    public $schemaVersion = '1.0.8';
    public $hasCpSettings = false;
    public $hasCpSection = true;
    public $changelogUrl = 'https://raw.githubusercontent.com/owldesign/QARR/master/CHANGELOG.md';
    public $downloadUrl = 'https://qarr.tools';
    public $pluginUrl = 'https://qarr.tools';
    public $docsUrl = 'https://docs.qarr.tools';

    // Trails
    // =========================================================================

    use Services;
    use Routes;

    // Public Methods
    // =========================================================================

    /**
     *  @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::setAlias('@qarr', dirname(__DIR__));

        $this->_setPluginComponents();
        $this->_registerSiteRoutes();
        $this->_addTwigExtensions();
        $this->_registerElementTypes();
        $this->_registerVariables();
        $this->_registerPermissions();
        $this->_registerCpAssets();
        $this->_registerCpRoutes();

        // TODO: Widgets, fieldtypes, utilities
        // Coming soon
         $this->_registerWidgets();
        // $this->_registerFieldTypes();
        // $this->_registerUtilities();

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $e) {
            /** @var CraftVariable $variable */
            $variable = $e->sender;
            $variable->set('qarrEmails', EmailTemplates::class);
            $variable->set('qarrRules', Rules::class);
            $variable->set('qarrElements', QarrElements::class);
            $variable->set('geolocations', Geolocations::class);
        });

        // Element status updates
        Event::on(SetStatus::class, SetStatus::EVENT_AFTER_SAVE, function(Event $e) {
            if ($e->response) {
                $status = $e->status;
                $type = $e->type;

                // Reset geolocation stats
                QARR::$plugin->geolocations->reset();
            }
        });

    }

    // Protected Methods
    // =========================================================================
    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function settingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('qarr/settings/index', [
            'settings' => $this->getSettings()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse()
    {
        return Craft::$app->controller->renderTemplate('qarr/settings/index');
    }


    /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        $parent = parent::getCpNavItem();

        $navigation = ArrayHelper::merge($parent, [
            'badgeCount' => QARR::getInstance()->elements->getTotalPending(),
            'subnav' => [
                'dashboard' => [
                    'label' => QARR::t('Dashboard'),
                    'url' => 'qarr'
                ],
                'reviews' => [
                    'label' => QARR::t('Reviews'),
                    'url' => 'qarr/reviews'
                ],
                'questions' => [
                    'label' => QARR::t('Questions'),
                    'url' => 'qarr/questions'
                ],
                'campaigns' => [
                    'label' => QARR::t('Campaigns'),
                    'url' => 'qarr/campaigns'
                ],
                'displays' => [
                    'label' => QARR::t('Displays'),
                    'url' => 'qarr/displays'
                ],
                'rules' => [
                    'label' => QARR::t('Rules'),
                    'url' => 'qarr/rules'
                ],
                'settings' => [
                    'label' => QARR::t('Settings'),
                    'url' => 'qarr/settings'
                ]
            ]
        ]);

        return $navigation;
    }

    /**
     * @param $message
     * @param array $params
     * @return string
     */
    public static function t($message, array $params = [])
    {
        return Craft::t('qarr', $message, $params);
    }

    /**
     * @param $message
     * @param string $type
     */
    public static function log($message, $type = 'info')
    {
        Craft::$type(self::t($message), __METHOD__);
    }
    /**
     * @param $message
     */
    public static function info($message)
    {
        Craft::info(self::t($message), __METHOD__);
    }
    /**
     * @param $message
     */
    public static function error($message)
    {
        Craft::error(self::t($message), __METHOD__);
    }

    // Private Methods
    // =========================================================================

    /**
     * Register element types
     */
    private function _registerElementTypes()
    {
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ReviewElement::class;
            }
        );
    }

    /**
     * Register variables
     */
    private function _registerVariables()
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('qarr', Variables::class);
            }
        );
    }

    /**
     * Register twig extensions
     */
    private function _addTwigExtensions()
    {
        Craft::$app->view->registerTwigExtension(new Extensions());
    }

    /**
     * Register plugin permissions
     */
    private function _registerPermissions()
    {
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $permissions = [];
                $permissions['qarr:accessReviews'] = [
                    'label' => QARR::t('Access Reviews'),
                    'nested' => [
                        'qarr:editReviews' => [
                            'label' => QARR::t('Edit reviews')
                        ],
                        'qarr:deleteReviews' => [
                            'label' => QARR::t('Delete reviews')
                        ]
                    ]
                ];
                $permissions['qarr:accessQuestions'] = [
                    'label' => QARR::t('Access Questions'),
                    'nested' => [
                        'qarr:editQuestions' => [
                            'label' => QARR::t('Edit questions')
                        ],
                        'qarr:deleteQuestions' => [
                            'label' => QARR::t('Delete questions')
                        ]
                    ]
                ];
                $permissions['qarr:accessDisplays'] = [
                    'label' => QARR::t('Access Displays'),
                    'nested' => [
                        'qarr:editDisplays' => [
                            'label' => QARR::t('Edit displays')
                        ],
                        'qarr:deleteDisplays' => [
                            'label' => QARR::t('Delete displays')
                        ]
                    ]
                ];
                $permissions['qarr:accessRules'] = [
                    'label' => QARR::t('Access Rules'),
                    'nested' => [
                        'qarr:editRules' => [
                            'label' => QARR::t('Edit rules')
                        ],
                        'qarr:deleteRules' => [
                            'label' => QARR::t('Delete rules')
                        ]
                    ]
                ];
                $permissions['qarr:accessCampaigns'] = [
                    'label' => QARR::t('Access Campaigns'),
                    'nested' => [
                        'qarr:editCampaigns' => [
                            'label' => QARR::t('Edit campaigns')
                        ],
                        'qarr:deleteCampaigns' => [
                            'label' => QARR::t('Delete campaigns')
                        ]
                    ]
                ];
                $event->permissions[QARR::t('QARR')] = $permissions;
            }
        );
    }

    /**
     * Register Widgets
     */
    private function _registerWidgets()
    {
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = Stats::class;
                $event->types[] = Pending::class;
            }
        );
    }

    /**
     * Register CP Assets
     */
    private function _registerCpAssets()
    {
        // TODO: take a look at this, does it need to exist?
//        if (Craft::$app->getRequest()->getIsCpRequest()) {
//            Event::on(
//                View::class,
//                View::EVENT_BEFORE_RENDER_TEMPLATE,
//                function (TemplateEvent $event) {
//                    Craft::$app->getView()->registerAssetBundle(QarrCp::class);
//                }
//            );
//        }
    }

    private function _registerFieldTypes()
    {
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = QARRFieldField::class;
            }
        );
    }

    private function _registerUtilities()
    {
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = QARRUtilityUtility::class;
            }
        );
    }
}
