# Quick WP

A CLI tool to spin up quick, disposable WordPress instances.

This is pretty beta right now. But it works. On MacOS at least. And probably Linux.

Don't use for prod! Or even dev! This is for testing and playing with WordPress.

This tool uses:
 - Your own installation of PHP (currently - in future I'm hoping to bundle a PHP binary with the tool)
 - WordPress
 - The SQLite plugin - no MySQL required!

## Installation

For now, you need PHP (v8.1+) and Composer installed. In future I'm hoping to remove this need.

1. Clone this repo
2. Run `composer install`
3. Run `php qwp` to see the available commands

## Usage

### `php qwp add <sitename> {--wp-version=<version>} {--path=<install-path>}`

Adds a new WordPress instance to the current directory.

By default it will be placed in a subdirectory of the current directory named after the site name you provide. e.g. if you
are in /Users/me/qwp-sites and you run `php qwp add mysite`, the site will be created in /Users/me/qwp-sites/mysite.

If you have used the `qwp config` command to set a default path, the site will be created as a subdirectory of that path.

If you specify a path with the `--path` option, the site will be created in that path (not as a subdirectory!)

This latest version of WordPress is installed by default. You can specify a version with the
`--wp-version` option. You can get the latest nightly build by specifying 'nightly'. Betas
and RCs can be obtained by passing the relevant version number, e.g. '6.2-RC4'.

Sites are created on https://localhost:8001. Only run one site at a time.

Admin login is admin/admin.

Press `Ctrl-C` to stop the site.

*Note*: Quick WP symlinks in WordPress core files, so core is shared. Do not edit core files! (You
wouldn't anyway, right?). The wp-content directory is not shared, so you can edit themes and plugins.

### php qwp start {<sitename>}

Starts an existing Quick WP site.

If you don't specify a sitename you will be shown a list of installed sites to choose from.

### `php qwp remove <site name>`

Removes a Quick WP site.

This will delete the specified site including all its files and the database.

If you don't specify a sitename you will be shown a list of installed sites to choose from.

### `php qwp list`

This lists the installed Quick WP sites along with their directories and versions.

### `php qwp config {--default-path=<path>}`



## Problems?

If you have problems, delete the `.quick-wp` directory in your `$HOME` directory and try again.

Doing this may break existing sites (as WordPress core files for each site is symlinked to shared installs in `.quick-wp`)
