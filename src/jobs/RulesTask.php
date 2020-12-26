<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\jobs;

use craft\helpers\Json;
use owldesign\qarr\QARR;

use Craft;
use craft\queue\BaseJob;

class RulesTask extends BaseJob
{
    // Public Properties
    // =========================================================================

    public $entry;
    public $elementType;

    // Public Methods
    // =========================================================================

    public function execute($queue)
    {
        try {
            $result = QARR::$plugin->rules->applyRules($this->entry, $this->elementType);
        } catch (\Exception $e) {
            QARR::log('There was an error applying rules: ' . $e->getMessage());
        }

        return true;
    }

    // Private Methods
    // =========================================================================


    // Protected Methods
    // =========================================================================

    /**
     * @return string
     */
    protected function defaultDescription(): string
    {
        return Craft::t('qarr', 'Rules');
    }
    
}
