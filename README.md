# Chat Bot with Elasticsearch and LLM Integration

A comprehensive full-stack application featuring SilverStripe 6 CMS with Elasticsearch integration and an AI-powered chat interface built with Nuxt 3.

## Features

- **SilverStripe 6 CMS**: Content management system for creating and managing pages
- **Elasticsearch Integration**: Full-text search capabilities with custom indexing
- **AI-Powered Chat**: LLM integration (Anthropic Claude or OpenAI GPT) for intelligent search responses
- **Nuxt 3 Frontend**: Modern, responsive chat interface running in Docker
- **DDEV Development Environment**: Easy local development setup
- **Knowledge-Based Responses**: LLM only responds based on indexed content in Elasticsearch

## Architecture

```
┌─────────────────┐
│  Nuxt Frontend  │ (Port 3000)
│   (Docker)      │
└────────┬────────┘
         │ HTTP API
         ↓
┌─────────────────┐
│  SilverStripe   │ (Port 8080)
│   Backend       │
│   (DDEV)        │
└────┬────────┬───┘
     │        │
     │        └──→ ┌──────────────┐
     │             │ Elasticsearch│ (Port 9200)
     │             │   (DDEV)     │
     │             └──────────────┘
     │
     └──────────→  ┌──────────────┐
                   │  LLM API     │
                   │(Anthropic or │
                   │   OpenAI)    │
                   └──────────────┘
```

## Prerequisites

- [DDEV](https://ddev.readthedocs.io/) installed (for SilverStripe backend)
- [Docker](https://www.docker.com/) and Docker Compose (for Nuxt frontend)
- [Composer](https://getcomposer.org/) (installed automatically by DDEV)
- Node.js 20+ (for local frontend development)
- An API key from either Anthropic or OpenAI

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd chat-bot-elastic-sim
```

### 2. Configure Environment Variables

Create a `.env` file in the root directory:

```bash
cp .env.example .env
```

Edit `.env` and add your API key:

```env
# Choose your LLM provider
LLM_PROVIDER="anthropic"  # or "openai"

# Add your API key
ANTHROPIC_API_KEY="your-anthropic-api-key-here"
# OR
OPENAI_API_KEY="your-openai-api-key-here"
```

### 3. Start DDEV (SilverStripe Backend + Elasticsearch)

```bash
ddev start
```

This will:
- Start the SilverStripe application on port 8080
- Start Elasticsearch on port 9200
- Set up the MariaDB database

### 4. Install SilverStripe Dependencies

```bash
ddev composer install
```

### 5. Build the Database and Run Initial Setup

```bash
ddev sake dev/build flush=1
```

### 6. Populate Dummy Pages

Run the task to create 10 sample pages and index them in Elasticsearch:

```bash
ddev sake dev/tasks/PopulateDummyPagesTask
```

This will create pages with various content about:
- Welcome/Home page
- About Us
- Services
- Team
- Contact
- Blog posts
- Case studies
- Careers
- FAQ
- Privacy Policy

### 7. Access SilverStripe Admin

Visit: `http://chat-bot-elastic-sim.ddev.site/admin`

Default credentials:
- Username: `admin`
- Password: `admin`

### 8. Start the Nuxt Frontend

```bash
cd frontend
docker-compose up -d
```

Or for local development without Docker:

```bash
cd frontend
npm install
npm run dev
```

### 9. Configure Frontend Environment

Create `frontend/.env`:

```bash
cp frontend/.env.example frontend/.env
```

Edit if needed (default should work):

```env
NUXT_PUBLIC_API_BASE=http://localhost:8080
```

## Usage

### Access the Applications

- **Chat Interface**: http://localhost:3000
- **SilverStripe Backend**: http://chat-bot-elastic-sim.ddev.site
- **SilverStripe Admin**: http://chat-bot-elastic-sim.ddev.site/admin
- **Elasticsearch**: http://localhost:9200 (via DDEV)

### Using the Chat Bot

1. Open http://localhost:3000 in your browser
2. Type a question in the input field
3. The system will:
   - Search Elasticsearch for relevant pages
   - Send the search results to the LLM as context
   - Display the LLM's response based only on the indexed content
   - Show source links for referenced pages

### Example Questions

Try asking:
- "What services do you offer?"
- "Tell me about your company"
- "How can I contact you?"
- "What are your latest blog posts?"

### Adding More Pages

1. Log in to SilverStripe Admin
2. Create new pages in the CMS
3. Publish them
4. Pages are automatically indexed in Elasticsearch upon publishing

Or run the populate task again:

```bash
ddev sake dev/tasks/PopulateDummyPagesTask
```

### Reindexing Elasticsearch

If you need to reindex all pages manually:

```bash
ddev sake dev/tasks/ReindexElasticsearchTask
```

## API Endpoints

The SilverStripe backend exposes the following API endpoints:

### Search Endpoint

**GET** `/api/chatbot/search?q=query`

Returns raw Elasticsearch results.

Response:
```json
{
  "success": true,
  "query": "services",
  "total": 5,
  "results": [
    {
      "id": 3,
      "title": "Our Services",
      "summary": "Explore the comprehensive range of services we offer.",
      "content": "...",
      "url": "http://...",
      "score": 2.5
    }
  ]
}
```

### Chat Endpoint

**POST** `/api/chatbot/chat`

Body:
```json
{
  "message": "What services do you offer?"
}
```

Response:
```json
{
  "success": true,
  "message": "What services do you offer?",
  "response": "Based on our knowledge base, we offer...",
  "sources": [
    {
      "title": "Our Services",
      "url": "http://...",
      "summary": "..."
    }
  ]
}
```

## Project Structure

```
.
├── .ddev/                          # DDEV configuration
│   ├── config.yaml                 # Main DDEV config
│   └── docker-compose.elasticsearch.yaml  # Elasticsearch service
├── app/                            # SilverStripe application
│   ├── _config/                    # Configuration files
│   │   ├── mysite.yml
│   │   ├── elasticsearch.yml
│   │   └── routes.yml
│   └── src/                        # PHP source code
│       ├── Controllers/
│       │   └── ChatBotAPIController.php
│       ├── Extensions/
│       │   └── SearchableExtension.php
│       ├── Services/
│       │   ├── ElasticsearchService.php
│       │   └── LLMService.php
│       ├── Tasks/
│       │   └── PopulateDummyPagesTask.php
│       ├── Page.php
│       └── PageController.php
├── frontend/                       # Nuxt 3 frontend
│   ├── pages/
│   │   └── index.vue              # Chat interface
│   ├── docker-compose.yml
│   ├── Dockerfile
│   ├── nuxt.config.ts
│   └── package.json
├── public/                         # SilverStripe webroot
├── .env                           # Environment variables
├── composer.json                  # PHP dependencies
└── README.md                      # This file
```

## How It Works

1. **Content Creation**: Pages are created in SilverStripe CMS
2. **Indexing**: When a page is published, it's automatically indexed in Elasticsearch via the `SearchableExtension`
3. **User Query**: User asks a question in the Nuxt frontend
4. **Search**: The backend searches Elasticsearch for relevant pages
5. **Context Building**: Search results are formatted as context for the LLM
6. **LLM Processing**: The LLM generates a response based only on the provided context
7. **Response**: The answer and source links are displayed to the user

## LLM Configuration

### Using Anthropic Claude (Recommended)

Set in `.env`:
```env
LLM_PROVIDER="anthropic"
ANTHROPIC_API_KEY="your-api-key"
```

Uses Claude 3.5 Sonnet model.

### Using OpenAI

Set in `.env`:
```env
LLM_PROVIDER="openai"
OPENAI_API_KEY="your-api-key"
```

Uses GPT-4 model.

### Knowledge Base Constraint

The LLM is instructed to:
- Only provide information from the Elasticsearch results
- Explicitly state when it doesn't have information
- Never make up or infer information not in the knowledge base

## Development

### Stopping Services

Stop DDEV:
```bash
ddev stop
```

Stop Nuxt frontend:
```bash
cd frontend
docker-compose down
```

### Debugging

View DDEV logs:
```bash
ddev logs
```

View Elasticsearch:
```bash
curl http://localhost:9200/_cat/indices
curl http://localhost:9200/silverstripe/_search?pretty
```

Test API directly:
```bash
curl "http://localhost:8080/api/chatbot/search?q=services"
```

### Customization

#### Modify Search Fields

Edit `app/src/Services/ElasticsearchService.php` to adjust:
- Index mappings
- Search fields and their weights
- Fuzzy matching settings

#### Customize LLM Behavior

Edit `app/src/Services/LLMService.php` to modify:
- System prompts
- Model selection
- Token limits
- Temperature settings

#### Update Chat UI

Edit `frontend/pages/index.vue` to customize:
- Styling
- Message display
- User experience

## Troubleshooting

### Elasticsearch Connection Failed

Ensure Elasticsearch is running:
```bash
ddev describe
curl http://localhost:9200
```

### LLM API Errors

- Check your API key is correct in `.env`
- Verify you have credits/quota with your LLM provider
- Check the logs for specific error messages

### CORS Issues

Update `CORS_ALLOWED_ORIGINS` in `.env` if accessing from a different domain:
```env
CORS_ALLOWED_ORIGINS="http://localhost:3000,http://your-domain.com"
```

### Pages Not Appearing in Search

Ensure pages are published and reindex:
```bash
ddev sake dev/tasks/PopulateDummyPagesTask
```

## License

MIT License - see LICENSE file for details