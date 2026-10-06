# Digital Wallet API

A Laravel 13 digital-wallet coding challenge implementation supporting:

- Receiving bank transaction webhooks for multiple bank formats.
- Duplicate-safe transaction ingestion using SHA-256 fingerprints.
- Asynchronous webhook processing through Laravel events and queued listeners.
- Bank-specific parsers behind a registry-based abstraction.
- Normalisation of bank-specific transactions into a common parsed transaction model.
- Optional bank-specific transaction metadata.
- An XML payment-request endpoint for the challenge's "Sending Money" requirement.
- An authenticated Filament dashboard for inspecting raw and parsed transactions.
- API documentation generated with Scribe.
- Bruno requests for exercising the API manually.
- Automated unit and feature tests with Pest.

## Tech Stack

- PHP 8.3+
- Laravel 13
- SQLite by default (configurable through Laravel's database configuration)
- Filament 5
- Livewire 4
- Laravel Fortify
- Laravel Sanctum
- Spatie Laravel Permission
- Pest 5 / PHPUnit
- Scribe 5 for API documentation
- Bruno for manual API testing
- Vite / Tailwind CSS for the frontend assets

## Requirements

Make sure the development machine has:

- PHP 8.3 or newer
- Composer
- Node.js and npm
- SQLite with the PHP SQLite extension enabled (for the default configuration)

## Installation

Clone the repository and enter the project directory:

```bash
git clone <repository-url>
cd wallet-app
```

Install PHP dependencies:

```bash
composer install
```

Create the environment file if it does not already exist:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

The default `.env.example` uses SQLite. Create the database file if necessary:

```bash
touch database/database.sqlite
```

Run the migrations:

```bash
php artisan migrate
```

Install and build frontend dependencies:

```bash
npm install
npm run build
```

The repository also provides a setup script that performs the main setup steps:

```bash
composer run setup
```

## Running the Application

Start the Laravel development environment with:

```bash
composer run dev
```

The API is available under `/api`.

The Filament administration panel is available at:

```text
/admin
```

### Queue worker

Webhook processing listeners implement `ShouldQueue`, and the default `.env.example` configuration uses Laravel's database queue:

```env
QUEUE_CONNECTION=database
```

When running the application outside a development process that already starts a queue worker, start one separately:

```bash
php artisan queue:work
```

The queue is important for the receiving-money flow because the webhook endpoint stores the incoming batch and hands the heavier parsing work to the queue instead of doing all parsing synchronously inside the HTTP request.

## Database

The receiving-money flow uses three main tables.

### `transactions`

Stores the original bank transaction line exactly as received, together with ingestion information:

- `webhook_id` — identifies the webhook batch.
- `bank_name` — bank/parser used for the batch.
- `raw_line` — original transaction line.
- `raw_line_hashed` — SHA-256 fingerprint used for duplicate detection.
- `status` — `pending`, `processed`, or `failed`.

`raw_line_hashed` has a unique database constraint. This provides a database-level uniqueness guarantee even if the same transaction is encountered in different webhook deliveries.

### `parsed_transactions`

Stores the common representation extracted from the raw bank transaction:

- `transaction_id`
- `reference`
- `amount`
- `date`

This table provides a bank-independent representation for the rest of the application.

### `transaction_metadata`

Stores optional key/value metadata associated with a transaction. This allows a bank such as PayTech to expose additional fields without adding bank-specific columns to `parsed_transactions`.

## Receiving Money: Webhook Flow

The receiving-money flow is:

```text
Bank
  |
  | POST /api/webhook/{bank_name}/payment
  | text/plain
  v
TransactionController
  |
  | WebhookReceived event
  v
HandleWebhook (queued listener)
  |
  v
WebhookHandler
  |
  | split webhook into transaction lines
  | calculate SHA-256 fingerprints
  | remove duplicates
  | bulk insert raw transactions
  v
TransactionParse event
  |
  v
ParseTransaction (queued listener)
  |
  v
TransactionParser
  |
  | resolve bank parser
  | validate line format when supported
  | parse each transaction
  | bulk insert parsed transactions
  | bulk insert metadata when supported
  | update transaction status
  v
Parsed transaction data
```

### Webhook endpoint

```http
POST /api/webhook/{bank_name}/payment
Content-Type: text/plain
```

`{bank_name}` is used to select the parser from `BankParserRegistry`. Bank names are registered in `BankParserServiceProvider` and are currently lowercase.

Example:

```text
POST /api/webhook/paytech/payment
```

A webhook can contain multiple transaction lines separated by newlines.

### Duplicate handling

Duplicate transactions are identified using:

```text
SHA-256(raw transaction line)
```

Duplicates are removed both within the incoming webhook and against transactions that have already been persisted.

The webhook identifier is intentionally **not** used as the transaction uniqueness key. One webhook may contain many transactions, so `webhook_id` identifies the batch rather than an individual transaction.

## Supported Banks

### PayTech

PayTech transactions are parsed from the bank-specific format described by the challenge. The parser extracts:

- Date
- Amount
- Reference
- Key/value metadata

Example raw line:

```text
20250615156,50#202506159000001#note/debt payment march/internal_reference/A462JE81
```

The PayTech parser implements `HasMetadata`, so its key/value section is persisted in `transaction_metadata`.

### Acme

Acme transactions contain:

- Amount
- Reference
- Date

Example:

```text
156,50//202506159000001//20250615
```

Acme does not implement `HasMetadata` because its format does not contain the PayTech-style metadata section.

## Adding a New Bank Parser

The parser system is intentionally registry-based. Adding a bank does not require modifying a large `if`/`match` statement in the transaction service.

### 1. Create the parser

Create a class under:

```text
app/BankParser/
```

Implement `BankParser`:

```php
namespace App\BankParser;

class NewBank implements BankParser
{
    public function name(): string
    {
        return 'newbank';
    }

    public function parse(string $rawLine): array
    {
        // Convert the bank-specific line into the application's
        // canonical transaction representation.

        return [
            'date' => '...',
            'amount' => 0.0,
            'reference' => '...',
        ];
    }
}
```

The parser should hide the bank's format-specific rules. `TransactionParser` should not need to know how the new bank separates or encodes its fields.

### 2. Add format validation when appropriate

If the bank needs an explicit format check, implement `HasMatchingFormat`:

```php
class NewBank implements BankParser, HasMatchingFormat
{
    public function isMatchingFormat(string $rawLine): bool
    {
        return (bool) preg_match('/.../', $rawLine);
    }
}
```

A line that fails the format check is marked as `failed` instead of being parsed.

### 3. Add metadata support when appropriate

If the bank contains arbitrary key/value metadata, implement `HasMetadata` and provide the bank-specific conversion into the metadata rows expected by the application.

Banks without metadata do not need to implement this interface.

### 4. Register the parser

Add the parser to `BankParserServiceProvider`:

```php
$registry->register($app->make(NewBank::class));
```

The relevant file is:

```text
app/Providers/BankParserServiceProvider.php
```

This makes the parser available through `BankParserRegistry`.

### 5. Add tests

Add parser tests covering at least:

- Valid transaction parsing.
- Invalid/mismatched input when `HasMatchingFormat` is used.
- Amount/date/reference extraction.
- Metadata extraction if applicable.
- Multiple transactions in a webhook.
- Duplicate handling through the webhook handler.

Existing examples are:

```text
tests/Unit/TransactionParserTest.php
tests/Unit/WebhookHandlerTest.php
tests/Feature/Apis/PaytechWebhookTest.php
tests/Feature/Apis/AcmeWebhookTest.php
```

### 6. Add a Bruno request

Add a request to:

```text
wallet_app_apis/
```

The repository currently contains:

```text
wallet_app_apis/
├── PayTech.yml
├── Acme.yml
├── payment_request.yml
└── opencollection.yml
```

## Sending Money

The sending-money side intentionally does not persist a payment or track bank communication. The challenge explicitly limits this part to XML generation.

The flow is:

```text
POST /api/payment
       |
       v
PaymentRequestFormRequest
       |
       | validate HTTP input
       v
PaymentRequestDto
       |
       v
PaymentRequestXmlGenerator
       |
       | PHP XMLWriter
       v
application/xml response
```

### Endpoint

```http
POST /api/payment
Content-Type: application/json
Accept: application/xml
```

The request uses snake_case at the HTTP boundary. The internal DTO uses camelCase properties.

Example:

```json
{
  "reference": "e0f4763d-28ea-42d4-ac1c-c4013c242105",
  "date": "2025-02-25 06:33:00+03",
  "amount": "177.39",
  "currency": "SAR",
  "sender_account_number": "SA6980000204608016212908",
  "bank_code": "FDCSSARI",
  "receiver_account_number": "SA6980000204608016211111",
  "beneficiary_name": "Jane Doe",
  "notes": [
    "Lorem Epsum",
    "Dolor Sit Amet"
  ],
  "payment_type": "421",
  "charge_details": "RB"
}
```

### XML generation

`PaymentRequestXmlGenerator` uses PHP's `XMLWriter` support and divides the document into the same logical sections as the required XML format:

- `TransferInfo`
- `SenderInfo`
- `ReceiverInfo`
- `Notes`
- `PaymentType`
- `ChargeDetails`

The generator also implements the conditional XML rules:

- `Notes` is omitted when there are no notes.
- `PaymentType` is omitted when its value is `99`.
- `ChargeDetails` is omitted when its value is `SHA`.

## API Documentation

API documentation is generated with **Scribe**.

Scribe is configured in:

```text
config/scribe.php
```

Endpoint documentation is defined alongside the controllers using Scribe annotations/attributes and generated from the Laravel routes.

Generate the documentation with:

```bash
php artisan scribe:generate
```

The generated documentation is available through Scribe's Laravel routes:

```text
/docs
/docs.postman
/docs.openapi
```

The generated OpenAPI and collection artifacts are also stored under the application's Scribe storage location.

The API currently includes documentation for the webhook and payment-request endpoints.

## Bruno

The project includes a Bruno-compatible API collection under:

```text
wallet_app_apis/
```

The collection contains manual requests for:

- PayTech webhook
- Acme webhook
- Payment request XML generation

The collection defines a `localhost` variable pointing at:

```text
127.0.0.1:8000/api
```

Import/open the `wallet_app_apis` collection in Bruno and update the variable if the application runs on a different host or port.

The webhook requests use `text/plain` bodies because the receiving endpoint consumes the raw bank statement rather than a JSON wrapper.

## Filament Dashboard

The project includes a Filament admin panel at:

```text
/admin
```

The panel provides resources for inspecting:

- Raw `Transactions`
- Parsed `ParsedTransactions`
- Users

The transaction resources are intended primarily for inspection. Transaction policies prevent normal users from creating, editing, or deleting transaction records through the policy layer.

Only users with the `admin` or `moderator` role can access the Filament panel.

### Default seeded admin

`DatabaseSeeder` creates the default administrator account if it does not already exist. The seeder is safe to run again because it skips creating the account when the admin already exists.

Default credentials:

```text
Email:    admin@admin.com
Password: password
```

Run the database seeders after running the migrations:

```bash
php artisan db:seed
```

You can then sign in to the Filament dashboard at `/admin` using the credentials above.

> **Important:** These credentials are intended for the local coding-challenge environment only. Change or remove the default credentials before using the application in a production environment.

## Testing

The project uses Pest with Laravel's testing integration.

Run the complete suite with:

```bash
php artisan test
```

The Composer test script additionally performs formatting and static analysis:

```bash
composer run test
```

Individual test groups can be run with Pest/PHPUnit filters as needed.

### Test organisation

```text
tests/
├── Unit/
│   ├── PaymentRequestXmlGeneratorTest.php
│   ├── TransactionParserTest.php
│   └── WebhookHandlerTest.php
│
└── Feature/
    ├── Apis/
    │   ├── AcmeWebhookTest.php
    │   ├── PaytechWebhookTest.php
    │   └── PaymentRequestXmlTest.php
    └── DashboardTest.php
```

The test environment uses an in-memory SQLite database and synchronous queues, as configured in `phpunit.xml`. This keeps tests isolated and ensures queued listeners execute during the test request instead of requiring a separate queue worker.

## Performance and Scaling

The receiving webhook path is designed around batch processing rather than issuing a database operation for every transaction.

The webhook handler:

1. Splits the incoming statement into lines.
2. Calculates fingerprints in memory.
3. Removes duplicates in memory.
4. Performs a bulk database insert.
5. Dispatches one parsing event for the webhook batch.

The parser similarly collects parsed transactions and metadata and writes them in bulk rather than performing an insert/update for every individual transaction.

This is particularly relevant to the challenge's requirement to handle a webhook containing around 1,000 transactions at acceptable performance.

## Design Decisions

### Bank Parser Registry

Bank-specific parsing is isolated behind `BankParser` implementations and resolved through `BankParserRegistry`. This avoids coupling the transaction service to individual banks.

### Batch-level processing

A webhook receives a natural batch of transactions, so the system dispatches a parsing event containing the webhook ID and bank name rather than dispatching an event for every transaction.

### Raw and parsed transaction separation

The original bank line is retained in `transactions`, while the common application representation is stored in `parsed_transactions`. This keeps the original source data available for inspection and makes parsing a separate concern.

### SHA-256 duplicate fingerprint

The raw line is stored separately from its SHA-256 fingerprint. The fingerprint has a fixed length and a unique database constraint, avoiding the need for a large unique index on the raw `LONGTEXT` value.

### Optional parser capabilities

`HasMatchingFormat` and `HasMetadata` are capability interfaces. A parser only implements the behaviour it needs instead of forcing every bank to implement bank-specific functionality that its format does not contain.

### No persistence for outgoing payment requests

The sending-money requirement only asks for XML generation. The project therefore does not create an outgoing payment database record or implement bank communication/tracking for that part.

## Project Structure

Important application areas:

```text
app/
├── BankParser/                 Bank parser contracts and implementations
├── DTOs/                       Internal data-transfer objects
├── Events/                     Webhook and parsing events
├── Listeners/                  Queued event listeners
├── Http/
│   ├── Controllers/            HTTP entry points
│   └── Requests/               HTTP validation
├── Models/                     Eloquent models
├── Services/                   Core application services
├── Filament/Resources/         Admin dashboard resources
└── Providers/                  Service registration, including parser registry

database/
├── factories/                  Test/example data factories
├── migrations/                 Database schema
└── seeders/                    Roles, users and example transaction data

tests/
├── Unit/                       Service/parser unit tests
└── Feature/                    HTTP and application-flow tests

wallet_app_apis/                Bruno API collection
config/scribe.php               Scribe configuration
```

## Useful Commands

```bash
# Install dependencies
composer install
npm install

# Application setup
composer run setup

# Start development environment
composer run dev

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Run queue worker
php artisan queue:work

# Run tests
php artisan test

# Run formatting/static-analysis test script
composer run test

# Check formatting only
composer run lint:check

# Run PHPStan
composer run types:check

# Generate API documentation
php artisan scribe:generate
```

## API Summary

| Method | Endpoint | Purpose |
|---|---|---|
| `POST` | `/api/webhook/{bank_name}/payment` | Receive a bank transaction statement |
| `POST` | `/api/payment` | Generate the standard payment-request XML |

For complete request/response details, examples, and interactive testing, use the Scribe documentation at `/docs` or the Bruno collection in `wallet_app_apis/`.
