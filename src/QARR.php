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

use craft\base\Element;
//use craft\commerce\elements\Product;
use craft\events\ElementEvent;
use craft\events\ModelEvent;
use craft\web\Controller;
use owldesign\qarr\elements\actions\SetStatus;
use owldesign\qarr\elements\Review as ReviewElement;
use owldesign\qarr\fields\QARRField as QARRFieldField;
use owldesign\qarr\services\campaigns\EmailTemplates;
use owldesign\qarr\services\Functions;
use owldesign\qarr\services\Rules;
use owldesign\qarr\services\Geolocations;
use owldesign\qarr\services\Elements as QarrElements;
use owldesign\qarr\utilities\QARRUtility as QARRUtilityUtility;

//use owldesign\qarr\web\assets\QarrCp;
use owldesign\qarr\web\twig\BaseExtensions;
use owldesign\qarr\web\twig\BaseVariables;
use owldesign\qarr\web\twig\Render;
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

/**
 *
 * @property-read null|array $cpNavItem
 * @property-read mixed $settingsResponse
 */
class QARR extends Plugin
{
    /// Static Properties
    // =========================================================================

    public static $app;
    public static $plugin;

    // Public Properties
    // =========================================================================

    public string $schemaVersion = '1.0.8';
    public bool $hasCpSettings = false;
    public bool $hasCpSection = true;
    public ?string $changelogUrl = 'https://raw.githubusercontent.com/owldesign/QARR/master/CHANGELOG.md';
    public ?string $downloadUrl = 'https://qarr.tools';
    public string $pluginUrl = 'https://qarr.tools';
    public string $docsUrl = 'https://docs.qarr.tools';

    // Trails
    // =========================================================================

    use Services;
    use Routes;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        self::$app = $this;

        Craft::setAlias('@qarr', dirname(__DIR__));
        Craft::setAlias('@libs', '@vendor/owldesign/qarr/lib/');

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

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('functions', Functions::class);
            $variable->set('qarrEmails', EmailTemplates::class);
            $variable->set('qarrRules', Rules::class);
            $variable->set('qarrElements', QarrElements::class);
            $variable->set('geolocations', Geolocations::class);
        });

        // Element status updates
        Event::on(SetStatus::class, SetStatus::EVENT_AFTER_SAVE, function(Event $event) {
            if ($event->response) {
                $status = $event->status;
                $type = $event->type;

                // Reset geolocation stats
                QARR::$plugin->geolocations->reset();
            }
        });

        // On Element disable or delete, update all reviews + questions to dateDeleted status
        Event::on(Element::class, Element::EVENT_AFTER_DELETE, function (Event $event) {
            $sender = $event->sender;
            $elementClass = get_class($sender);

            $elementId = null;

            if ($elementClass === 'craft\\commerce\\elements\\Product') {
                $elementId = $sender->id;
            }

            if ($elementClass === 'craft\\elements\\Entry') {
                $elementId = $sender->id;
            }

            if ($elementId) {
                $result = QARR::$app->getElements()->markElementsAsDeletedByElementId($elementId, $sender->dateUpdated);
            }
        });

    }

    // Protected Methods
    // =========================================================================
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('qarr/settings/index', [
            'settings' => $this->getSettings()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse(): mixed
    {
        return Craft::$app->controller->renderTemplate('qarr/settings/index');
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        $navItems = parent::getCpNavItem();
        $loggedUser = Craft::$app->getUser();

        $navItems = ArrayHelper::merge($navItems, [
            'badgeCount' => self::getInstance()->elements->getTotalPending(),
            'subnav' => [
                'dashboard' => [
                    'label' => self::t('Dashboard'),
                    'url' => 'qarr'
                ]
            ]
        ]);

        if ($loggedUser->checkPermission('qarr:accessReviews')) {
            $navItems['subnav']['reviews'] = [
                'label' => self::t('Reviews'),
                'url' => 'qarr/reviews'
            ];
        }

        if ($loggedUser->checkPermission('qarr:accessQuestions')) {
            $navItems['subnav']['questions'] = [
                'label' => self::t('Questions'),
                'url' => 'qarr/questions'
            ];
        }

        if ($loggedUser->checkPermission('qarr:accessCampaigns')) {
            $navItems['subnav']['campaigns'] = [
                'label' => self::t('Campaigns'),
                'url' => 'qarr/campaigns'
            ];
        }

        if ($loggedUser->checkPermission('qarr:accessDisplays')) {
            $navItems['subnav']['displays'] = [
                'label' => self::t('Displays'),
                'url' => 'qarr/displays'
            ];
        }

        if ($loggedUser->checkPermission('qarr:accessRules')) {
            $navItems['subnav']['rules'] = [
                'label' => self::t('Rules'),
                'url' => 'qarr/rules'
            ];
        }

        if ($loggedUser->checkPermission('qarr:manageSettings')) {
            $navItems['subnav']['settings'] = [
                'label' => self::t('Settings'),
                'url' => 'qarr/settings'
            ];
        }

        return $navItems;
    }

    /**
     * @param $message
     * @param array $params
     * @return string
     */
    public static function t($message, array $params = []): string
    {
        return Craft::t('qarr', $message, $params);
    }

    /**
     * @param $message
     * @param string $type
     */
    public static function log($message, string $type = 'info'): void
    {
        Craft::$type(self::t($message), __METHOD__);
    }

    /**
     * @param $message
     */
    public static function info($message): void
    {
        Craft::info(self::t($message), __METHOD__);
    }

    /**
     * @param $message
     */
    public static function error($message): void
    {
        Craft::error(self::t($message), __METHOD__);
    }

    // Private Methods
    // =========================================================================

    /**
     * Register element types
     */
    private function _registerElementTypes(): void
    {
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            static function (RegisterComponentTypesEvent $event) {
                $event->types[] = ReviewElement::class;
            }
        );
    }

    /**
     * Register variables
     */
    private function _registerVariables(): void
    {
        Event::on(BaseVariables::class, BaseVariables::EVENT_INIT, static function (Event $event) {
            /** @var BaseVariables $variables */
            $variables = $event->sender;
            $variables->set('render', Render::class);
        });

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function (Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('qarr', Variables::class);
            $variable->set('functions', Functions::class);
            $variable->set('qarrEmails', EmailTemplates::class);
            $variable->set('qarrRules', Rules::class);
            $variable->set('qarrElements', QarrElements::class);
            $variable->set('geolocations', Geolocations::class);
        });
    }

    /**
     * Register twig extensions
     */
    private function _addTwigExtensions(): void
    {
        if (Craft::$app->request->getIsSiteRequest()) {
            Craft::$app->view->registerTwigExtension(new BaseExtensions);
        }

        if (Craft::$app->request->getIsCpRequest()) {
            Craft::$app->view->registerTwigExtension(new Extensions);
        }
    }

    /**
     * Register plugin permissions
     */
    private function _registerPermissions(): void
    {
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            static function (RegisterUserPermissionsEvent $event) {
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

                $permissions['qarr:manageSettings'] = [
                    'label' => QARR::t('Manage settings'),
                ];

                $event->permissions[QARR::t('QARR')] = $permissions;
            }
        );
    }

    /**
     * Register Widgets
     */
    private function _registerWidgets(): void
    {
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            static function (RegisterComponentTypesEvent $event) {
                $event->types[] = Stats::class;
                $event->types[] = Pending::class;
            }
        );
    }

    /**
     * Register CP Assets
     */
    private function _registerCpAssets(): void
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

    private function _registerFieldTypes(): void
    {
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            static function (RegisterComponentTypesEvent $event) {
                $event->types[] = QARRFieldField::class;
            }
        );
    }

    private function _registerUtilities(): void
    {
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            static function (RegisterComponentTypesEvent $event) {
                $event->types[] = QARRUtilityUtility::class;
            }
        );
    }
}
