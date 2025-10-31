<?php

namespace App\Services;

use GuzzleHttp\Client;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injectable;

/**
 * Service for interacting with LLM APIs (Anthropic or OpenAI)
 */
class LLMService
{
    use Injectable;

    /**
     * @var string
     */
    protected $provider;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var Client
     */
    protected $httpClient;

    public function __construct()
    {
        $this->provider = Environment::getEnv('LLM_PROVIDER') ?: 'anthropic';
        $this->httpClient = new Client([
            'timeout' => 30,
            'http_errors' => false
        ]);

        if ($this->provider === 'anthropic') {
            $this->apiKey = Environment::getEnv('ANTHROPIC_API_KEY');
        } else {
            $this->apiKey = Environment::getEnv('OPENAI_API_KEY');
        }
    }

    /**
     * Send a chat message with context and get a response
     */
    public function chat(string $userMessage, string $context): string
    {
        if (empty($this->apiKey)) {
            return "Error: API key not configured for {$this->provider}. Please set the appropriate environment variable.";
        }

        if ($this->provider === 'anthropic') {
            return $this->chatAnthropic($userMessage, $context);
        } else {
            return $this->chatOpenAI($userMessage, $context);
        }
    }

    /**
     * Chat with Anthropic Claude
     */
    protected function chatAnthropic(string $userMessage, string $context): string
    {
        $systemPrompt = $this->buildSystemPrompt();

        $userPrompt = "Context from knowledge base:\n\n{$context}\n\n";
        $userPrompt .= "User question: {$userMessage}\n\n";
        $userPrompt .= "Please provide a helpful answer based ONLY on the context provided above. ";
        $userPrompt .= "If the context doesn't contain information to answer the question, ";
        $userPrompt .= "politely inform the user that you don't have that information in your knowledge base.";

        try {
            $response = $this->httpClient->post('https://api.anthropic.com/v1/messages', [
                'headers' => [
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'model' => 'claude-3-5-sonnet-20241022',
                    'max_tokens' => 1024,
                    'system' => $systemPrompt,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $userPrompt
                        ]
                    ]
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 200 && isset($body['content'][0]['text'])) {
                return $body['content'][0]['text'];
            } else {
                $error = $body['error']['message'] ?? 'Unknown error';
                return "Error communicating with Anthropic API: {$error}";
            }
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Chat with OpenAI
     */
    protected function chatOpenAI(string $userMessage, string $context): string
    {
        $systemPrompt = $this->buildSystemPrompt();

        $userPrompt = "Context from knowledge base:\n\n{$context}\n\n";
        $userPrompt .= "User question: {$userMessage}";

        try {
            $response = $this->httpClient->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt
                        ],
                        [
                            'role' => 'user',
                            'content' => $userPrompt
                        ]
                    ],
                    'max_tokens' => 1024,
                    'temperature' => 0.7
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 200 && isset($body['choices'][0]['message']['content'])) {
                return $body['choices'][0]['message']['content'];
            } else {
                $error = $body['error']['message'] ?? 'Unknown error';
                return "Error communicating with OpenAI API: {$error}";
            }
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Build the system prompt
     */
    protected function buildSystemPrompt(): string
    {
        return "You are a helpful assistant that answers questions based on a knowledge base. " .
               "IMPORTANT: You must ONLY provide information that is present in the context provided to you. " .
               "If the context does not contain information to answer the user's question, you must clearly state: " .
               "\"I apologize, but I don't have information about that in my knowledge base. " .
               "The available information is limited to what has been indexed in our system.\" " .
               "Never make up information or provide answers based on your general knowledge. " .
               "Always be honest about the limitations of the available information. " .
               "When you do have relevant information, provide clear, concise, and helpful answers. " .
               "You may refer users to the source URLs provided in the context for more detailed information.";
    }
}
