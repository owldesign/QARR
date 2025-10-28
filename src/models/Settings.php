<?php

namespace owldesign\qarr\models;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    public array $elementAssetHandleName = [];
    public bool $enableAutoApprovalForReviews = false;
    public bool $enableAutoApprovalForQuestions = false;
}
