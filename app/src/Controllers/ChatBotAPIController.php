<?php

namespace App\Controllers;

use App\Services\ElasticsearchService;
use App\Services\LLMService;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

/**
 * API Controller for Chat Bot functionality
 */
class ChatBotAPIController extends Controller
{
    private static $url_segment = 'api/chatbot';

    private static $allowed_actions = [
        'search',
        'chat'
    ];

    /**
     * Handle CORS preflight requests
     */
    protected function init()
    {
        parent::init();

        // Allow CORS
        $allowedOrigins = explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: 'http://localhost:3000');
        $origin = $this->getRequest()->getHeader('Origin');

        if (in_array($origin, $allowedOrigins)) {
            $this->getResponse()->addHeader('Access-Control-Allow-Origin', $origin);
        }

        $this->getResponse()->addHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $this->getResponse()->addHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $this->getResponse()->addHeader('Access-Control-Max-Age', '3600');
        $this->getResponse()->addHeader('Content-Type', 'application/json');

        // Handle OPTIONS request for CORS preflight
        if ($this->getRequest()->httpMethod() === 'OPTIONS') {
            return $this->getResponse()->setStatusCode(200);
        }
    }

    /**
     * Search endpoint - returns raw Elasticsearch results
     * GET /api/chatbot/search?q=query
     */
    public function search(HTTPRequest $request)
    {
        $query = $request->getVar('q');

        if (!$query) {
            return $this->jsonError('Query parameter "q" is required', 400);
        }

        $elasticService = ElasticsearchService::singleton();
        $results = $elasticService->search($query);

        $formattedResults = [];
        foreach ($results as $hit) {
            $source = $hit['_source'];
            $formattedResults[] = [
                'id' => $source['id'],
                'title' => $source['title'],
                'summary' => $source['summary'] ?? '',
                'content' => $source['content'] ?? '',
                'url' => $source['url'] ?? '',
                'score' => $hit['_score'],
                'highlights' => $hit['highlight'] ?? []
            ];
        }

        return $this->jsonResponse([
            'success' => true,
            'query' => $query,
            'total' => count($formattedResults),
            'results' => $formattedResults
        ]);
    }

    /**
     * Chat endpoint - returns LLM-processed response based on search results
     * POST /api/chatbot/chat
     * Body: {"message": "user question"}
     */
    public function chat(HTTPRequest $request)
    {
        // Get JSON body
        $body = json_decode($request->getBody(), true);

        if (!$body || !isset($body['message'])) {
            return $this->jsonError('Message is required in request body', 400);
        }

        $userMessage = $body['message'];

        // Search Elasticsearch
        $elasticService = ElasticsearchService::singleton();
        $searchResults = $elasticService->search($userMessage, 5);

        // Format search results for LLM context
        $context = $this->formatSearchResultsForLLM($searchResults);

        // Get LLM response
        $llmService = LLMService::singleton();
        $response = $llmService->chat($userMessage, $context);

        return $this->jsonResponse([
            'success' => true,
            'message' => $userMessage,
            'response' => $response,
            'sources' => $this->extractSources($searchResults)
        ]);
    }

    /**
     * Format search results for LLM context
     */
    protected function formatSearchResultsForLLM(array $results): string
    {
        if (empty($results)) {
            return "No relevant information found in the knowledge base.";
        }

        $context = "Here is the relevant information from the knowledge base:\n\n";

        foreach ($results as $index => $hit) {
            $source = $hit['_source'];
            $num = $index + 1;

            $context .= "Document {$num}:\n";
            $context .= "Title: {$source['title']}\n";

            if (!empty($source['summary'])) {
                $context .= "Summary: {$source['summary']}\n";
            }

            if (!empty($source['content'])) {
                // Limit content length
                $content = substr($source['content'], 0, 500);
                $context .= "Content: {$content}...\n";
            }

            $context .= "URL: {$source['url']}\n\n";
        }

        return $context;
    }

    /**
     * Extract sources from search results
     */
    protected function extractSources(array $results): array
    {
        $sources = [];

        foreach ($results as $hit) {
            $source = $hit['_source'];
            $sources[] = [
                'title' => $source['title'],
                'url' => $source['url'],
                'summary' => $source['summary'] ?? ''
            ];
        }

        return $sources;
    }

    /**
     * Return JSON response
     */
    protected function jsonResponse(array $data, int $statusCode = 200): HTTPResponse
    {
        $response = $this->getResponse();
        $response->setStatusCode($statusCode);
        $response->setBody(json_encode($data, JSON_PRETTY_PRINT));
        return $response;
    }

    /**
     * Return JSON error response
     */
    protected function jsonError(string $message, int $statusCode = 400): HTTPResponse
    {
        return $this->jsonResponse([
            'success' => false,
            'error' => $message
        ], $statusCode);
    }
}
