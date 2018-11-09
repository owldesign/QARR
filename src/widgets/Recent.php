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
class Recent extends Widget
{

    // Public Properties
    // =========================================================================

    public $type;
    public $limit = 5;
    public $status = 'pending';
    public $colspan = 2;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return QARR::t('Recent Submissions');
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias("@owldesign/qarr/web/assets/images/icon.svg");
    }

    /**
     * @inheritdoc
     */
    public static function maxColspan()
    {
        return 2;
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules   = parent::rules();
        $rules[] = [['type', 'limit'], 'required'];
        $rules[] = [['limit'], 'integer', 'min' => 1];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $id = Craft::$app->getView()->formatInputId('type');
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        return Craft::$app->getView()->renderTemplate('qarr/widgets/_recent/settings', [
                'id'        => $namespacedId,
                'widget'    => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml()
    {
        $view = Craft::$app->getView();

        $view->registerAssetBundle(Widgets::class);

        if ($this->type == 'reviews') {
            $variables['title'] = QARR::t('Reviews');
            $js = 'new QARR.Widgets.PendingItemsWidget("#qarr-widget-' . $this->type . '-'. $this->id .'")';
        } else {
            $variables['title'] = QARR::t('Questions');
            $js = 'new QARR.Widgets.PendingItemsWidget("#qarr-widget-' . $this->type . '-'. $this->id .'")';
        }

        $view->registerJs($js);

        $variables['type'] = $this->type;
        $variables['limit'] = $this->limit;
        $variables['status'] = $this->status;
        $variables['id'] = $this->id;

        return Craft::$app->getView()->renderTemplate('qarr/widgets/_recent/body', $variables);
    }
}
