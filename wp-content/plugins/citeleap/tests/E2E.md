# CiteLeap , end-to-end smoke checklist

PHPUnit (`composer test`) covers the pure-PHP commercial layer in isolation:
plan capability gates, credit ledger arithmetic, license resolution,
top-up catalog + webhook handlers. **This document covers everything
the unit tests cannot reach**: rendered UI, real LLM calls, real
Freemius checkout, real WP admin flows.

Run this checklist before every public release. ~20 minutes end-to-end
on a clean WP install.

## Setup

1. Fresh WordPress install (any recent version, 6.6+).
2. Activate CiteLeap from the plugin zip.
3. In `wp-config.php`:
   - Production simulation: do NOT set `CITELEAP_DEV_MODE`.
   - Real Freemius: set `CITELEAP_FS_ID` + `CITELEAP_FS_PUBLIC_KEY`
     and drop the SDK at `vendor/freemius/wordpress-sdk/start.php`.
4. Settings tab: paste API keys for at least Claude. Pick a writing
   model.

## 1. Free plan baseline

- [ ] Top of every CiteLeap admin page shows the quiet info pill
      "CiteLeap , Free , 0 of 3 credits used this cycle".
- [ ] Calendar tab renders the locked-feature upgrade card with
      "Upgrade to Pro , $99/mo" button.
- [ ] Languages tab renders the same upgrade card.
- [ ] Settings tab , Auto-publish heading has a "Solo+ Upgrade" pill;
      the auto_mode `<select>` is disabled.
- [ ] Settings tab , Refresh heading has a "Solo+ Upgrade" pill; the
      refresh_auto_mode `<select>` is disabled.
- [ ] License & Credits tab loads, shows "Free" plan, "Upgrade plan"
      + "Buy top-up pack" CTAs, and the 4-card top-up grid in
      simulator mode.

## 2. Credit consumption + hard gate

- [ ] Planner tab , generate 3 ideas (or paste manual topics).
- [ ] Click "Write draft" on idea #1 , credits used should rise to 1,
      banner refreshes.
- [ ] Click "Write draft" on idea #2 , credits used should rise to 2.
- [ ] Click "Write draft" on idea #3 , credits used = 3, banner turns
      amber "credits running low".
- [ ] Click "Write draft" on idea #4 , should refuse with the error
      "Out of credits this cycle. Upgrade your plan or buy a top-up
      pack to draft more posts." Banner turns red.
- [ ] Log tab shows `credit_blocked` entry.

## 3. Simulator top-up flow

- [ ] License & Credits tab , click "Buy (simulated)" on the Growth
      pack ($199 / 50 credits).
- [ ] Success flash: "Growth pack purchased , 50 credits added to
      your reserve."
- [ ] Banner now shows "50 top-up credits in reserve."
- [ ] Click "Write draft" on idea #4 , should succeed, consume 1
      top-up credit (top-up balance drops to 49, monthly stays at 3).
- [ ] Log tab shows `topup_simulated` + `credit_consumed` entries.

## 4. Refresh plan gate

- [ ] Free plan , Planner tab , queue an existing post for refresh.
- [ ] Click "Refresh now" , should refuse with "Refresh is not
      available on the Free plan. Upgrade to Solo or higher."
- [ ] Log tab shows `refresh_blocked_plan` entry.

## 5. Dev mode

- [ ] Add `define( 'CITELEAP_DEV_MODE', true );` to wp-config.php.
- [ ] Reload any CiteLeap page , banner now shows green "Developer
      mode , Unlimited credits, all features enabled."
- [ ] Calendar tab renders normally (no upgrade card).
- [ ] Languages tab renders normally.
- [ ] Auto-publish + Refresh selects are enabled.
- [ ] Drafting consumes no credit; lifetime_used does not increment.

## 6. Freemius checkout (only when SDK is wired)

- [ ] Banner "Buy top-up pack" link should open Freemius checkout
      in a new tab (not the in-admin simulator).
- [ ] Complete a test-mode purchase of the Growth pack.
- [ ] Freemius webhook fires , `fs_after_purchase_citeleap` , credits
      land in the local ledger (verify on the License tab).
- [ ] Upgrade plan from Free to Pro , `fs_after_account_plan_change`
      resets the monthly cycle , banner now shows "0 of 30 credits
      used this cycle, Pro plan".

## 7. Cycle reset

- [ ] On a paid plan, fast-forward the cycle by editing the
      `citeleap_credits` option's `cycle_start` to last month.
- [ ] Reload any CiteLeap page , `used` should reset to 0 (top-up
      and lifetime_used must NOT reset).

## 8. Uninstall hygiene

- [ ] Deactivate + delete CiteLeap.
- [ ] Check `wp_options` , every `citeleap_*` option must be gone,
      including `citeleap_credits`.

## Regression triggers

Re-run the full PHPUnit suite + this checklist whenever:

- Plan capability list in `includes/plan.php` changes.
- Credit ledger shape in `includes/credits.php` changes.
- A new sprint adds a paid feature that should be plan-gated.
- Freemius SDK upgrades (the action / filter names can change).
- A new Plan tier is added (re-run `lowest_plan_with` tests).
