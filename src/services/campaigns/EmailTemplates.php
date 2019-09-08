<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\services\campaigns;

use craft\helpers\ArrayHelper;
use owldesign\qarr\models\EmailTemplate;

use Craft;
use craft\base\Component;
use owldesign\qarr\QARR;
use owldesign\qarr\records\EmailTemplate as EmailTemplateRecord;

/**
 * Class Reviews
 * @package owldesign\qarr\services
 */
class EmailTemplates extends Component
{
    // Protected Properties
    // =========================================================================

    // Properties
    // =========================================================================
    /**
     * @var
     */
    private $_templates;

    // Public Methods
    // =========================================================================

    /**
     * Get all links
     *
     * @param null $enabled
     * @return array
     */
    public function getAllEmailTemplates(): array
    {
        if ($this->_templates !== null) {
            return $this->_templates;
        }

        $this->_templates = [];

        $templateRecords = EmailTemplateRecord::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();

        foreach ($templateRecords as $templateRecord) {
            $this->_templates[] = $this->_createEmailTemplateFromRecord($templateRecord);
        }

        return $this->_templates;
    }

    public function emailTemplateSuggestions(): array
    {
        // Get all the template files sorted by path length
        $root = Craft::$app->getPath()->getSiteTemplatesPath() . '/_qarr/emails';

        if (!is_dir($root)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        /** @var \SplFileInfo[] $files */
        $files = [];
        $pathLengths = [];

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if (!$file->isDir() && $file->getFilename()[0] !== '.') {
                $files[] = $file;
                $pathLengths[] = strlen($file->getRealPath());
            }
        }

        array_multisort($pathLengths, SORT_NUMERIC, $files);

        // Now build the suggestions array
        $suggestions = [];
        $templates = [];
        $sites = [];
        $config = Craft::$app->getConfig()->getGeneral();
        $rootLength = strlen($root);

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $sites[$site->handle] = Craft::t('site', $site->name);
        }

        foreach ($files as $file) {
            $template = substr($file->getRealPath(), $rootLength + 1);

            // Can we chop off the extension?
            $extension = $file->getExtension();
            if (in_array($extension, $config->defaultTemplateExtensions, true)) {
                $template = substr($template, 0, strlen($template) - (strlen($extension) + 1));
            }

            $hint = null;

            // Is it in a site template directory?
            foreach ($sites as $handle => $name) {
                if (strpos($template, $handle . DIRECTORY_SEPARATOR) === 0) {
                    $hint = $name;
                    $template = substr($template, strlen($handle) + 1);
                    break;
                }
            }

            // Avoid listing the same template path twice (considering localized templates)
            if (isset($templates[$template])) {
                continue;
            }

            $templates[$template] = true;
            $suggestions[] = [
                'name' => $template,
                'hint' => $hint,
            ];
        }

        ArrayHelper::multisort($suggestions, 'name');

        return [
            [
                'label' => QARR::t( 'Email Templates'),
                'data' => $suggestions,
            ]
        ];
    }


    // Private Methods
    // =========================================================================

    private function _createEmailTemplateFromRecord(EmailTemplateRecord $templateRecord = null)
    {
        if (!$templateRecord) {
            return null;
        }

        $template = new EmailTemplate($templateRecord->toArray([
            'id',
            'name',
            'handle',
            'enabled',
        ]));

        return $template;
    }
}
