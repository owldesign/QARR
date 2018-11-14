<?php

namespace owldesign\qarr\models;

use craft\base\Model;

class Settings extends Model
{
    public $foo = 'defaultFooValue';
    public $bar = 'defaultBarValue';

    public function rules()
    {
        return [
            [['foo', 'bar'], 'required'],
            // ...
        ];
    }
}
