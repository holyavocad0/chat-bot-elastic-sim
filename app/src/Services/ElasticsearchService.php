<?php

namespace App\Services;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Client;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injectable;

/**
 * Service for managing Elasticsearch operations
 */
class ElasticsearchService
{
    use Injectable;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $index;

    public function __construct()
    {
        $host = Environment::getEnv('ELASTICSEARCH_HOST') ?: 'elasticsearch:9200';
        $this->index = Environment::getEnv('ELASTICSEARCH_INDEX') ?: 'silverstripe';

        $this->client = ClientBuilder::create()
            ->setHosts(['http://' . $host])
            ->build();
    }

    /**
     * Get the Elasticsearch client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Get the index name
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * Create the index with mappings
     */
    public function createIndex()
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0
                ],
                'mappings' => [
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'title' => ['type' => 'text'],
                        'content' => ['type' => 'text'],
                        'summary' => ['type' => 'text'],
                        'menu_title' => ['type' => 'text'],
                        'meta_description' => ['type' => 'text'],
                        'url' => ['type' => 'keyword'],
                        'last_edited' => ['type' => 'date'],
                        'created' => ['type' => 'date'],
                    ]
                ]
            ]
        ];

        try {
            if ($this->client->indices()->exists(['index' => $this->index])->asBool()) {
                $this->client->indices()->delete(['index' => $this->index]);
            }
            return $this->client->indices()->create($params);
        } catch (\Exception $e) {
            user_error('Failed to create Elasticsearch index: ' . $e->getMessage(), E_USER_WARNING);
            return false;
        }
    }

    /**
     * Index a document
     */
    public function indexDocument(int $id, array $data)
    {
        $params = [
            'index' => $this->index,
            'id' => $id,
            'body' => $data
        ];

        try {
            return $this->client->index($params);
        } catch (\Exception $e) {
            user_error('Failed to index document: ' . $e->getMessage(), E_USER_WARNING);
            return false;
        }
    }

    /**
     * Delete a document
     */
    public function deleteDocument(int $id)
    {
        $params = [
            'index' => $this->index,
            'id' => $id
        ];

        try {
            return $this->client->delete($params);
        } catch (\Exception $e) {
            user_error('Failed to delete document: ' . $e->getMessage(), E_USER_WARNING);
            return false;
        }
    }

    /**
     * Search documents
     */
    public function search(string $query, int $size = 10)
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => ['title^3', 'summary^2', 'content', 'meta_description'],
                        'fuzziness' => 'AUTO'
                    ]
                ],
                'size' => $size,
                'highlight' => [
                    'fields' => [
                        'title' => new \stdClass(),
                        'content' => new \stdClass(),
                        'summary' => new \stdClass()
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
            return $response['hits']['hits'] ?? [];
        } catch (\Exception $e) {
            user_error('Search failed: ' . $e->getMessage(), E_USER_WARNING);
            return [];
        }
    }

    /**
     * Reindex all pages
     */
    public function reindexAll()
    {
        $this->createIndex();

        $pages = \SilverStripe\CMS\Model\SiteTree::get()->filter(['ClassName' => 'App\Page']);
        $count = 0;

        foreach ($pages as $page) {
            if ($page->isPublished()) {
                $data = [
                    'id' => $page->ID,
                    'title' => $page->Title,
                    'content' => strip_tags($page->Content),
                    'summary' => $page->Summary,
                    'menu_title' => $page->MenuTitle,
                    'meta_description' => $page->MetaDescription,
                    'url' => $page->AbsoluteLink(),
                    'last_edited' => $page->LastEdited,
                    'created' => $page->Created,
                ];

                $this->indexDocument($page->ID, $data);
                $count++;
            }
        }

        return $count;
    }
}
