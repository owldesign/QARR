<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    public $elementAssetHandleName;
    public $craftify;

    public function rules()
    {
        return [
        ];
    }

    public function setAttributes($values, $safeOnly = true)
    {
        if (isset($values['craftify']) && $values['craftify']) {
            $this->craftify = true;
        } else {
            $this->craftify = false;
        }

        parent::setAttributes($values);
    }

}
