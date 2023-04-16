<?php

namespace owldesign\qarr\elements\db;


abstract class Table
{
    const REVIEWS                   = '{{%qarr_reviews}}';
    const REVIEWSREPLIES            = '{{%qarr_reviews_replies}}';

    const QUESTIONS                 = '{{%qarr_questions}}';
    const QUESTIONSANSWERS          = '{{%qarr_questions_answers}}';
    const QUESTIONSANSWERSCOMMENTS  = '{{%qarr_questions_answers_comments}}';

    const DISPLAYS                  = '{{%qarr_displays}}';

    const CORRESPONDENCE            = '{{%qarr_correspondence}}';
    const CORRESPONDENCERESPONSES   = '{{%qarr_correspondence_responses}}';

    const NOTES                     = '{{%qarr_notes}}';

    const RULES                     = '{{%qarr_rules}}';
    const RULESFLAGGED              = '{{%qarr_rules_elements}}';

    const DIRECTLINKS               = '{{%qarr_direct_links}}';
    const EMAIL_TEMPLATES           = '{{%qarr_email_templates}}';

}