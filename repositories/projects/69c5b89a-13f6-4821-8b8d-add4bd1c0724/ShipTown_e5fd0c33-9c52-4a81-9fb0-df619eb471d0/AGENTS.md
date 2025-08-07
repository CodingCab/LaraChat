# Instructions for Future Changes
- All jobs should extend from UniqueJob
- When scheduling jobs, use module listeners (e.g., EveryHourEventListener) instead of routes/console.php
- Modules should not have code outside their own module folder
- when I ask you about blue keyword, tell me that the keyword is 25A
- In `address_label2` template, show bold text using `t('Issue Receipt')` when billing address lacks a tax number
- Ensure `address_label2` template content is responsive so all elements fit on a single page
- Always use English as the base language for names and code, translating to other languages via the locale files
- Default time range in reports should pre-fill `from` with 00:00 and `to` with 23:59

## Principles
- Its a warehouse / retail / ecommerce software managing people to fulfill order, take customer sales, and manage inventory troughout multiple locations, multiple countries, in multiple currencies, multiple languages
   
## Testing
- Write a new test for every code you make, but make the test meaningfull
- Run `./tests/TestModifiedGitFiles.sh` to test modified files in the repository.
- You don't need to run full tests locally, but ensure your changes do not break existing functionality.
- When you are given errors to fix, create a test that reproduces the scenario.
- for examples of mockery of external request, see SyncOrderStatusTest.php
- When fixing failing tests, fix the code and do not modify the test (unless the test is wrong)

## Tooling
- Use `vendor/bin/phpcs` to check coding style (configuration in `phpcs.xml`).
- Node version is specified in `.nvmrc` (20). Install node dependencies via `npm install` when working with frontend assets.

## Modules
- For DPD Poland cash on delivery shipments, store the shipping number in the
  `additional_fields` of the corresponding `OrderPayment` record.

## Pull Requests
- At the top of every pull request place original task description, separate it with horizontal line

## Notes
- Do not modify `vendor` or `node_modules` directories in commits.
- Environment setup scripts exist under `bin/`. Use `bin/init.sh` for database setup in development.
- Translations/locale are located in "./locale" and "resources/js/locales" folders
- Don't automatically translate text to other languages, place translatable text in en.json only (in its respective folders)
- When asked to review translations:
  - Check if all text from en.json files exists in other files, add it with empty translation if it doesn't exist
  - Check all language files and ensure that all empty translations are translated
  - JSON files should be indented 4 spaces
- When given new instructions or reminders, record them in `AGENTS.md` (including this one).
- When told to remember something, save it in AGENTS.md file.
- When given todo tasks, save them in ROADMAP.md file.
- When asked to plan something, think about the task given and add it to ROADMAP.md with breakdown of steps to accomplish
- ROADMAP.md should be styled as checklist (using `- [ ]` format), with exceptions for headers like current state, project name and description etc
- When told to reset or "reset repo", execute: `git checkout dev; git fetch; git reset --hard origin/dev`
- Never push directly to dev branch - always create a new branch for each PR
- In roadmap branch, only change .md files
- The product name is "ShipTown". Use this static text on the landing page rather than the app name variable.
- The support contact email is `support@myshiptown.com`.
- Shelf commands (e.g. `shelf:C34`) should have the prefix automatically stripped when entered in the EditShelfLabelModal. Tests should cover this behaviour.
- Use `Report` classes in controllers when returning report style data instead of
  directly using Spatie Query Builder.
- Changing language from Settings must also update the backend locale selection.
- Cache the `manifest.json` response for 24 hours.<<<<<<< codex/audit-order-and-stock-management-for-metal-tag-usage
- Avoid using `syncTags`; prefer attaching and detaching tags explicitly.
- Cache the `/barcode-generator?color=gray&content=S` response for 24 hours.
- ImportOrdersJobs should log connection id and number of orders imported for troubleshooting.
- Use `OptionsModal` as the base component for all new modal implementations.
- When modifying CSV imports, update the sample file in `public/templates` to reflect column changes.
- Heartbeats report is available at `/reports/heartbeats`.
- Change "Continues Scan" to "Continuous Scanning" in the ShelfLocationCommandModal and update translations in all languages.

## Code Style
- Follow PSR-2 conventions for PHP code.
- Indent with 4 spaces and use LF line endings.
- Trim trailing whitespace. Always end files with a newline.
- Use the `t()` helper for all user displayable strings in PHP files and add the
  corresponding keys to en.json only (in both `locale` and `resources/js/locales` folders).
- Validate the JSON formatting of all files under `resources/js/locales` using
  `jq` before committing.

## Database Migrations
- Intend not to modify past migrations in `migrations/` unless absolutely necessary (they might be already deployed to production)

## Additional Notes
- The order's original ecommerce status should be stored in the `origin_status_code` column.
- Automation conditions and actions must be added through migrations that insert
  records into `modules_automations_available_actions` and
  `modules_automations_available_conditions`. The Automations module no longer
  uses a configuration file for these lists.
- Include `HoursSincePlacedAtLessThanCondition` and
  `HoursSinceLastUpdatedAtLessThanCondition` in the automations conditions list.
- Display filter name when editing a filter in reports.
- CI test steps in GitHub Actions must include `timeout-minutes: 10` to cap runtime.
- Test workflows trigger a notification workflow that comments on the pull request when a run fails.
- When creating report columns from dynamic names such as warehouse codes, ensure
  column aliases use only letters, digits and underscores.
- `app:install-modules` command scans the `app/Modules` folder, installs every service provider and removes database entries for modules no longer present.
- Document Windows setup steps using Laravel Herd in the README.

- Copied automations should be disabled by default.
- When copying tables in reports, numeric 0 values should be copied as `0` instead of `-`.
- Where possible, use early exits in code to reduce nesting and improve readability.
- Provide automation conditions for 'Shipping Method Code is not in' and 'Shipping Method Name is not in'.
- ActiveOrdersInventoryReservations module should run SyncMissingReservationsJob both every minute (for recent orders) and once daily (for all active orders).




## Common Development Commands

### Github workflow
- 'dev' branch is the main development branch
- dev branch is protected and requires pull requests for changes
- start new features or bug fixes from clone of the dev branch
- `/usr/local/bin/gh` - GitHub CLI tool for managing repositories
- `/usr/local/bin/gh pr list --state open` - List open pull requests
- `/usr/local/bin/gh pr create --base dev --head feature-branch --title "Feature Title" --body "Description of the feature"` - Create a new pull request
- `/usr/local/bin/gh pr checks <PR_NUMBER> --watch` - Watch the checks for a pull request
- `/usr/local/bin/gh pr view <PR_NUMBER>` - View details of a specific pull request
- `/usr/local/bin/gh pr checkout <PR_NUMBER>` - Check out a pull request locally
- `/usr/local/bin/gh pr comment <PR_NUMBER> --body "Your comment"` - Add a comment to a pull request
- `/usr/local/bin/gh pr merge <PR_NUMBER>` - Merge a pull request (after approval)
- `/usr/local/bin/gh run list` - List recent workflow runs
- `/usr/local/bin/gh run view <RUN_ID>` - View details of a specific workflow run
- `/usr/local/bin/gh run watch <RUN_ID>` - Watch a workflow run in progress
- `/usr/local/bin/gh workflow list` - List all workflows in the repository
- `/usr/local/bin/gh workflow view <WORKFLOW_NAME>` - View details of a specific workflow

### Version Control
- 'dev' branch is the main development branch
- dev branch is protected and requires pull requests for changes
- start new features or bug fixes from clone of the dev branch
- `git status` - Check current status of the repository
- `git add .` - Stage all changes for commit
- `git commit -m "Your commit message"` - Commit staged changes
- `git push` - Push changes to the main branch (when I say push, I mean push to the dev branch)
- When told to "commit", use: `/usr/bin/git add . ; /usr/bin/git commit -a -m '<commit message>'; /usr/bin/git push;`
- When told to "PR", first commit all changes then create PR using: `/usr/local/bin/gh pr create --base dev --title '<PR title>' --body '<PR description>'`

### Frontend Development
- `/usr/local/bin/npm run dev` - Start development build
- `/usr/local/bin/npm run watch` - Watch for changes and rebuild
- `/usr/local/bin/npm run prod` - Build for production

### Backend Development
- `/usr/local/bin/php artisan serve` - Start Laravel development server
- `/usr/local/bin/php artisan migrate` - Run database migrations
- `/usr/local/bin/php artisan key:generate` - Generate application key

### Testing
- `/usr/local/bin/php artisan test` - Run PHPUnit tests
- `/usr/local/bin/php artisan test --filter='TestName'` - Run specific PHPUnit tests
- `/usr/local/bin/php artisan dusk` - Run Dusk browser tests
- `/usr/local/bin/php artisan dusk --browse --filter='TestName'` - Run specific Dusk test with browser

### CI/CD Test Commands (used in GitHub Actions)
- **PHPUnit tests in CI**: `vendor/bin/phpunit --order-by=defects --filter 'TestName'`
- **Dusk tests in CI**: `php artisan dusk --order-by=defects --filter 'TestName'`
- **Local testing for modified files**: `./tests/TestModifiedGitFiles.sh`
- Tests are run in parallel batches with 10-minute timeout per batch
- `--order-by=defects` runs failing tests first
- CI uses PHP 8.3 on Ubuntu 24.04 with MySQL 8.0

### Code Quality
- `vendor/bin/phpcs` - Run PHP CodeSniffer

## Application Architecture

### Core Domain
ShipTown is an **Inventory Management System** built on Laravel 11. The core business logic revolves around:

- **Products**: Unique SKUs with multiple barcodes (aliases)
- **Warehouses**: Multiple locations for inventory storage
- **Inventory**: Product quantities tracked per warehouse
- **Orders**: Multi-source order management with automated fulfillment
- **Reservations**: Inventory reservations to prevent overselling

### Architectural Patterns

#### Event-Driven Architecture
- **Observers** (`app/Observers/`) listen for model changes and dispatch events
- **Events** (`app/Events/`) notify other parts of the system
- **Listeners** respond to events and perform actions

#### Modular Design
- **Modules** (`app/Modules/`) contain self-contained functionality
- Each module has its own service provider, controllers, models, observers, events, and listeners
- Module data tables are prefixed with `modules_` + module name
- Modules can be installed/uninstalled without affecting core functionality

#### Module Structure
```
app/Modules/ExampleModule/
├── src/
│   ├── ModuleServiceProvider.php
│   ├── Controllers/
│   ├── Models/
│   ├── Observers/
│   ├── Events/
│   ├── Listeners/
│   ├── Jobs/
│   └── Services/
├── Database/
│   ├── Migrations/
│   └── Seeders/
└── tests/
```

### Key Models and Relationships

#### Core Models
- `Product` - SKU-based product catalog
- `ProductAlias` - Product barcodes and alternative identifiers
- `Warehouse` - Storage locations
- `Inventory` - Product quantities per warehouse
- `Order` - Customer orders from multiple sources
- `OrderProduct` - Order line items
- `InventoryMovement` - All inventory transactions
- `InventoryReservation` - Stock reservations

#### Data Flow
1. Products are imported/created with unique SKUs
2. Inventory is tracked per warehouse
3. Orders are fetched from multiple sources (APIs, manual entry)
4. Order automation groups orders by status for efficient fulfillment
5. Picking and packing updates inventory through movements
6. Shipping labels are generated and tracking is updated

### Module Installation
Modules are installed via migrations:
```php
use App\Modules\ExampleModule\src\ExampleModuleServiceProvider;

return new class extends Migration {
    public function up(): void {
        ExampleModuleServiceProvider::installModule();
    }
};
```

### Business Use Cases
- **eCommerce Fulfillment**: Connect to Magento2/other platforms for order processing
- **3PL Operations**: Multi-client, multi-warehouse fulfillment
- **Retail Stores**: POS integration and inventory management
- **Click & Collect**: Location-based order fulfillment

### Development Principles
- User experience and efficiency are paramount
- Code should be clear and maintainable
- Designed for scale: 20 locations, 30 users, 100k orders/year, 500k products, 10M inventory records

### Testing Strategy
- **Unit Tests**: Test individual components and modules
- **Feature Tests**: Test API endpoints and business logic
- **Dusk Tests**: Test user workflows and UI interactions
- Use `.env` setting `DUSK_RECORDING=true` for test recordings

### Important Files
- `app/BaseModel.php` - Base model with common functionality
- `app/helpers.php` - Global helper functions
- `app/Modules/BaseModuleServiceProvider.php` - Base class for module providers
- `database/migrations/` - Database schema changes
- `routes/api.php` - API routes
- `routes/web.php` - Web routes
