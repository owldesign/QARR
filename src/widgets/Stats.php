<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\widgets;

use craft\helpers\Json;
use owldesign\qarr\QARR;
use owldesign\qarr\web\assets\Widgets;

use Craft;
use craft\base\Widget;

/**
 * QARR Widget
 *
 * Dashboard widgets allow you to display information in the Admin CP Dashboard.
 * Adding new types of widgets to the dashboard couldn’t be easier in Craft
 *
 * https://craftcms.com/docs/plugins/widgets
 *
 * @author    Vadim Goncharov
 * @package   QARR
 * @since     1.0.0
 */
class Stats extends Widget
{

    // Public Properties
    // =========================================================================

    public $type;

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return QARR::t('QARR Stats');
    }

    /**
     * Returns the path to the widget’s SVG icon.
     *
     * @return string|null The path to the widget’s SVG icon
     */
    public static function iconPath()
    {
        return Craft::getAlias("@owldesign/qarr/web/assets/images/icon.svg");
    }

    /**
     * Returns the widget’s maximum colspan.
     *
     * @return int|null The widget’s maximum colspan, if it has one
     */
    public static function maxColspan()
    {
        return null;
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge(
            $rules,
            [
                ['type', 'string'],
                ['type', 'default', 'value' => 'owldesign\\qarr\\elements\\Review'],
            ]
        );
        return $rules;
    }

    public function getSettingsHtml()
    {
        $id = Craft::$app->getView()->formatInputId('type');
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        return Craft::$app->getView()->renderTemplate('qarr/widgets/reviews/_settings', [
                'id' => $id,
                'namespacedId' => $namespacedId,
                'settings'     => $this->getSettings()
            ]
        );
    }

    public function getBodyHtml()
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(Widgets::class);

        $options = [
            'type' => ['owldesign\\qarr\\elements\\Review', 'owldesign\\qarr\\elements\\Question']
        ];

        $js = 'new QARR.Widgets.StatusStats(' . $this->id . ', ' . Json::encode($options) . ')';

        $view->registerJs($js);

        return Craft::$app->getView()->renderTemplate('qarr/widgets/reviews/_body');
    }
}
