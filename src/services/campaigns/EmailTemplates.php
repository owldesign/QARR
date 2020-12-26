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
use craft\helpers\Json;
use owldesign\qarr\models\EmailTemplate;

use Craft;
use craft\base\Component;
use owldesign\qarr\QARR;
use owldesign\qarr\records\EmailTemplate as EmailTemplateRecord;
use yii\base\Exception;

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
            $template = $this->_createEmailTemplateFromRecord($templateRecord);
            if ($template->settings) {
                $template->settings = Json::decode($template->settings);
            }
            if ($template->options) {
                $template->options = Json::decode($template->options);
            }
            $this->_templates[] = $template;

        }

        return $this->_templates;
    }

    /**
     * Returns email template by its ID.
     *
     * @param int $templateId
     * @return EmailTemplate|null
     */
    public function getEmailTemplateById(int $templateId)
    {
        return ArrayHelper::firstWhere($this->getAllEmailTemplates(), 'id', $templateId);
    }

    /**
     * Returns email template by its UID.
     *
     * @param int $uid
     * @return EmailTemplate|null
     */
    public function getEmailTemplateByUid(int $uid)
    {
        return ArrayHelper::firstWhere($this->getAllEmailTemplates(), 'uid', $uid, true);
    }

    /**
     * Returns email template by its handle.
     *
     * @param string $templateHandle
     * @return EmailTemplate|null
     */
    public function getEmailTemplateByHandle(string $templateHandle)
    {
        return ArrayHelper::firstWhere($this->getAllEmailTemplates(), 'handle', $templateHandle, true);
    }

    /**
     * Save email template
     *
     * @param EmailTemplate $template
     * @return bool
     * @throws Exception
     */
    public function save(EmailTemplate $template)
    {
        $isNewEmailTemplate = !$template->id;

        if ($template->id) {
            $record = EmailTemplateRecord::findOne($template->id);

            if (!$record) {
                throw new Exception(QARR::t('Email template with ID not found: ' . $template->id));
            }
        } else {
            $record = new EmailTemplateRecord();
        }

        $record->name           = $template->name;
        $record->handle         = $template->handle;
        $record->enabled        = $template->enabled;
        $record->templatePath   = $template->templatePath;
        $record->bodyHtml       = $template->bodyHtml;
        $record->bodyRaw        = $template->bodyRaw;
        $record->footerHtml     = $template->footerHtml;
        $record->footerRaw     = $template->footerRaw;

        if ($template->settings) {
            $record->settings = $template->settings;
        }

        if ($template->options) {
            $record->options = $template->options;
        }

        $record->save(false);

        if ($isNewEmailTemplate) {
            $template->id = $record->id;
        }

        return true;
    }

    /**
     * Get email template suggestions for custom templates
     *
     * @return array
     * @throws \yii\base\Exception
     */
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
            'templatePath',
            'bodyHtml',
            'bodyRaw',
            'footerHtml',
            'footerRaw',
            'settings',
            'options',
        ]));

        return $template;
    }
}
