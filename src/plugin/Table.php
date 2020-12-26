<?php

namespace owldesign\qarr\plugin;

abstract class Table
{
    const REVIEWS = '{{%qarr_reviews}}';
    const REVIEWS_REPLIES = '{{%qarr_reviews_replies}}';

    const QUESTIONS = '{{%qarr_questions}}';
    const QUESTIONS_ANSWERS = '{{%qarr_questions_answers}}';
    const QUESTIONS_ANSWERS_COMMENTS = '{{%qarr_questions_answers_comments}}';

    const CORRESPONDENCE = '{{%qarr_correspondence}}';
    const CORRESPONDENCE_RESPONSES = '{{%qarr_correspondence_responses}}';

    const DIRECT_LINKS = '{{%qarr_direct_links}}';

    const DISPLAYS = '{{%qarr_displays}}';

    const EMAIL_TEMPLATES = '{{%qarr_email_templates}}';

    const NOTES = '{{%qarr_notes}}';

    const RULES = '{{%qarr_rules}}';
    const RULES_ELEMENTS = '{{%qarr_rules_elements}}';
}