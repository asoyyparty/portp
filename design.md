# Design — Qois Abdul Qudus (Developer Portfolio)

Locked design system. Future Hallmark runs read this file first; pages defer
to it. Amend intentionally — the file is the rule.

<!-- Hallmark · pre-emit critique: P5 H5 E5 S5 R5 V5 -->

---

## Brief

Personal portfolio for Qois Abdul Qudus — Full-Stack Developer & IT Specialist.
Audience: technical recruiters, engineering leads, collaborators.
One job: showcase technical projects & systems, then connect via LinkedIn/GitHub.

---

## System

- **Genre** · editorial-minimal (Framer creator portfolio aesthetic)
- **Macrostructure** · Portfolio Grid (18) + Capabilities Services Grid
- **Theme** · Majd Framer inspired · vibe: "editorial minimal, warm oat & obsidian, bold typography"
- **Axes** · warm / contrast / editorial
- **Nav** · N5 Floating glass pill with dark/light theme switch
- **Footer** · Big Impact CTA ("Let's talk.") + Structured sitemap footer

---

## Palette rationale

Base paper is warm oat `#FAF7F3` in light mode with deep obsidian `#111111` ink, paired with an instant switch to deep obsidian `#0E0E11` and `#FAF7F3` ink in dark mode. Accent is deep charcoal/white pill buttons with subtle rose highlights (`#EB4D6D`). Borders use delicate hairline transparency `rgba(0,0,0,0.08)` / `rgba(255,255,255,0.08)`.

---

## Tokens (canonical · tokens.css is the source of truth)

```css
:root {
  /* Paper (dark mode baseline) */
  --color-paper:      oklch(11% 0.012 225);   /* deep cool near-black  */
  --color-paper-2:    oklch(15% 0.013 225);   /* card / panel surface  */
  --color-paper-3:    oklch(19% 0.014 225);   /* hover surface / input */

  /* Ink */
  --color-ink:        oklch(93% 0.008 220);   /* primary text          */
  --color-ink-2:      oklch(68% 0.010 220);   /* secondary / caption   */

  /* Neutrals (tinted cool) */
  --color-rule:       oklch(24% 0.012 225);   /* hairline dividers     */
  --color-rule-2:     oklch(20% 0.011 225);   /* subtle inner rules    */
  --color-muted:      oklch(48% 0.010 222);   /* de-emphasised text    */
  --color-neutral:    oklch(35% 0.011 223);   /* mid-grey equivalent   */

  /* Accent (electric cyan — signal only, never a fill block) */
  --color-accent:     oklch(78% 0.17  210);   /* links, active, border */
  --color-accent-ink: oklch(11% 0.012 225);   /* text on accent bg     */
  --color-focus:      oklch(80% 0.21  210);   /* focus ring, higher C  */

  /* Typography */
  --font-display: "JetBrains Mono", ui-monospace, monospace;
  --font-body:    "Geist", ui-sans-serif, system-ui, sans-serif;
  --font-mono:    "Geist Mono", ui-monospace, monospace;
  /* 2+1: display=wordmark+hero · body=prose+UI · mono=code */

  /* Type scale (1.25 major-third, 16 px root) */
  --text-xs:      0.64rem;
  --text-sm:      0.8rem;
  --text-base:    1rem;
  --text-md:      1.25rem;
  --text-lg:      1.5625rem;
  --text-xl:      1.953rem;
  --text-2xl:     2.441rem;
  --text-3xl:     3.052rem;
  --text-4xl:     3.815rem;
  --text-display: clamp(2.75rem, 5vw + 1rem, 5.25rem);

  /* Spacing (4-pt scale) */
  --space-3xs:  0.25rem;   /*   4 px */
  --space-2xs:  0.5rem;    /*   8 px */
  --space-xs:   0.75rem;   /*  12 px */
  --space-sm:   1rem;      /*  16 px */
  --space-md:   1.5rem;    /*  24 px */
  --space-lg:   2rem;      /*  32 px */
  --space-xl:   3rem;      /*  48 px */
  --space-2xl:  4rem;      /*  64 px */
  --space-3xl:  6rem;      /*  96 px */
  --space-4xl:  8rem;      /* 128 px */

  /* Motion */
  --ease-out:    cubic-bezier(0.16, 1, 0.3, 1);
  --ease-in:     cubic-bezier(0.7, 0, 0.84, 0);
  --ease-in-out: cubic-bezier(0.65, 0, 0.35, 1);
  --dur-micro:   120ms;
  --dur-short:   220ms;
  --dur-long:    420ms;

  /* Radii */
  --radius-card:  6px;
  --radius-pill:  999px;
  --radius-input: 4px;
  --radius-tag:   3px;
}
```

---

## Typography rules

- **Display / wordmark** · JetBrains Mono 500–600 · tracking -0.02em · roman, never italic
- **Hero headline (≤ 50 chars)** · JetBrains Mono 600 · --text-display · tracking -0.03em
- **Section heads h2** · Geist 700 · --text-2xl · tracking -0.015em
- **Body** · Geist 350 (dark) · --text-base · line-height 1.6 · max-width 65ch
- **Labels / tags / code** · Geist Mono 400 · --text-sm · tracking 0.04em
- Headings always roman. Italics only in body prose emphasis.
- No gradient text on headings. No font below --text-xs.

---

## CTA voice

- **Primary** · ghost border 1px solid var(--color-accent) · text var(--color-accent) · --radius-pill · padding var(--space-xs) var(--space-md) · hover: tinted fill oklch(78% 0.17 210 / 0.08)
- **Secondary** · text-only · var(--color-ink-2) · underline slide on hover
- No solid accent-fill buttons anywhere.

---

## Motion stance

- **scroll-reveal** · opacity + translateY(8px→0) on IntersectionObserver · --dur-long · --ease-out · stagger via --i CSS property
- **link-underline-slide** · background-size 0% 1px → 100% 1px · --dur-short · --ease-out
- **focus-ring** · :focus-visible 2px solid var(--color-focus) · outline-offset 3px · instant, no transition
- prefers-reduced-motion: reduce → all spatial motion ≤ 150ms opacity crossfade
- ≤ 3 primitives per page. No parallax. No infinite loops. No bounce.

---

## Layout

- Content max-width: 1280px centered
- Section padding: --space-3xl top/bottom · --space-lg sides (mobile: --space-xl / --space-md)
- Card grid: repeat(auto-fill, minmax(340px, 1fr)) · mobile: minmax(0, 1fr)
- overflow-x: clip on both html and body

---

## Component voice

### Nav — N5 Floating pill

- Fixed, centered, top --space-md
- background: var(--color-paper-2) · border: 1px solid var(--color-rule) · border-radius: var(--radius-pill) · backdrop-filter: blur(12px) saturate(1.4)
- Wordmark: JetBrains Mono 600 · --text-sm · small accent dot after name
- Links: Geist 400 · --text-sm · active = var(--color-accent) underline · hover = underline-slide
- Mobile: pill collapses to wordmark + 3 links max, no hamburger

### Project cards

- background: var(--color-paper-2) · border: 1px solid var(--color-rule) · border-radius: var(--radius-card)
- Hover: border → var(--color-accent) · translateY(-2px) · --dur-short · --ease-out
- Tag chips: Geist Mono · --text-xs · background: var(--color-paper-3) · border-radius: var(--radius-tag)
- No box-shadow glow on dark backgrounds

### Footer — Ft1 Minimal statement

- Single row: wordmark left · year center · social links right
- border-top: 1px solid var(--color-rule) · padding: var(--space-lg) 0
- All text var(--color-ink-2) · --text-sm

---

## Page sections (Portfolio Grid sequence)

1. **Hero / About** — Left-aligned display name, professional headline, bio, quick metrics strip (3+ Tahun IT, 11+ Proyek, AWS Certified), action CTAs, and live operational status.
2. **Featured Projects** — Filterable project grid (All 11 projects · Enterprise CBA · Web Apps · Mobile & Research) with active links, tech stack tags, and metadata.
3. **Work Experience** — Timeline of career history (PT CBA Chemical Industry, Dicoding SIB, Yayasan Al-Kahfi, PT Smartfren Telecom).
4. **Education** — Academic credentials (S1 Teknik Informatika, Universitas Bina Bangsa).
5. **Certifications & Licenses** — 10 official credentials with credential links/IDs (AWS Cloud Practitioner, Dicoding AI, Microsoft Fabric Data Science, etc.).
6. **Contact & Socials** — Direct card grid for LinkedIn, Email, WhatsApp (+62 851-9530-0828), GitHub, Telegram, Instagram, X, and Website.
7. **Footer** — Ft1 minimal statement with secondary navigation.

---

## Bans (hard — no exceptions)

- No purple-to-cyan / orange-to-pink / 3-stop gradients
- No #000000 / #ffffff — all tokens carry chroma ≥ 0.005
- No Inter, Roboto, Open Sans, Poppins, Lato as display or body
- No gradient text on any heading
- No fabricated metrics — real numbers or omit entirely
- No re-drawn browser frames / fake phone chrome
- No box-shadow glow on dark backgrounds
- No italic headings — roman everywhere
- Max 3 font families (display + body + mono = 3)
- No horizontal scroll

---

## Exports

tokens.css is the source of truth. For Tailwind v4 @theme, DTCG tokens.json,
or shadcn/ui CSS variables, ask "extend design.md with Tailwind exports" — Hallmark will append them.

---

Hallmark · macrostructure: Portfolio Grid · theme: custom
vibe: "precision engineering, cool, near-future dark"
paper: oklch(11% 0.012 225) · accent: oklch(78% 0.17 210)
display: JetBrains Mono · body: Geist · axes: dark / mono / cool
studied: no · context: inferred · v1.1.0
