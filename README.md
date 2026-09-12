# Global Media Star

A complete WordPress business & entrepreneurship magazine for **Global Media Star**
(Connect • Create • Inspire), built to the requested site structure with a custom
theme, editorial content and working forms.

## Running it

```bash
./start.sh            # http://localhost:8080
./start.sh 9000       # or any other port
```

MySQL must be running (`sudo systemctl start mysql`).

| | |
|---|---|
| Site | http://localhost:8080 |
| Admin | http://localhost:8080/wp-admin |
| Username | `admin` |
| Password | `admin123` |
| Database | `cbw_magazine` (user `cbw_user`, password `cbw_pass_2026`) |

## Site structure

Every page from the requested tree exists, with the same parent/child nesting.
47 pages in total:

```
HOME                    /
BUSINESS                /business/
  Business News, Business Trends, Companies, Corporate, Industry Insights
ENTREPRENEURS           /entrepreneurs/
  Entrepreneur Stories, Founder Stories, CEO Stories, Leadership, Success Stories
FEATURES                /features/
  Founder Features, CEO Features, Business Features, Startup Features,
  Entrepreneur Spotlight, Business Profiles
STARTUPS                /startups/
  Startup Stories, Startup News, Startup Founders, Startup Success Stories,
  Startup Insights
MARKETING               /marketing/
  Digital Marketing, Business Branding, Personal Branding, B2B Marketing,
  Thought Leadership
PR & MEDIA              /pr-media/
  Business PR, Startup PR, Entrepreneur PR, Media Coverage, Business Publicity
MAGAZINE                /magazine/
  Latest Issue, Magazine Archive, Featured Entrepreneurs
ABOUT                   /about/
  About Us, Editorial Team, Contact, Advertise With Us
```

Each section page is backed by a matching category (under `/topic/…`), so
articles filed to a category appear automatically on its page, on its parent
section page, and on the homepage.

## What is in the site

- **128 articles** across all 32 leaf sections, each with a standfirst, pull
  quote, subheads, takeaway list, tags and generated cover artwork.
- **8 magazine issues** with covers, at `/issue/…` and in the archive.
- **5 bylined authors** with bios and author archives.
- **Working contact form** — submissions are stored under *Messages* in
  wp-admin and emailed to the admin address. Nonce-protected, validated,
  honeypot-guarded, post/redirect/get.
- **Working newsletter signup** in the homepage band, deduplicated by address.

## Theme

`wp-content/themes/cbw-magazine` — a custom theme ("GMS Magazine"), no page builder.

```
functions.php              setup, assets, widget areas
header.php / footer.php    top bar, masthead, mega-menu nav, structural footer
front-page.php             magazine homepage (hero slider, picks carousel, section
                           explorer, per-section blocks, stats band, issues carousel)
single.php                 article: standfirst, share, author box, related
archive.php / index.php    category, tag, author and date archives
search.php / 404.php
single-cbw_issue.php       magazine issue
archive-cbw_issue.php      all issues
page-templates/
  template-section.php     section landing (used by all 38 section pages)
  template-issues.php      Latest Issue / Magazine Archive
  template-contact.php     Contact
inc/
  icons.php                inline SVG icon set, section → icon map
  template-tags.php        cards, meta, breadcrumbs, share, newsletter, ticker,
                           carousel buttons, social links
  nav-walker.php           dropdown mega-menu walker
  post-types.php           Magazine Issue post type
  forms.php                contact + newsletter handling, Messages admin screen
  i18n.php                 English / Hindi / Marathi switching
  theme-mode.php           light / dark / auto, server-stamped to avoid a flash
  admin-style.php          the admin and sign-in page in the magazine's colours
assets/css/main.css        design system (navy + gold), fully responsive
assets/js/main.js          nav panel, language dropdown, colour mode, slider,
                           carousels, ticker, scroll reveals, count-ups, back-to-top
```

## Homepage and motion

| Block | What it does |
|---|---|
| **Hero slider** | The five latest stories, full width. Crossfade with a slow zoom on the photo, headline and standfirst rising in, numbered tabs whose gold bar fills as the slide's timer runs. Arrows, pause button, swipe, ←/→ keys. |
| **Trending ticker** | Eight latest headlines scrolling under the nav on every page, with a pause button. |
| **Editor's Picks** | Eight stories in a scroll-snap carousel — arrow buttons, mouse drag, native swipe on phones. |
| **Explore sections** | All eight sections as photo cards with an icon, story count and sub-section count. Each card uses a different photo. |
| **By the numbers** | Stories, issues, editors and languages, counted from the database; the numbers count up as they scroll in. |
| **The Magazine** | Eight issue covers in a carousel, tilting slightly on hover. |

**Navbar.** Each section has an icon. A gold bar grows under the hovered item,
dropdown links arrive in sequence, and once the masthead scrolls away the bar
turns to frosted glass with a reading-progress line along its bottom edge. The
icons are hidden between 901 and 1280px, the widths where nine sections plus
icons would not fit on one row.

**How the slider's timer works.** The timer *is* the CSS fill animation on
the active tab, and `animationend` moves to the next slide. Pausing is just
`animation-play-state`, so hovering, keyboard focus and the pause button all
stop it exactly where it is. Inactive slides are `inert` and `aria-hidden`.

**Reduced motion.** Under `prefers-reduced-motion` there is no autoplay,
marquee, zoom, drift or entrance animation; every control still works. This is
done component by component rather than by setting every animation to a
near-zero duration, because that would make the slider's timer end instantly
and skip through the slides.

**Nothing depends on scripts to be seen.** Entrance reveals only hide content
under an `html.js` class set in `<head>`, and the language list opens on
keyboard focus when scripts are off.

## Branding

The masthead logo is the supplied Global Media Star artwork, set as the
WordPress **custom logo** (Appearance → Customize → Site Identity) and as the
**site icon** (favicon, cropped to the GMS emblem).

| | |
|---|---|
| Site title | Global Media Star |
| Tagline | Connect • Create • Inspire |
| Header logo | `brand/global-media-star-logo.png` (640px wide, transparent) |
| Site icon | `brand/gms-site-icon.png` (512×512) |
| Original supplied file | `brand/global-media-star-logo-original.png` |

The logo is gold-and-black with dark lettering, so it sits on the **white**
masthead — on a dark background the "GLOBAL"/"STAR" words disappear. The dark
footer uses the text lockup (GMS mark + wordmark) instead, which reads cleanly
there.

Article cover artwork is generated with the GMS masthead baked in. If you change
the brand again, regenerate it rather than editing images by hand.

## Palette and colour mode

**Midnight Navy · Champagne Gold · Ivory**, in a **light and a dark theme**.
A three-state toggle in the top bar offers Light / Dark / **Auto** — Auto hands
control back to the operating system.

### Token structure

The stylesheet separates *literal* scales from *semantic* roles, which is what
makes two themes possible without duplicating rules:

- **Literal, shared by both themes** — `--navy-950/900/800`, `--gold-500/400`,
  the type scale, the radii. The bars, buttons and gold rules are the same
  midnight navy and champagne whichever theme is on.
- **Semantic, redefined per theme** — `--bg`, `--bg-soft`, `--bg-tint`,
  `--surface`, `--ink`, `--body`, `--muted`, `--link`, `--link-strong`,
  `--accent-text`, `--badge`, `--line`, `--line-soft`, `--rule`, the shadows.

| Role | Light | Dark |
|---|---|---|
| Page ground `--bg` | `#fbf8f2` ivory | `#0a1330` midnight navy |
| Alternating band `--bg-soft` | `#f4efe4` | `#0d1836` |
| Card / field `--surface` | `#ffffff` | `#111d45` (lifts *above* the page) |
| Headings `--ink` | `#141b2e` | `#f3efe4` |
| Body `--body` | `#3b4358` | `#c8cede` |
| Meta `--muted` | `#61687a` | `#98a1b6` |
| Link `--link` | `#2b4079` | `#9fb6e6` |
| Champagne as text `--accent-text` | `#806434` | `#d8be8b` |
| Category badge `--badge` | `#9c2f3b` | `#e59aa3` |

Selected states (chips, pagination, tag hovers) use `background:var(--ink);
color:var(--bg)` so they invert cleanly rather than hard-coding navy.

### No flash of the wrong theme

The choice lives in a `cbw_mode` cookie, so **PHP stamps `data-theme` on
`<html>` server-side** — the correct theme is in the first byte of HTML, with
no client-side repaint. With no cookie (Auto) a three-line inline script in
`<head>` resolves `prefers-color-scheme` before first paint, and the page keeps
following the system if it changes mid-session.

### The logo needed a dark variant

The supplied artwork sets "GLOBAL" and "STAR" in near-black, which disappears on
midnight navy. Rather than washing the whole mark out with a filter, a reversed
variant (`brand/global-media-star-logo-dark.png`) recolours only the near-black
strokes to ivory and leaves the gold untouched; dark mode swaps it in by CSS.

### Contrast is measured, not assumed

A script reads the *rendered* colour of every text node — compositing
semi-transparent layers, reading gradient backgrounds, and ignoring decorative
gradients such as the zero-width hover underline on card titles — then checks it
against WCAG AA.

**Six page types × both themes × three languages: zero failures**, roughly
5,800 elements. Real problems this caught and fixed:

- `--muted` and `--accent-text` cleared 4.5:1 on ivory but failed on the
  slightly darker `--bg-soft` band used by forms and the author box.
- The large sidebar rank numerals sat at 1.39:1.
- The footer brand anchor inherited the navy link colour on a navy ground.

Two further "failures" turned out to be faults in the measuring script, not the
site — it was not compositing alpha, and it was treating a decorative underline
gradient as a background. Both were fixed in the checker.

Magazine covers and editor avatars are generated in the same palette
(`brand/brandart.php`), so the artwork never drifts from the CSS.

## The admin and the sign-in page

Both wear the magazine's own navy and champagne, and both **restyle rather than
rebuild** — every rule dresses markup WordPress already prints, so a core update
changes the furniture and this only changes the paint. Nothing is moved or
hidden, and every field keeps the label and focus ring a password manager and a
screen reader rely on.

**The sign-in page** (`assets/css/login.css`) — midnight navy with a slow gold
drift behind the card, the GMS logo above the form, and a champagne sign-in
button. The card animates in; `prefers-reduced-motion` turns that off.

**The admin** (`assets/css/admin.css`) — navy menu, ivory workspace, champagne
accents on headings and focus rings, warm borders on list tables.

**The rail.** A champagne glow at the head of a navy gradient, a hairline down
the edge, a gold marker that slides in on the open section, icons that lean
toward the pointer, and submenu items that arrive in sequence rather than all
at once. Counters are champagne on navy instead of red.

**The hero.** A full-width band above the page title: the masthead, today's
date, a greeting, what shipped this week, four actions, a translation meter
that fills and shimmers, and the three most recent stories with edit links.

It renders through `in_admin_header`, **not** core's welcome panel. That panel is
switched by a `show_welcome_panel` user option core's own dismiss handler owns —
hanging the site's masthead on it meant it could vanish for reasons nothing to
do with this theme, which is exactly what happened during the build. Core's
panel is removed so the two never stack.

**The stats panel.** Core's "At a Glance" counts posts and pages and stops. This
site also has magazine issues, reader messages, a photo library with attribution
and three languages, none of which showed anywhere. Six tiles count all of it,
each a link into that screen, and the numbers count up on load. The count-up is
skipped under `prefers-reduced-motion`, and the values are in the markup
already, so the panel reads correctly if the script never runs. Stats are cached
for five minutes and flushed whenever a post or attachment changes.

It is a normal dashboard widget, so it can be collapsed, moved or switched off
in Screen Options. It is placed first **once**; after that the reader's own
arrangement is never overridden. It deliberately repeats nothing the hero
already shows.

**Cards** arrive with a short stagger, lift on hover, and carry a champagne
thread drawn under each heading. Every animation is turned off under
`prefers-reduced-motion`.

**The dashboard at width.** WordPress always prints four widget containers,
whatever column count is chosen, and it had every widget stacked in the first
one — so a wide screen showed one full column beside three empty rooms. The
widgets are now spread across two columns (once, under its own flag), the
dashboard is capped at 1720px so it does not stretch across a very large
monitor, and the unused third and fourth containers collapse when empty. They
reappear the moment the pointer enters the widget area, which is exactly when a
drag is under way and a drop target is needed.

### Problems this turned up

Restyling core surfaced four real defects, all fixed:

- **Every primary button in the admin had unreadable text.** `.wp-core-ui
  .button` set a dark colour and came *after* `.button-primary` in the sheet at
  equal specificity, so any `<a class="button button-primary">` lost its white
  label.
- **The sign-in card overflowed every phone by 36px.** `wp-login.php` leaves
  `#login` as `content-box`, so `width:100%` plus 18px of side padding measured
  426px in a 390px viewport.
- **The "?" beside Remember Me sat at 1.75:1** — core's dark grey on the navy
  card.
- **The widget reorder arrows sat at 2.33:1** on ivory, and Quick Edit at
  4.26:1.
- **The hero's own tones** — the champagne figures and the meter footnote —
  landed at 4.48:1 and 3.71:1 once the translucent panel lifted the ground
  beneath them.
- **The hero was cut off by the admin bar below 782px.** The bar switches to
  `position:absolute` there and reserves no space; a top margin could not fix
  it because the margin collapsed out to `#wpcontent` and moved the bar too, so
  the hero sits in a padded wrapper instead.

Verified with the same contrast checker used on the front end, at desktop and
mobile widths, across the dashboard, posts, media, settings, issues and
messages: **zero failures**. The disabled pagination arrows are deliberately
left dim — WCAG 1.4.3 exempts disabled controls, and that dimming is how they
read as unavailable.

Two more "failures" were faults in the checker, not the admin: it took only the
first colour stop of the first background layer, so the rail's two-layer navy
read as a 10%-champagne wash over white, and it could not resolve a control that
overlaps the hero without being inside it. The first was fixed by compositing
every layer; the second by giving that control its own ground.

## Images

| Where | What |
|---|---|
| Article featured images | Indian photo, themed to the section |
| Inside every article | A second photo with caption and credit, after the pull quote |
| Section pages (46) | Featured photo behind the dark header band, dimmed for legibility |
| About / Contact / Magazine pages | Hero photo above the copy |
| Editorial Team | Generated initial avatars (see note below) |
| Magazine covers | Generated brand artwork |

**Source and licence.** 96 photographs from **Wikimedia Commons**, all under
licences that permit commercial use (CC0, CC BY, CC BY-SA, GODL-India, FAL).
They were found by searching Commons for Indian business subjects — Mumbai,
Bengaluru, Delhi, Hyderabad, Chennai, Kolkata, Gurugram; container ports and
freight rail; coworking spaces and offices; conferences and trade fairs — then
reviewed by eye on contact sheets and curated into five themes
(`city` 36, `industry` 23, `people` 16, `media` 12, `workspace` 9).

**Attribution is required by these licences and is rendered on the page**: under
every hero image and inside every in-content caption, linking back to the
Commons file page. Photographer, licence and source URL are also stored on each
attachment (`cbw_credit_artist`, `cbw_credit_license`, `cbw_credit_source`), and
the full list is in [IMAGE-CREDITS.md](IMAGE-CREDITS.md).

**What was deliberately excluded.** Photographs of identifiable public figures —
serving politicians and named officials, of which Commons has many — were left
out. Using a real, recognisable person as generic illustration for fictional
articles would misrepresent them. Ceremonial, military and handicraft imagery
was excluded as off-subject.

**Why the editors have lettered avatars, not photos.** The five editors are
fictional. Attaching a real photographed person's face to a made-up byline
would misrepresent that person, so they get brand-coloured initial tiles
instead. Replace them with real headshots once you have real staff.

## Languages

A dropdown in the top bar (globe icon) offers **English / हिंदी / मराठी**, each
with its native name and English name. The options are plain links, so arrow
keys, Escape and click-away all work, and it opens rightward when the top bar
wraps and puts the button near the left edge of a phone. The choice arrives as
`?lang=hi` and is remembered in a `cbw_lang` cookie for a year, so the visitor
stays in their language while browsing.

| Layer | How it is translated |
|---|---|
| Interface (152 strings) | Theme text domain — `wp-content/languages/themes/cbw-hi_IN.l10n.php` and `cbw-mr_IN.l10n.php` |
| Page titles, menu labels | `_cbw_title_hi` / `_cbw_title_mr` post meta |
| Standfirsts and descriptions | `_cbw_excerpt_hi` / `_cbw_excerpt_mr` post meta |
| Page and issue body copy | `_cbw_content_hi` / `_cbw_content_mr` post meta |
| Category names | `_cbw_name_hi` / `_cbw_name_mr` term meta |
| Site tagline | `cbw_tagline_hi` / `cbw_tagline_mr` options |

Everything is stored as ordinary meta, so an editor can adjust any translation
from wp-admin without touching code. The switching itself lives in
`inc/i18n.php`, which filters `locale`, `the_title`, `get_the_excerpt`,
`the_content`, `get_term` and `wp_setup_nav_menu_item`.

**Devanagari typography.** `body.lang-devanagari` swaps in Noto Sans Devanagari
and loosens line height — Devanagari needs more vertical room than Latin, and
the display faces used for English headings have no Devanagari glyphs.

**What is not translated.** The 128 demo articles keep their English titles and
bodies. Translating that volume of reported prose properly is a job for
translators, not a build step. The mechanism is already wired up: fill in
`_cbw_title_hi`, `_cbw_excerpt_hi` and `_cbw_content_hi` on any post and it will
appear in Hindi. Everything else on the site — navigation, all 47 pages, all 8
magazine issues, all category names and the entire interface — is fully
trilingual.

## Responsive behaviour

Verified at 320 / 375 / 414 / 768 / 1024 / 1440 px across six page types, and
again in Hindi and Marathi (Devanagari runs wider than Latin) —
`scrollWidth == clientWidth` everywhere, so no page scrolls sideways.

**Mobile header (≤900px).** Tapping MENU slides a panel in from the left over
a dimmed page: every section with its icon (each expands with `+`), then
search, the two calls to action and the social links. Focus moves into the
panel and stays there until it closes (✕, Escape, or tapping the page). Once
the masthead has scrolled away, the bar shows the GMS mark as well. Below
760px the masthead carries only the logo, and the slider's tabs become dots.

Below 560px the reading size drops to 16px and section headers, forms and the
newsletter band tighten up.

**Testing note.** Headless Chrome clamps its window to about 485px, so
`--window-size=375` silently renders at 485 and crops the screenshot — which
looks like a broken layout but is not. Real phone widths need CDP
`Emulation.setDeviceMetricsOverride`. Also avoid `captureBeyondViewport` for
full-page shots: it re-lays-out the page and leaves lazy-loaded images blank.

## Notes on content

Layout, navigation pattern and tone follow ciobusinessworld.com as the
reference. The articles, company names, people and quotes are original and
fictional — republishing that site's copy would be a copyright problem. Replace
the demo articles with your own from the WordPress admin; the structure,
templates and category wiring stay as they are.

Cover artwork is generated programmatically (PHP GD), so no third-party images
are bundled. Swap in photography by setting a featured image on any post.
