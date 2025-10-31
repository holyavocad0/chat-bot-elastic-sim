<?php

namespace App\Extensions;

use SilverStripe\ORM\DataExtension;

/**
 * Extension to make pages searchable via Elasticsearch
 */
class SearchableExtension extends DataExtension
{
    /**
     * Get the data to be indexed in Elasticsearch
     */
    public function getElasticData()
    {
        return [
            'id' => $this->owner->ID,
            'title' => $this->owner->Title,
            'content' => strip_tags($this->owner->Content),
            'summary' => $this->owner->Summary,
            'menu_title' => $this->owner->MenuTitle,
            'meta_description' => $this->owner->MetaDescription,
            'url' => $this->owner->AbsoluteLink(),
            'last_edited' => $this->owner->LastEdited,
            'created' => $this->owner->Created,
        ];
    }

    /**
     * Triggered after the page is published
     */
    public function onAfterPublish()
    {
        $this->indexInElasticsearch();
    }

    /**
     * Triggered after the page is unpublished
     */
    public function onAfterUnpublish()
    {
        $this->removeFromElasticsearch();
    }

    /**
     * Index this page in Elasticsearch
     */
    protected function indexInElasticsearch()
    {
        $service = \App\Services\ElasticsearchService::singleton();
        $data = $this->getElasticData();
        $service->indexDocument($this->owner->ID, $data);
    }

    /**
     * Remove this page from Elasticsearch
     */
    protected function removeFromElasticsearch()
    {
        $service = \App\Services\ElasticsearchService::singleton();
        $service->deleteDocument($this->owner->ID);
    }
}
