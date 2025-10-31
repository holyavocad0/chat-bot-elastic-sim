# Quick Start Guide

This guide will help you get the application up and running in minutes.

## Prerequisites Check

Before starting, ensure you have:
- ✅ DDEV installed: `ddev version`
- ✅ Docker running: `docker ps`
- ✅ Node.js 20+: `node --version`

## Step-by-Step Setup (5 minutes)

### 1. Environment Configuration (1 min)

```bash
# Copy environment file
cp .env.example .env

# Edit .env and add your API key
nano .env  # or use your preferred editor
```

Add your API key:
```env
LLM_PROVIDER="anthropic"
ANTHROPIC_API_KEY="sk-ant-xxxxx"
```

### 2. Start Backend Services (2 min)

```bash
# Start DDEV (SilverStripe + Elasticsearch)
ddev start

# Install PHP dependencies
ddev composer install

# Build database
ddev sake dev/build flush=1

# Create dummy pages
ddev sake dev/tasks/PopulateDummyPagesTask
```

### 3. Start Frontend (2 min)

```bash
# Navigate to frontend
cd frontend

# Copy environment file
cp .env.example .env

# Start with Docker
docker-compose up -d

# OR start locally (if you prefer)
npm install && npm run dev
```

## Access Your Application

- **Chat Interface**: http://localhost:3000
- **Admin Panel**: http://chat-bot-elastic-sim.ddev.site/admin
  - Username: `admin`
  - Password: `admin`

## First Test

1. Open http://localhost:3000
2. Type: "What services do you offer?"
3. Watch the AI respond based on your indexed pages!

## Common Issues

### Port Already in Use
If port 3000 or 8080 is in use:
```bash
# Change frontend port in docker-compose.yml
# Or stop the conflicting service
```

### Elasticsearch Not Starting
```bash
# Check DDEV status
ddev describe

# Restart DDEV
ddev restart
```

### API Key Error
Make sure your API key is:
- Correctly copied (no extra spaces)
- Has available credits
- Is for the correct provider (Anthropic or OpenAI)

## Next Steps

- ✅ Read the full [README.md](README.md) for detailed documentation
- ✅ Explore the SilverStripe admin to create custom pages
- ✅ Customize the chat UI in `frontend/pages/index.vue`
- ✅ Modify search behavior in `app/src/Services/ElasticsearchService.php`

## Stopping the Services

```bash
# Stop DDEV
ddev stop

# Stop frontend
cd frontend && docker-compose down
```

## Getting Help

If you encounter issues:
1. Check the [README.md](README.md) Troubleshooting section
2. View logs: `ddev logs`
3. Ensure all prerequisites are installed and updated

Happy coding! 🚀
