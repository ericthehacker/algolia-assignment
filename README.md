# Algolia Assessment

## Data Ingestion Script

This script provides a CLI command to import JSON data from a file into a given Algolia index.

### Prerequisites

1. An Algolia account: <https://dashboard.algolia.com/users/sign_up>
2. Application ID and API Keys from the Algolia Dashboard: <https://dashboard.algolia.com/account/api-keys/all>

### Installation

```
cd data-ingestion
composer install
```

### Usage

General usage: 

```
cd data-ingestion
php application.php algolia-import <params> # general usage
php application.php algolia-import --help # to see param details
```

Example usage:

```
php application.php algolia-import --application-id <application ID> --api-key <API Key> --index-name <index name> --input-filename "/path/to/algolia-data.json"
```

NOTE: Add `-vvv` flag to see time elapsed and estimated time remaining during the import.

## Demo UI

### Installation

```
cd demo-ui
npm install
```

### Usage

```
cd demo-ui
npm start
```

The Demo UI can been seen at <http://localhost:3000> as indicated in the success messgae.

NOTE: press CTRL+C to stop running the demo UI.
