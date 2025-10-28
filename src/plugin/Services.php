<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\plugin;

use owldesign\qarr\services\Functions;
use owldesign\qarr\services\Cookies;
use owldesign\qarr\services\Reviews;
use owldesign\qarr\services\Questions;
use owldesign\qarr\services\Displays;
use owldesign\qarr\services\Replies;
use owldesign\qarr\services\Answers;
use owldesign\qarr\services\Notes;
use owldesign\qarr\services\Correspondence;
use owldesign\qarr\services\Elements;
use owldesign\qarr\services\Rules;
use owldesign\qarr\services\campaigns\DirectLink;
use owldesign\qarr\services\campaigns\EmailTemplates;
use owldesign\qarr\services\Geolocations;
use owldesign\qarr\services\Encrypt;
use yii\base\InvalidConfigException;

trait Services
{
    // Public Methods
    // =========================================================================

    /**
     * Get functions
     *
     * @return Functions
     * @throws InvalidConfigException
     */
    public function getFunctions(): Functions
    {
        return $this->get('functions');
    }

    /**
     * Get reviews
     *
     * @return Reviews
     * @throws InvalidConfigException
     */
    public function getReviews(): Reviews
    {
        return $this->get('reviews');
    }

    /**
     * Get questions
     *
     * @return Questions
     * @throws InvalidConfigException
     */
    public function getQuestions(): Questions
    {
        return $this->get('questions');
    }

    /**
     * Get displays
     *
     * @return Displays
     * @throws InvalidConfigException
     */
    public function getDisplays(): Displays
    {
        return $this->get('displays');
    }

    /**
     * Get replies
     *
     * @return Replies
     * @throws InvalidConfigException
     */
    public function getReplies(): Replies
    {
        return $this->get('replies');
    }

    /**
     * Get answers
     *
     * @return Answers
     * @throws InvalidConfigException
     */
    public function getAnswers(): Answers
    {
        return $this->get('answers');
    }

    /**
     * Get notes
     *
     * @return Notes
     * @throws InvalidConfigException
     */
    public function getNotes(): Notes
    {
        return $this->get('notes');
    }

    /**
     * Get notes
     *
     * @return Correspondence
     * @throws InvalidConfigException
     */
    public function getCorrespondence(): Correspondence
    {
        return $this->get('correspondence');
    }

    /**
     * Get cookies
     *
     * @return Cookies
     * @throws InvalidConfigException
     */
    public function getCookies(): Cookies
    {
        return $this->get('cookies');
    }

    /**
     * Get elements
     *
     * @return Elements
     * @throws InvalidConfigException
     */
    public function getElements(): Elements
    {
        return $this->get('elements');
    }

    /**
     * Get rules
     *
     * @return Rules
     * @throws InvalidConfigException
     */
    public function getRules(): Rules
    {
        return $this->get('rules');
    }

    /**
     * Get geolocations
     *
     * @return Geolocations
     * @throws InvalidConfigException
     */
    public function getGeolocations(): Geolocations
    {
        return $this->get('geolocations');
    }

    /**
     * Get direct link
     *
     * @return DirectLink
     * @throws InvalidConfigException
     */
    public function getLinks(): DirectLink
    {
        return $this->get('links');
    }

    /**
     * Get email templates
     *
     * @return EmailTemplates
     * @throws InvalidConfigException
     */
    public function getEmailTemplates(): EmailTemplates
    {
        return $this->get('email-templates');
    }

    /**
     * Get Encrypt
     *
     * @return Encrypt
     * @throws InvalidConfigException
     */
    public function getEncrypt(): Encrypt
    {
        return $this->get('encrypt');
    }

    // Private Methods
    // =========================================================================

    /**
     * Set components
     */
    private function _setPluginComponents(): void
    {
        $this->setComponents([
            'functions' => Functions::class,
            'reviews' => Reviews::class,
            'replies' => Replies::class,
            'questions' => Questions::class,
            'answers' => Answers::class,
            'displays' => Displays::class,
            'notes' => Notes::class,
            'correspondence' => Correspondence::class,
            'cookies' => Cookies::class,
            'elements' => Elements::class,
            'rules' => Rules::class,
            'geolocations' => Geolocations::class,
            'links' => DirectLink::class,
            'encrypt' => Encrypt::class,
            'email-templates' => EmailTemplates::class,
        ]);
    }
}