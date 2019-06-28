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

use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\Review;
use owldesign\qarr\elements\Question;
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
    public $elementType;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return QARR::t('Overall Stats');
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias("@owldesign/qarr/web/assets/images/icon.svg");
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules   = parent::rules();
        $rules[] = [['type'], 'required'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $id = Craft::$app->getView()->formatInputId('type');
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        return Craft::$app->getView()->renderTemplate('qarr/widgets/_stats/settings', [
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

        $this->elementType = $this->_getElementType($this->type);

        $variables['stats'] = $this->_getStatusStats();
        
        if ($this->type == 'reviews') {
            $js = 'new QarrDonutChart("#' . $this->type . '-donut-'. $this->id .'", "owldesign\\\qarr\\\elements\\\Review")';
        } else {
            $js = 'new QarrDonutChart("#' . $this->type . '-donut-'. $this->id .'", "owldesign\\\qarr\\\elements\\\Question")';
        }

        $view->registerJs($js);

        $variables['type']  = $this->type;
        $variables['id']    = $this->id;

        return Craft::$app->getView()->renderTemplate('qarr/widgets/_stats/body', $variables);
    }

    /**
     * Get element instance
     * 
     * @param $type
     * @return \craft\elements\db\ElementQueryInterface
     */
    private function _getElementType($type)
    {
        if ($type == 'reviews') {
            return Review::find();
        } else {
            return Question::find();
        }
    }

    /**
     * Get status stats
     */
    private function _getStatusStats()
    {
        $data = [];
        $entries = $this->elementType->all();

        $this->_setCount($data, $entries);
        $this->_setHandle($data);
        $this->_setStatColors($data);

        if ($data['total'] > 0) {
            $this->_setPercentages($data);
        }

        return $data;
    }

    /**
     * Set count
     *
     * @param $variables
     * @param $entries
     */
    private function _setCount(&$variables, $entries)
    {
        $variables['total'] = $this->elementType->count();

        $variables['entries']['0']['count'] = count(ArrayHelper::filterByValue($entries, 'status', 'pending', true));
        $variables['entries']['1']['count'] = count(ArrayHelper::filterByValue($entries, 'status', 'approved', true));
        $variables['entries']['2']['count'] = count(ArrayHelper::filterByValue($entries, 'status', 'rejected', true));

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
        $variables['entries']['0']['percent'] = round(($variables['entries']['0']['count'] / $variables['total']) * 100) . '%';
        $variables['entries']['1']['percent'] = round(($variables['entries']['1']['count'] / $variables['total']) * 100) . '%';
        $variables['entries']['2']['percent'] = round(($variables['entries']['2']['count'] / $variables['total']) * 100) . '%';
    }
}
