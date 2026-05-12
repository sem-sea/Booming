---
name: schema-builder
description: Use this skill when adding, fixing, or validating JSON-LD schema. Knows the canonical templates for Organization, BlogPosting, FAQPage, HowTo, SoftwareApplication, Person, BreadcrumbList, and Service. Triggers when editing `wp-content/themes/booming-venture/inc/seo.php` or when a `/add-schema` slash command runs.
allowed-tools: Read, Edit, Write, Bash, WebFetch
---

# schema-builder

For every page / post / pattern:

1. **Detect page type** from URL pattern and content. The mapping is in `inc/seo.php`:
   - Sitewide: `Organization` + `WebSite` (always)
   - `is_singular( 'post' )`: `BlogPosting` + `Person` (author) + `BreadcrumbList` + auto `FAQPage` + auto `HowTo`
   - `is_singular( 'service' )`: `Service`
   - `is_page( [ 'funnel-calculator', 'roi-forecaster' ] )`: `SoftwareApplication`

2. **Emit minimum required types** per page. Anything extra is opt-in via the `bv_emit_jsonld` filter.

3. **Fill every required property with real values**, never placeholders. Pull from:
   - Post title → `headline`
   - Post excerpt → `description`
   - First answer capsule (matched via regex `.bv-capsule`) → preferred `description` if richer
   - WP `get_the_date( 'c' )` → `datePublished`
   - WP `get_the_modified_date( 'c' )` → `dateModified`
   - Featured image full URL → `image`
   - Author user_meta → `Person.sameAs`

4. **Validate JSON parses** before commit:
   ```bash
   php -l wp-content/themes/booming-venture/inc/seo.php
   ```
   Then on a live page:
   ```bash
   curl -s $URL | python3 -c "import sys,re,json; \
     blocks = re.findall(r'<script type=\"application/ld\+json\">(.*?)</script>', sys.stdin.read(), re.S); \
     [json.loads(b) for b in blocks]; print(f'{len(blocks)} JSON-LD block(s) parsed OK')"
   ```

5. **Cross-link entities with `@id`**:
   - `Article.publisher` → `Organization @id`
   - `Article.author` → `Person @id`
   - `BreadcrumbList` items use canonical URLs
   - `SoftwareApplication.publisher` → `Organization @id`
   - `Service.provider` → `Organization @id`

6. **ISO 8601 dates with timezone**. `get_the_date( 'c' )` already does this.

7. **`sameAs` URLs** on every `Person` and `Organization`. The `Organization.sameAs` array is gated behind the `bv_organization_sameas` filter so Wikipedia / Wikidata / Crunchbase / GitHub can be added without touching theme code:
   ```php
   add_filter( 'bv_organization_sameas', function ( $list ) {
       $list[] = 'https://www.wikidata.org/wiki/QXXXXXX';
       $list[] = 'https://en.wikipedia.org/wiki/Booming_Venture';
       $list[] = 'https://www.crunchbase.com/organization/booming-venture';
       return $list;
   } );
   ```

## Canonical templates

When extending `inc/seo.php`, copy from these. Do NOT invent new shapes.

### Article (BlogPosting)
```json
{
  "@type": "BlogPosting",
  "@id": "{permalink}#article",
  "mainEntityOfPage": "{permalink}",
  "headline": "{H1}",
  "description": "{20-25-word answer capsule, verbatim}",
  "image": "{featured-image-url}",
  "datePublished": "{ISO-8601}",
  "dateModified": "{ISO-8601}",
  "author": { "@id": "{author-url}#person" },
  "publisher": { "@id": "{site-url}#organization" },
  "wordCount": 1850,
  "keywords": ["AI marketing", "GEO"]
}
```

### FAQPage (auto-extracted; do not hand-write)
```json
{
  "@type": "FAQPage",
  "mainEntity": [
    { "@type": "Question", "name": "Q?", "acceptedAnswer": { "@type": "Answer", "text": "A." } }
  ]
}
```

### SoftwareApplication (calculator pages)
```json
{
  "@type": "SoftwareApplication",
  "name": "Funnel Leak Calculator",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "offers": { "@type": "Offer", "price": "0", "priceCurrency": "EUR" },
  "isAccessibleForFree": true
}
```

## Never

- Hand-author schema in a pattern / WXR / template. `inc/seo.php` is the only source.
- Use string dates without timezone.
- Skip `@id` on Organization or Article (breaks the graph).
- Emit duplicate Organization when Yoast / Rank Math is active (guard with `bv_seo_active_plugin_handles_meta()`).
- Ship without parsing the JSON locally first.
