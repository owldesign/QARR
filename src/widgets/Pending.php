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

use craft\helpers\Html;
use craft\helpers\Json;
use owldesign\qarr\elements\Question;
use owldesign\qarr\elements\Review;
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
class Pending extends Widget
{

    // Public Properties
    // =========================================================================

    public $type;
    public $elementType;
    public int $limit = 5;
    public string $status = 'pending';
    public ?int $colspan = 2;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return QARR::t('Pending Entries');
    }

    /**
     * @inheritdoc
     */
    public static function icon(): ?string
    {
        return Craft::getAlias("@qarr/src/web/assets/images/icon.svg");
    }

    /**
     * @inheritdoc
     */
    public static function maxColspan(): ?int
    {
        return 2;
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules   = parent::rules();
        $rules[] = [['type', 'limit'], 'required'];
        $rules[] = [['limit'], 'integer', 'min' => 1];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        $id = Html::id('type');
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        return Craft::$app->getView()->renderTemplate('qarr/widgets/_pending/settings', [
                'id'        => $namespacedId,
                'widget'    => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml(): ?string
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(Widgets::class);

        $this->elementType = $this->_getElementType($this->type);

        if ($this->type == 'reviews') {
            $js = 'new QARRWidgets.PendingItemsWidget("#qarr-widget-' . $this->type . '-'. $this->id .'")';
        } else {
            $js = 'new QARRWidgets.PendingItemsWidget("#qarr-widget-' . $this->type . '-'. $this->id .'")';
        }

        $view->registerJs($js);

        $variables['type']      = $this->type;
        $variables['limit']     = $this->limit;
        $variables['status']    = $this->status;
        $variables['id']        = $this->id;

        return Craft::$app->getView()->renderTemplate('qarr/widgets/_pending/body', $variables);
    }

    /**
     * Get element instance
     *
     * @param $type
     * @return \craft\elements\db\ElementQueryInterface
     */
    private function _getElementType($type): \craft\elements\db\ElementQueryInterface
    {
        if ($type == 'reviews') {
            return Review::find();
        } else {
            return Question::find();
        }
    }
}
