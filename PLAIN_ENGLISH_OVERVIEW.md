# What is this? Plain-English guide to everything in this repo

If you're not a developer, this is the file for you. It explains what each piece does in everyday words, who it's for, and how the pieces fit together. No jargon where it can be avoided.

This document lives on the `claude/master-learnings` branch so you can read it without knowing where to look first.

---

## What is this repo, in one paragraph?

This repo holds a **WordPress website kit for Booming Venture** (a marketing consultancy) and four separate **WordPress plugins** that were built to keep that website running, growing, and making money. Three of those plugins are specifically about getting blog posts written and seen by AI search engines (ChatGPT, Perplexity, Google AI Overviews, Claude, Microsoft Copilot). One plugin fixes a contact-form bug. Everything is built so you (the site owner) can install, configure, and forget it.

---

## What's a plugin, what's a theme?

- A **theme** is what your website looks like — the colours, fonts, page layouts, navigation. Booming Venture has its own theme called `booming-venture`.
- A **plugin** is an add-on that gives WordPress new abilities. Like an app on your phone, but for your website.

Both come as zip files. You upload them in WordPress admin under **Appearance → Themes** (for themes) or **Plugins → Add New** (for plugins).

---

## The cast of characters (what each piece does)

### 1. The Booming Venture theme

**Plain English:** This is the website itself. The home page, services page, blog layout, contact forms, calculators, lead-magnet pages — they all live here. If you uploaded a fresh WordPress and activated this theme, you'd see the Booming Venture site.

**What's special about it:** It's built to be picked up by AI search engines. When ChatGPT or Perplexity scrapes the web for answers, this theme structures its pages so they get cited instead of ignored. Every blog post has a TL;DR box, question-based headings, short answer paragraphs, statistics with sources, and a FAQ — exactly what AI engines look for.

**Where it lives:** Branch `claude/lovable-to-wordpress-theme-lnXza`, version 1.8.0.

---

### 2. Booming Venture Importer (the full setup tool)

**Plain English:** A one-click "fill my site with everything" button. After you upload the theme to a blank WordPress install, this plugin imports all 13 pages, 4 services, 83 blog posts, plus the menus and the homepage settings. Saves you weeks of manual content entry.

**Why it exists:** WordPress doesn't ship pre-filled. Without this plugin you'd have to copy-paste 83 blog posts and create every page by hand.

**Three buttons:**
- **Import / Re-import content** — adds everything bundled, skips anything that already exists (safe to re-run).
- **Force re-import content (WXR)** — rebuilds even if something already exists.
- **Re-import demo content** — same idea, kept for backward compatibility.

**Where it lives:** Same branch as the theme, version 1.1.0.

---

### 3. Booming Venture Blog Importer (just the blog)

**Plain English:** Same idea as #2 but ONLY the blog. If you already have a site with pages, services, and menus you don't want to disturb, this plugin adds or refreshes the 83 long-form blog posts without touching anything else.

**Three modes:**
- **Add new blog posts** — only adds posts that don't already exist.
- **Refresh existing post bodies** — overwrites the title, content, and excerpt of every existing post to match the bundled long-form versions (your blog drafts are NOT preserved). Use this once to upgrade old short posts to the new 1,200+ word versions.
- **Sync all** — adds missing AND refreshes existing in one click.

**Where it lives:** Same branch, version 1.0.1.

---

### 4. Booming Venture Brevo Form Fix

**Plain English:** Fixes a specific bug that makes contact forms hang forever when a visitor clicks Submit. The bug is in another plugin called Brevo (formerly Sendinblue) which Booming Venture uses to collect email signups. This fix sits next to Brevo, intercepts what goes wrong, and patches it.

**What it does in three sentences:**
1. Suppresses random PHP warnings that were leaking into the form's response and breaking it.
2. Auto-fills the visitor's Name from their Email address as they type (so even forms that only ask for email still work with Brevo, which requires a Name).
3. Treats "this email is already on our list" as a success instead of an error, so returning visitors don't see a confusing failure message.

**Where it lives:** Same branch, version 1.1.0. Install only if you use the Brevo plugin and your forms hang.

---

### 5. Booming Venture Blog Images

**Plain English:** Lets you pick a folder of images from your Media Library and have WordPress automatically assign one to each blog post as the Featured Image. The image then shows up as a hero at the top of the post AND as a thumbnail on the /blog/ overview page. Manual override always wins — if you set the Featured Image yourself, this plugin never overwrites it.

**The flow:**
1. Open **Tools → Blog Images Pool**.
2. Click "Open Media Library" — pick the images you want available (e.g. 30 photos).
3. Click "Assign random images now" — every blog post that has no Featured Image gets one from the pool.
4. Done. Hero appears on every single post, card thumbnail on the blog overview.
5. (Optional) "Re-randomise random-assigned posts" later if you want fresh picks.

**Where it lives:** Branch `claude/blog-images-plugin`, version 1.4.0.

---

### 6. Booming Venture SEO Boost

**Plain English:** Makes sure every page on your site is "findable" by AI engines and Google. It adds the technical tags that search engines need (schema markup, Open Graph, Twitter Card, canonical URLs) but ONLY where they're missing. If you already have Yoast or Rank Math, this plugin steps back and does nothing.

**On every new blog post publish it also automatically:**
- Tells Google about the new post (sitemap ping)
- Tells Bing about the new post (sitemap ping)
- Tells Bing's IndexNow service (which Microsoft Copilot uses)
- Hits the new post once to warm up your page cache

So the moment you publish, search engines know within seconds instead of waiting days for the next crawl.

**Where it lives:** Branch `claude/seo-schema-plugin`, version 1.0.0.

---

### 7. CiteLeap (the commercial AI content engine)

**Plain English:** This is the big one. It's an AI writer that runs INSIDE your WordPress site. You point it at Claude, ChatGPT, or Gemini using your own API key. It brainstorms blog post ideas, writes the posts, and publishes them on a schedule. You stay in full control — you can set a strict monthly spending limit, you can switch between "auto-publish" / "save as draft only" / "off", you can manually pin specific topics, and you can pause anything at any time.

**Why it's called CiteLeap:** Because the content it produces is specifically designed to get CITED by AI engines like ChatGPT and Perplexity. Every post follows research-backed rules from the May 2026 "GEO/AEO Bible" — statistics in the first 30%, question-based headings, answer paragraphs, FAQ sections, comparison tables. The goal isn't to rank on Google like old SEO; the goal is to be the source an AI engine quotes in its answer.

**The five tabs in the CiteLeap menu:**

| Tab | What's on it |
|---|---|
| **Dashboard** | Money you've spent this month, posts published this month, warnings if you're close to your spending limit, recent successes and errors |
| **Planner** | The queue. Every blog post idea (whether brainstormed by AI or pasted by you) lives here. You can write, schedule, pause, publish, refresh, or remove any of them. Plus the "How CiteLeap works" expandable panel at the top |
| **Prompts** | The exact instructions sent to the AI. You can edit them. There are three: the master writing prompt (what makes a good blog post), the idea-generation prompt (what makes a good topic), and a "custom additional instructions" field for your tone/banned-words/preferences |
| **Settings** | Your API keys (encrypted on disk), which AI model to use for which job, your monthly spending caps, your auto-mode toggle, your refresh schedule |
| **Log** | Every action ever taken, with severity colours so you spot errors fast |

**The four ways to get a blog post written:**

1. **Click "Generate ideas now"** — AI brainstorms 10 unique topics. Then click "Write draft" on any one. You publish manually.
2. **Paste topics into the bulk-add textarea** — One title per line. Each becomes a queued post. Same draft + publish flow.
3. **Auto mode = Draft only** — Every hour, the plugin picks the next queued topic, writes a draft, and waits for you. Your reviewer's queue fills automatically without anything going live.
4. **Auto mode = Publish** — Every hour at the next scheduled slot (e.g. Monday and Thursday at 10 AM), the plugin picks the next queued topic, writes it, schedules WordPress to publish it. Hands-off operation.

**The four ways to refresh an existing old post:**

1. **Manual checkbox** — Tools tab lists your posts oldest-first, tick the ones to refresh.
2. **Bulk paste** — Paste post IDs, slugs, or URLs into a textarea.
3. **Refresh mode = Draft (pending review)** — Every hour, the plugin researches one due post, parks the rewrite as "pending review" — your live post is untouched until you click Approve.
4. **Refresh mode = Live (overwrite)** — Same as above but the live post is overwritten immediately, no approval step.

**What you pay:**
- The plugin itself: $0 (you own it).
- Each blog post costs roughly 5 to 25 US cents in AI tokens depending on which model you choose. About 100 to 500 posts per $25.
- You pay the AI provider directly using your own API key. The plugin shows you the running total and warns you at 80% of your monthly budget.

**Where it lives:** Branch `claude/citeleap-plugin`, version 1.6.0.

---

### 8. PROJECT_PLAYBOOK.md (the 15-lesson conversion guide)

**Plain English:** A markdown file that documents the lessons learned converting Booming Venture from a Lovable (drag-and-drop builder) site into a WordPress site. Mainly useful if you're going to do another conversion like this — saves you from repeating expensive mistakes.

**Where it lives:** Repo root, on the main release branch.

---

### 9. LEARNINGS_MASTER.md

**Plain English:** A bigger version of the playbook. Captures every pattern, every anti-pattern, every surprise from building all of the above. Read this before starting any new work in this repo — it tells you what works, what doesn't, and what shortcuts exist.

**Where it lives:** Branch `claude/master-learnings`, repo root.

---

### 10. BRANCHES.md

**Plain English:** A map of every branch in this repo so you know which branch to be on when working on which thing. Companion to LEARNINGS_MASTER.md.

**Where it lives:** Branch `claude/master-learnings`, repo root.

---

### 11. PLAIN_ENGLISH_OVERVIEW.md (this file)

**Plain English:** What you're reading now. Built for non-developers. Updated whenever something new ships.

---

## Who is each thing for?

| Plugin / Theme | Who uses it |
|---|---|
| Booming Venture theme | Booming Venture itself (the marketing consultancy). Could be sold as a "B2B consultancy starter theme" later. |
| Booming Venture Importer | Booming Venture itself, on initial site setup. |
| Booming Venture Blog Importer | Anyone wanting to import just the long-form blog content into an existing WordPress site. |
| Booming Venture Brevo Form Fix | Anyone using the Brevo plugin who has hanging forms. Generic enough to help any Brevo site. |
| Booming Venture Blog Images | Anyone with a WordPress blog who wants automatic Featured Image assignment from a curated pool. |
| Booming Venture SEO Boost | Anyone with a WordPress site who doesn't already use Yoast / Rank Math and wants AI-search-engine visibility. |
| CiteLeap | Commercial product. Sold to B2B SaaS / agency / consultancy operators who want AI-generated content for lead generation, with full operator control. |
| Documentation files | Future you, future Claude sessions, anyone who joins the project later. |

---

## How do these pieces fit together?

You don't need ALL of them. Here are common combos:

### Combo A — "I'm Booming Venture, set up my entire site"
1. Booming Venture theme (1.8.0).
2. Booming Venture Importer plugin (full-site import).
3. Booming Venture Brevo Form Fix (if you use Brevo).

That's it. Everything else is optional.

### Combo B — "I have my own WordPress site, I just want the blog content"
1. Booming Venture Blog Importer plugin.

That's it. The plugin doesn't touch your theme or pages.

### Combo C — "I want the AI content engine"
1. CiteLeap plugin.

CiteLeap is standalone. It doesn't require the Booming Venture theme. Works on any modern WordPress site. Bring your own API keys.

### Combo D — "I want AI-search-engine visibility"
1. Booming Venture SEO Boost plugin (if you don't have Yoast / Rank Math).
2. Booming Venture Blog Images (so every post has a hero image for `og:image`).

### Combo E — "I want everything"
All seven plugins + theme. Install order doesn't matter as long as the theme is active.

---

## What can go wrong and how to fix it

| Symptom | Likely cause | First place to look |
|---|---|---|
| "Critical error on this website" after activating a plugin | PHP version too old, plugin needs 8.0+ | Hosting control panel → PHP version |
| Blog posts not appearing after upload | Importer wasn't activated, or didn't run | WP admin → Tools → Booming Venture Importer → "Force re-import" |
| Contact form spinner spins forever | Brevo plugin bug | Install Booming Venture Brevo Form Fix |
| Featured Image showing twice on single post | Theme renders it once, plugin renders it again | Update to Blog Images v1.2.0+ (auto-fixes) |
| CiteLeap "No API key configured" | API key never saved, or wp-config AUTH_KEY changed | Settings tab → re-paste your API key |
| CiteLeap "budget cap reached" | Monthly spending limit hit | Dashboard tab → raise the cap or wait until the 1st of next month |
| Posts not auto-publishing | Auto mode is OFF, or start date is in the future | CiteLeap Settings → Auto mode → Publish |

Every plugin has its own HOW_IT_WORKS.md or readme.txt with a longer troubleshooting matrix.

---

## Glossary (the jargon, decoded)

- **Plugin** — an add-on that gives WordPress new abilities.
- **Theme** — the design + layout of your site.
- **WXR** — WordPress's standard import file format (XML). Holds posts, pages, categories, and authors.
- **WP-Cron** — WordPress's task scheduler. Runs background jobs when someone visits your site.
- **API key** — a long string that proves you're you to an external service (like Claude or OpenAI). Treated like a password.
- **JSON-LD** — the format Google + AI engines prefer for "schema markup" (structured data embedded in your page).
- **GEO / AEO** — Generative Engine Optimization / Answer Engine Optimization. The 2026 equivalent of SEO, focused on getting cited by AI instead of ranked on Google.
- **Featured Image** — the main image WordPress associates with a blog post. Shows as the social-share image, the hero, and the card.
- **FSE / Block theme** — WordPress's modern theme system, where everything is built from drag-and-drop blocks.
- **Hero image** — the big image at the top of a single blog post.
- **Card thumbnail** — the small image shown next to a post on the /blog/ overview page.
- **og:image** — the special image tag that Facebook, LinkedIn, and Slack use when someone shares your link.
- **IndexNow** — a protocol Microsoft + Yandex use to instantly tell their search engines about new pages.
- **Token** — the unit AI providers charge by. Roughly 4 characters of text = 1 token. A 1,400-word blog post = ~5,000 tokens.
- **CET / CEST** — Central European Time / Central European Summer Time. The default timezone we set for European customers.
- **WP_DEBUG** — WordPress's logging setting. When ON, errors are written to a file you can read.
- **Cron lock / transient lock** — a way to make sure a scheduled task only runs once even if triggered multiple times.

---

## Last update

`2026-05-13`. Updated whenever a new plugin or major feature ships.
