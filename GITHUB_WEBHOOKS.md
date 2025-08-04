# GitHub Webhooks Implementation

This application supports GitHub webhooks to automate various workflows and stay synchronized with your GitHub repositories.

## Setup

1. **Add webhook secret to your `.env` file:**
   ```
   GITHUB_WEBHOOK_SECRET=your-secret-here
   ```

2. **Run migrations:**
   ```bash
   php artisan migrate
   ```

3. **Configure webhook in GitHub:**
   - Go to your repository settings
   - Navigate to Webhooks
   - Add webhook with URL: `https://your-domain.com/api/github/webhook`
   - Set Content type to: `application/json`
   - Add your secret from step 1
   - Select events you want to receive

## Supported Events

### Push Events
- Automatically pulls changes when commits are pushed to the default branch
- Logs all push activities

### Pull Request Events
- Tracks PR creation, updates, and merges
- Logs PR activities for auditing

### Issue Events
- Monitors issue creation, updates, and closures
- Can be extended to integrate with your issue tracking

### Release Events
- Notifies when new releases are published
- Can trigger deployment workflows

### Other Events
- Star events
- Fork events
- Create/Delete branch or tag events

## Security

- All webhooks are verified using HMAC-SHA256 signatures
- CSRF protection is disabled for the webhook endpoint
- Failed signature verifications are logged

## Webhook Logs

All webhook events are logged in the `github_webhook_logs` table with:
- Event type
- Delivery ID
- Repository name
- Full payload
- Processing status
- Error messages (if any)

## Processing

Webhooks are processed asynchronously using Laravel queues:
- Immediate response to GitHub (200 OK)
- Background job processes the webhook
- Automatic retries on failure (3 attempts)
- Exponential backoff: 1 min, 5 min, 15 min

## Extending

To add custom webhook handling:

1. Edit `app/Jobs/ProcessGitHubWebhook.php`
2. Add your event handler method
3. Update the switch statement in the `handle()` method

Example:
```php
private function handleCustomEvent()
{
    // Your custom logic here
    $data = $this->data;
    
    // Process the webhook data
    // Update your database
    // Send notifications
    // Trigger other jobs
}
```

## Testing

Run the webhook tests:
```bash
php artisan test --filter=GitHubWebhookTest
```

## Troubleshooting

1. **Signature verification fails:**
   - Check your webhook secret matches in GitHub and .env
   - Ensure the request body is not modified by proxies

2. **Webhooks not being received:**
   - Check your URL is publicly accessible
   - Verify GitHub can reach your server
   - Check Laravel logs for errors

3. **Processing failures:**
   - Check the queue is running: `php artisan queue:work`
   - Review failed jobs: `php artisan queue:failed`
   - Check logs in `storage/logs/`