# Queue Setup for LaraChat

## Important: Queue Worker Required

This application uses Laravel queues to process Claude messages in the background. You **MUST** have a queue worker running for the chat functionality to work.

## Starting the Queue Worker

### Option 1: Simple Queue Worker (Development)
```bash
php artisan queue:work
```

### Option 2: Queue Worker with Composer Dev Command
The project includes a composer script that runs everything together:
```bash
composer dev
```
This will run the Laravel server, queue listener, logs, and Vite concurrently.

### Option 3: Background Queue Worker
```bash
php artisan queue:work --daemon &
```

### Option 4: Using Supervisor (Production)
For production environments, use Supervisor to keep the queue worker running:

Create `/etc/supervisor/conf.d/larachat-worker.conf`:
```ini
[program:larachat-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/larachat/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/larachat/storage/logs/worker.log
stopwaitsecs=3600
```

Then reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start larachat-worker:*
```

## Troubleshooting

### Check if Queue Worker is Running
```bash
ps aux | grep "queue:work"
```

### Check for Failed Jobs
```bash
php artisan queue:failed
```

### Retry Failed Jobs
```bash
php artisan queue:retry all
```

### Clear Failed Jobs
```bash
php artisan queue:flush
```

### Monitor Queue in Real-Time
```bash
php artisan queue:listen
```

## How It Works

1. When you create a new conversation, the `ConversationsController` dispatches a `SendClaudeMessageJob`
2. The job runs Claude CLI commands and saves responses to both:
   - The database (messages table) for real-time polling
   - Session files (in storage/app/private/claude-sessions/) for persistence
3. The frontend polls the API to get message updates

## Queue Configuration

The queue is configured in `.env`:
```
QUEUE_CONNECTION=database
```

This uses database-backed queues, which are reliable for development and small-scale production use.