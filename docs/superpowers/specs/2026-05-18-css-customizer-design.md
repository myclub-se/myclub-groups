# MyClub CSS Customizer — Design Spec

**Date:** 2026-05-18
**Status:** Approved

## Overview

A browser-based CSS customization tool for three MyClub WordPress plugins: `myclub-groups`, `myclub-sections`, and `myclub-booking`. Runs as three separate standalone HTML applications (opened directly in a browser, no server required). Targets WordPress developers who are not CSS experts — the tool must be approachable but expose full property control.

The end product of using the tool is a clean CSS override file containing only the properties the developer changed, ready to drop into a WordPress child theme or custom CSS field.

---

## Architecture

### File structure

```
myclub-customizer/
├── shared/
│   ├── engine.js      ← all UI logic: tabs, controls, live preview, CSS generator
│   └── theme.css      ← app chrome styles (dark UI)
├── myclub-groups.html
├── myclub-sections.html
└── myclub-booking.html
```

Each plugin HTML file is a thin shell:
- Loads `shared/engine.js` and `shared/theme.css` via relative path
- Declares a JS config object describing its blocks, properties, and HTML mockups
- Has no UI logic of its own

The `shared/engine.js` reads the config and renders the full app. Fixing a bug or adding a new control type is done once in the engine and immediately applies to all three apps.

The folder must be kept intact to work (relative imports). Distribute by zipping the whole `myclub-customizer/` folder.

---

## App Layout (per plugin)

### Top bar
- App name: "MyClub / CSS Customizer"
- Plugin badge (e.g. "myclub-groups") — differentiates the three files visually

### Block tabs
- One tab per block registered in the plugin
- Tabs are horizontally scrollable if they overflow
- Active tab drives the preview and controls below

### Live preview area
- Static HTML mockup of the active block, built with the correct CSS class structure
- Placeholder data: fake names, placeholder images, sample dates
- Preview is rendered inside an isolated `<div>` that loads the plugin's original CSS plus any override rules generated from the current control values
- Updates in real time as controls change

### Controls area
- Grouped into collapsible sections: **Colors**, **Typography**, **Spacing**, **Borders & Radius**, **Modal** (only shown for blocks that have a modal)
- Sections are collapsed by default except Colors (most commonly needed)
- Each control shows:
  - A human-readable label (e.g. "Name font size")
  - The CSS class it targets as a subtitle (e.g. `.leader-name`) — for developer transparency
  - The appropriate input widget (see Control Types below)
- Every meaningful property from the source SCSS is exposed — nothing hidden

### Generated CSS panel (always visible at bottom)
- Shows only properties that differ from the plugin's default values — no noise
- Syntax-highlighted for readability
- **Copy to clipboard** button
- **Download as `.css`** button — filename: `myclub-groups-custom.css` (or sections / booking)

---

## Control Types

| Property type | Widget |
|---|---|
| Color | Colour swatch + hex text input (click swatch opens native `<input type="color">`) |
| Size (px/rem) | Slider with live numeric readout + text input for manual entry |
| Font weight | Dropdown (400 Normal, 600 Semi-bold, 700 Bold) |
| Text alignment | Dropdown or button group (left / center / right) |
| Border style | Dropdown (none, solid, dashed, dotted) |
| Boolean (show/hide) | Toggle switch |

---

## CSS Generation

- Each control has a `defaultValue` in the block config
- Only controls whose current value differs from the default are emitted
- Output is grouped by CSS selector — multiple changed properties on the same selector are batched into one rule block
- Output uses the full qualified selector from the source SCSS (e.g. `.myclub-groups-leaders-list .leader-name`) so it overrides with correct specificity
- Comment header per block section: `/* myclub-groups — Leaders block overrides */`

---

## Block Coverage

### myclub-groups (8 blocks)
- **Calendar** — event colors (yellow/red/green/blue), button styles, modal background, modal border radius, fc-button colors
- **Club Calendar** — same as Calendar
- **Leaders** — card background, card border, name color/size/weight, role color/size, modal styles, show-more/less link styles
- **Members** — same as Leaders
- **News** — item background, image aspect ratio enforcement, link color, hover underline, "more news" link style
- **Club News** — same as News
- **Coming Games** — odd/even row background colors, text color, title/venue/date font sizes
- **Menu** — link color, hover background, submenu background/border, mobile hamburger color
- **Navigation** — icon size, label visibility, spacing
- **Title** — container background, name font size, info text size, label/value styles

### myclub-sections (5 blocks)
- **Calendar** — same properties as groups Calendar (different class prefix)
- **Club Calendar** — same
- **News** — same as groups News
- **Club News** — same
- **Coming Games** — same as groups Coming Games
- **Description** — container margin, text styles

### myclub-booking (1 block)
- **Calendar** — event title styles, fc-button colors, booking modal (form inputs, button, border radius, shadows), selected-slots panel (background, chip colors, book button color)

---

## Spec Self-Review

- No TBDs or placeholders remaining
- Architecture matches all feature descriptions
- Scope is focused: one tool, three plugin flavours, shared engine
- All block names verified against the actual SCSS source files
- CSS generation approach (diff from defaults) is unambiguous
