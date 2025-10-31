<?php

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\TextareaField;

class Page extends SiteTree
{
    private static $db = [
        'Summary' => 'Text'
    ];

    private static $has_one = [];

    private static $summary_fields = [
        'Title',
        'Summary'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Main',
            TextareaField::create('Summary', 'Page Summary')
                ->setDescription('A brief summary of this page for search results'),
            'Content'
        );

        return $fields;
    }
}
