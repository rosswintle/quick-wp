# Quick WP

A CLI tool to spin up quick, disposable WordPress instances.

This is pretty beta right now. But it works. On MacOS at least. And probably Linux.

Don't use for prod! Or even dev! This is for testing and playing with WordPress.

This tool uses:
 - Your own installation of PHP (currently - in future I'm hoping to bundle a PHP binary with the tool)
 - WordPress
 - The SQLite plugin - no MySQL required!

## Installation

For now, you need PHP (v8.0+), Composer and SQLite installed. In future I'm hoping to remove this need.

1. Clone this repo
2. Run `composer install`
3. Run `php qwp` to see the available commands

## Usage

### `php qwp add <site name> {--wp-version=<version>}`

Adds a new WordPress instance to the current directory. It will be placed in a subdirectory
named after the site name you provide.

This uses the latest version of WordPress by default, but you can specify a version with the
`--wp-version` option. Nightlies, alphas, betas and RCs are not yet supported.

Sites are created on https://localhost:8001. Only run one site at a time.

Admin login is admin/admin.

Press `Ctrl-C` to stop the site.

*Note*: Quick WP symlinks in WordPress core files, so core is shared. Do not edit core files! (You
wouldn't anyway, right?). The wp-content directory is not shared, so you can edit themes and plugins.

## Problems?

If you have problems, delete the `storage` directory and try again.

Note that Quick WP keeps a little store of your sites (you can see this with `php qwp show`). In future this will be used
to re-start and properly delete sites.
