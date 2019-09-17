[unreleased]

#### 2.1.0 / 2019-09-17
* add extra setting to skip successful autoupdate emails
* add description to checkbox settings
* composer update

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
