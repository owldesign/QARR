<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    public $elementAssetHandleName = [];

    public $enableAutoApprovalForReviews = false;
    public $enableAutoApprovalForQuestions = false;
}
