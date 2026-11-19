# Remote deploy without wp-admin upload

`bv-self-update.php` is written into a WPVibe draft of the live theme
(`inc/bv-self-update.php`, plus a `require_once` in `functions.php`), then
the draft is published. On the next authenticated REST request or wp-admin
page view it downloads the pinned release zip from GitHub, checks the
SHA-256 and installs it with WordPress' Theme_Upgrader (overwrite). The
release package does not contain this file, so it disappears after the
install. Status: options `bv_self_update_status` and `bv_self_update_done`.

Update `BV_SU_URL` (pinned commit) and `BV_SU_SHA256` for every release.
