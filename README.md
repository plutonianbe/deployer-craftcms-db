# deployer-craftcms-db

[Deployer](https://deployer.org) recipe for [CraftCMS 3](https://craftcms.com) that provides database pull and push functionalities.

Database information and credentials get read from the `.env` file, by parsing `DB_DSN`, `DB_USERNAME` and `DB_PASSWORD`.

## Installation

Use composer to install the package and its recipes:

`composer require plutonianbe/deployer-craftcms-db --dev`

After the installation include the recipes:

`require __DIR__ . '/vendor/plutonianbe/deployer-craftcms-db/craft-db.php';`

## Available tasks

### `db:pull`

Pulls the database from the remote host and replaces the local one.

### `db:push`

Pushes the local database to the remote host and replaces it.

## TODO

- Provide credentials through configuration

## License

Licensed under the [MIT license](https://github.com/plutonianbe/deployer-craftcms-db/blob/master/LICENSE).