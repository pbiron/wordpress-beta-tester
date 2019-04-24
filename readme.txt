# WordPress Beta Tester

Tags: beta, advanced, testing
Contributors: westi, mlteal, afragen
Tested up to: 5.2
Requires at least: 3.1
Stable Tag: 2.0.4
License: GPLv2
License URI: https://www.opensource.org/licenses/GPL-2.0
Requires PHP: 5.2.4

Allows you to easily upgrade to Beta releases.

## Description
This plugin provides an easy way to get involved with Beta testing WordPress.

Once installed it will enable you to upgrade your website to the latest Beta or Release candidate at the click of a button using the built in upgrader.

By default once enabled it switches your website onto the point release development track.

For the more adventurous there is the option to switch to the bleeding edge (trunk) of development.

Don't forget to backup before you start!

### Extra Settings

The **Extra Settings** tab may contain choices for testing features in trunk that require constants to be set. A checked feature will add a constant to the user's `wp-config.php` file in the format as follows:

`define( 'WP_BETA_TESTER_{$feature}', true );`

Unchecking the feature will remove the constant.

This plugin resets the constants in `wp-config.php` on plugin activation and removes them on plugin deactivation. Use the filter `wp_beta_tester_config_path` to return a non-standard `wp-config.php` file path.

If no settings are present there is no testing to be done that requires this feature.

PRs are welcome on [GitHub](https://github.com/afragen/wordpress-beta-tester).

## Changelog

#### 2.0.4
* add update version information to settings page text

#### 2.0.3
* a11y fixes for settings tabs
* update `wp-cli/wp-config-transformer`

#### 2.0.2
* a11y fixes for checkbox, thanks @audrasjb

#### 2.0.1
* fix for incorrect last updated message

#### 2.0.0
* near complete re-write to use more OOPy practices
* put distinct process into separate classes
* allows for multiple settings tabs for addtional settings

#### 1.2.6
* remove extraneous code
* add GitHub Plugin URI header

#### 1.2.5
* fixed error message for downgrading version, thanks @andreas-andersson

#### 1.2.4
* don't use $GLOBALS

#### 1.2.3
* updated a few strings and correct typos
* run through WPCS linter
* fixed translation strings to include HTML in context and properly escape with `wp_kses_post()`
* fixed link to settings page under Multisite

#### 1.2.2
* change wording from blog to website

#### 1.2.0
* Escape output
* Indicate that _Bleeding edge nightlies_ are _trunk_
* new screenshot
* code improvements from linter

#### 1.1.2
* Remove anonymous function for PHP 5.2 compatibility.

#### 1.1.1
* fixed PHP notice for PHP 7.1
* made URL scheme agnostic

#### 1.1.0
* Fixed to work properly under Multisite.

#### 1.0.2
* Update tested up to version to 4.7.
* Fix the location of the settings screen in Multisite (moved under Settings in Network Admin).
* Minor text fixes.

#### 1.0.1
* Update tested up to version to 4.5.
* Fix PHP7 deprecated constructor notice.
* Change text domain to match the plugin slug.
* Update WordPress.org links to use HTTPS.
* Remove outdated bundled translations in favor of language packs.

#### 1.0
* Update tested up to version to 4.2.
* Update screenshot.
* Fix a couple typos.

#### See old-changelog.txt for previous changelog items

## Installation

1. Upload to your plugins folder, usually `wp-content/plugins/`
2. Activate the plugin on the plugin screen.
3. Navigate to Tools ... Beta Testing to configure the plugin.
4. Under Mulitsite, navigate to Settings ... Beta Testing to configure the plugin.
5. Visit Dashboard ... Upgrade (Or Tools ... Upgrade in versions before 3.0) and update to the latest Beta Release.

## Screenshots

1. This shows the main administration page for the plugin
2. This shows the Extra Settings page for the plugin
