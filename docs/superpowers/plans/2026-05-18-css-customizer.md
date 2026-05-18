# MyClub CSS Customizer — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build three standalone HTML apps (`myclub-groups.html`, `myclub-sections.html`, `myclub-booking.html`) that let WordPress developers visually customise the CSS of MyClub plugin blocks and export a clean override `.css` file.

**Architecture:** Shared `engine.js` + `theme.css` in `shared/`; each plugin HTML file declares a `PLUGIN_CONFIG` object then loads the engine. The engine renders tabs (one per block), a live iframe preview, collapsible control sections (Colors / Typography / Spacing / Borders), and a generated-CSS output panel with Copy + Download buttons. Only values that differ from the plugin defaults are emitted.

**Tech Stack:** Vanilla HTML/CSS/JS — no build step, no dependencies. Opens directly in browser via `file://`.

---

## File map

```
~/dev/myclub/myclub-customizer/
├── shared/
│   ├── engine.js          ← all rendering + CSS generation logic
│   └── theme.css          ← dark-UI chrome styles
├── myclub-groups.html     ← PLUGIN_CONFIG for groups (10 blocks)
├── myclub-sections.html   ← PLUGIN_CONFIG for sections (6 blocks)
└── myclub-booking.html    ← PLUGIN_CONFIG for booking (1 block)
```

---

## Task 1 — Scaffold directory + HTML shell

**Files:**
- Create: `~/dev/myclub/myclub-customizer/shared/engine.js`
- Create: `~/dev/myclub/myclub-customizer/shared/theme.css`
- Create: `~/dev/myclub/myclub-customizer/myclub-groups.html`
- Create: `~/dev/myclub/myclub-customizer/myclub-sections.html`
- Create: `~/dev/myclub/myclub-customizer/myclub-booking.html`

- [ ] **Create directory structure**

```bash
mkdir -p ~/dev/myclub/myclub-customizer/shared
touch ~/dev/myclub/myclub-customizer/shared/engine.js
touch ~/dev/myclub/myclub-customizer/shared/theme.css
```

- [ ] **Write the HTML shell** — same structure for all three files; write `myclub-groups.html` first:

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MyClub Groups — CSS Customizer</title>
  <link rel="stylesheet" href="shared/theme.css">
</head>
<body>
  <div id="top-bar"></div>
  <div id="block-tabs"></div>
  <div id="preview-area"></div>
  <div id="controls-area"></div>
  <div id="css-output-panel"></div>

  <script>
    const PLUGIN_CONFIG = {
      pluginName: 'myclub-groups',
      outputFilename: 'myclub-groups-custom.css',
      originalCSS: '',
      blocks: []
    };
  </script>
  <script src="shared/engine.js"></script>
</body>
</html>
```

- [ ] **Copy the same shell** to `myclub-sections.html` and `myclub-booking.html`, changing `pluginName`, `outputFilename`, and `<title>` accordingly.

- [ ] **Verify** — open `myclub-groups.html` in browser via `file://`. You should see a blank dark page with no JS errors in the console.

- [ ] **Commit**

```bash
cd ~/dev/myclub/myclub-customizer
git init
git add .
git commit -m "feat: scaffold css customizer"
```

---

## Task 2 — shared/theme.css

**Files:**
- Write: `~/dev/myclub/myclub-customizer/shared/theme.css`

- [ ] **Write complete theme.css**

```css
*, *::before, *::after { box-sizing: border-box; }

body {
  margin: 0;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #0f172a;
  color: #e2e8f0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── Top bar ── */
#top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 20px;
  background: #020817;
  border-bottom: 1px solid #1e293b;
  flex-shrink: 0;
}
.app-logo { font-size: 0.9rem; font-weight: 700; color: #7dd3fc; letter-spacing: -0.02em; }
.logo-sep { color: #334155; }
.plugin-badge { font-size: 0.7rem; background: #1e3a5f; color: #7dd3fc; border-radius: 4px; padding: 3px 10px; font-weight: 600; }

/* ── Block tabs ── */
#block-tabs {
  display: flex;
  gap: 2px;
  padding: 10px 16px 0;
  background: #020817;
  border-bottom: 1px solid #1e293b;
  overflow-x: auto;
  flex-shrink: 0;
}
.block-tab {
  padding: 7px 16px;
  font-size: 0.75rem;
  font-weight: 500;
  color: #64748b;
  background: transparent;
  border: 1px solid transparent;
  border-bottom: none;
  border-radius: 6px 6px 0 0;
  cursor: pointer;
  white-space: nowrap;
  transition: color 0.15s;
}
.block-tab:hover:not(.active) { color: #94a3b8; }
.block-tab.active {
  background: #0f172a;
  color: #e2e8f0;
  border-color: #1e293b;
  border-bottom-color: #0f172a;
}

/* ── Preview ── */
#preview-area {
  background: #fff;
  flex-shrink: 0;
  border-bottom: 2px solid #1e293b;
  position: relative;
  min-height: 80px;
}
#preview-area::before {
  content: 'Live Preview';
  position: absolute;
  top: 6px; right: 10px;
  font-size: 0.65rem;
  color: #94a3b8;
  background: rgba(15,23,42,0.75);
  padding: 2px 7px;
  border-radius: 4px;
  z-index: 10;
  pointer-events: none;
}
#preview-frame { display: block; width: 100%; border: none; }

/* ── Controls ── */
#controls-area {
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 420px;
  overflow-y: auto;
}
.ctrl-section { border-radius: 8px; overflow: hidden; }
.sect-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 9px 14px;
  background: #1e293b;
  color: #94a3b8;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.15s;
}
.sect-header:hover { background: #263548; }
.sect-chevron { color: #475569; font-size: 0.65rem; }
.sect-body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  padding: 12px 14px;
  background: #0f172a;
  border: 1px solid #1e293b;
  border-top: none;
  border-radius: 0 0 6px 6px;
}

/* Control row */
.ctrl-row { display: flex; flex-direction: column; gap: 5px; }
.ctrl-label {
  font-size: 0.68rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: flex;
  flex-direction: column;
  gap: 1px;
}
.ctrl-target {
  font-size: 0.62rem;
  color: #334155;
  font-weight: 400;
  text-transform: none;
  letter-spacing: 0;
  font-family: monospace;
}
.ctrl-row.changed .ctrl-label { color: #7dd3fc; }

/* Color */
.color-wrap { display: flex; align-items: center; gap: 6px; }
.color-wrap input[type="color"] {
  width: 26px; height: 26px; padding: 1px;
  border: 1px solid #334155; border-radius: 4px;
  cursor: pointer; background: none; flex-shrink: 0;
}
.hex-text {
  background: #1e293b; border: 1px solid #334155; border-radius: 4px;
  padding: 4px 7px; font-size: 0.72rem; color: #cbd5e1;
  font-family: monospace; width: 86px;
}
.hex-text:focus { outline: none; border-color: #395B9E; }

/* Size */
.size-wrap { display: flex; align-items: center; gap: 6px; }
.size-wrap input[type="range"] {
  flex: 1; -webkit-appearance: none;
  height: 4px; background: #334155; border-radius: 2px; outline: none;
}
.size-wrap input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none; width: 14px; height: 14px;
  background: #395B9E; border-radius: 50%; cursor: pointer;
}
.size-text {
  background: #1e293b; border: 1px solid #334155; border-radius: 4px;
  padding: 4px 6px; font-size: 0.72rem; color: #cbd5e1;
  width: 54px; font-family: monospace; text-align: right;
}
.size-text:focus { outline: none; border-color: #395B9E; }

/* Select */
select {
  background: #1e293b; border: 1px solid #334155; border-radius: 4px;
  padding: 5px 7px; font-size: 0.72rem; color: #cbd5e1; width: 100%; cursor: pointer;
}
select:focus { outline: none; border-color: #395B9E; }

/* Toggle */
.toggle { display: flex; align-items: center; cursor: pointer; }
.toggle input[type="checkbox"] { display: none; }
.toggle-track {
  width: 36px; height: 20px; background: #334155;
  border-radius: 10px; position: relative; transition: background 0.2s;
}
.toggle-track::after {
  content: ''; position: absolute; top: 3px; left: 3px;
  width: 14px; height: 14px; background: #64748b;
  border-radius: 50%; transition: transform 0.2s, background 0.2s;
}
.toggle input:checked + .toggle-track { background: #395B9E; }
.toggle input:checked + .toggle-track::after { transform: translateX(16px); background: #fff; }

/* ── CSS output ── */
#css-output-panel { border-top: 2px solid #1e293b; padding: 14px 16px; flex-shrink: 0; }
.output-header {
  display: flex; align-items: center;
  justify-content: space-between; margin-bottom: 10px;
}
.output-title {
  font-size: 0.72rem; font-weight: 700; color: #94a3b8;
  text-transform: uppercase; letter-spacing: 0.06em;
}
.output-actions { display: flex; gap: 8px; }
.btn-copy, .btn-dl {
  padding: 5px 12px; font-size: 0.72rem; font-weight: 600;
  border: none; border-radius: 5px; cursor: pointer; transition: opacity 0.15s;
}
.btn-copy { background: #1e3a5f; color: #7dd3fc; }
.btn-dl { background: #395B9E; color: #fff; }
.btn-copy:hover, .btn-dl:hover { opacity: 0.85; }
.css-pre {
  background: #020817; border: 1px solid #1e293b; border-radius: 6px;
  padding: 12px 14px;
  font-family: 'Fira Code', 'Cascadia Code', 'Consolas', monospace;
  font-size: 0.72rem; color: #7dd3fc; line-height: 1.7;
  max-height: 200px; overflow-y: auto; white-space: pre; margin: 0;
}
```

- [ ] **Verify** — reload `myclub-groups.html`. The page should be dark with the correct font and layout structure visible.

- [ ] **Commit**

```bash
git add shared/theme.css && git commit -m "feat: add app chrome styles"
```

---

## Task 3 — shared/engine.js

**Files:**
- Write: `~/dev/myclub/myclub-customizer/shared/engine.js`

- [ ] **Write complete engine.js**

```javascript
(function () {
  'use strict';

  let activeIdx = 0;
  const overrides = {}; // { blockSlug: { controlId: value } } — only changed values

  // ── Init ──────────────────────────────────────────────────────────────────

  function init() {
    if (!window.PLUGIN_CONFIG) {
      document.body.innerHTML = '<p style="padding:2rem;color:#ef4444">Error: PLUGIN_CONFIG not defined.</p>';
      return;
    }
    renderTopBar();
    renderTabs();
    renderOutputPanel();
    switchBlock(0);
  }

  // ── Top bar ───────────────────────────────────────────────────────────────

  function renderTopBar() {
    document.getElementById('top-bar').innerHTML =
      `<span class="app-logo">MyClub <span class="logo-sep">/</span> CSS Customizer</span>` +
      `<span class="plugin-badge">${eh(PLUGIN_CONFIG.pluginName)}</span>`;
  }

  // ── Tabs ──────────────────────────────────────────────────────────────────

  function renderTabs() {
    const el = document.getElementById('block-tabs');
    el.innerHTML = PLUGIN_CONFIG.blocks
      .map((b, i) => `<button class="block-tab${i === 0 ? ' active' : ''}" data-i="${i}">${eh(b.name)}</button>`)
      .join('');
    el.addEventListener('click', e => {
      const btn = e.target.closest('.block-tab');
      if (btn) switchBlock(Number(btn.dataset.i));
    });
  }

  function switchBlock(i) {
    activeIdx = i;
    document.querySelectorAll('.block-tab').forEach((t, j) => t.classList.toggle('active', j === i));
    renderPreview();
    renderControls();
    updateOutput();
  }

  // ── Preview ───────────────────────────────────────────────────────────────

  function renderPreview() {
    const block = PLUGIN_CONFIG.blocks[activeIdx];
    const area = document.getElementById('preview-area');
    const old = document.getElementById('preview-frame');
    if (old) old.remove();

    const iframe = document.createElement('iframe');
    iframe.id = 'preview-frame';
    area.appendChild(iframe);

    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(`<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>body{margin:16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}</style>
<style id="pcss">${PLUGIN_CONFIG.originalCSS || ''}</style>
<style id="ovr"></style>
</head><body>${block.mockupHTML}</body></html>`);
    doc.close();

    const fit = () => {
      if (iframe.contentDocument && iframe.contentDocument.body)
        iframe.style.height = (iframe.contentDocument.body.scrollHeight + 32) + 'px';
    };
    iframe.addEventListener('load', fit);
    setTimeout(fit, 150);
  }

  function pushPreviewCSS() {
    const iframe = document.getElementById('preview-frame');
    if (!iframe || !iframe.contentDocument) return;
    const s = iframe.contentDocument.getElementById('ovr');
    if (s) s.textContent = buildCSS(activeIdx);
  }

  // ── Controls ──────────────────────────────────────────────────────────────

  function renderControls() {
    const block = PLUGIN_CONFIG.blocks[activeIdx];
    document.getElementById('controls-area').innerHTML = block.sections
      .map((sec, si) => sectionHTML(block.slug, sec, si === 0))
      .join('');
  }

  function sectionHTML(slug, sec, open) {
    return `<div class="ctrl-section">
  <button class="sect-header" onclick="toggleSect(this)">
    <span>${eh(sec.icon)} ${eh(sec.name)}</span>
    <span class="sect-chevron">${open ? '▲' : '▼'}</span>
  </button>
  <div class="sect-body" style="display:${open ? 'grid' : 'none'}">
    ${sec.controls.map(c => ctrlHTML(slug, c)).join('')}
  </div>
</div>`;
  }

  window.toggleSect = function (btn) {
    const body = btn.nextElementSibling;
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'grid';
    btn.querySelector('.sect-chevron').textContent = open ? '▼' : '▲';
  };

  function ctrlHTML(slug, c) {
    const val = cur(slug, c);
    const ch = isDiff(slug, c) ? ' changed' : '';
    let w = '';

    if (c.type === 'color') {
      w = `<div class="color-wrap">
  <input type="color" value="${safeHex(val)}"
    oninput="hColor('${es(slug)}','${es(c.id)}',this.value)"
    onchange="hColor('${es(slug)}','${es(c.id)}',this.value)">
  <input class="hex-text" type="text" value="${eh(val)}"
    onchange="hColor('${es(slug)}','${es(c.id)}',this.value)"
    oninput="syncSwatch(this)">
</div>`;
    } else if (c.type === 'size') {
      w = `<div class="size-wrap">
  <input type="range" min="${c.min ?? 0}" max="${c.max ?? 100}" step="${c.step ?? 1}" value="${parseFloat(val) || 0}"
    oninput="hSize('${es(slug)}','${es(c.id)}','${es(c.unit ?? 'px')}',this)">
  <input class="size-text" type="text" value="${eh(val)}"
    onchange="hChange('${es(slug)}','${es(c.id)}',this.value)">
</div>`;
    } else if (c.type === 'select') {
      const opts = (c.options || []).map(o =>
        `<option value="${eh(o.value)}"${o.value === val ? ' selected' : ''}>${eh(o.label)}</option>`).join('');
      w = `<select onchange="hChange('${es(slug)}','${es(c.id)}',this.value)">${opts}</select>`;
    } else if (c.type === 'toggle') {
      w = `<label class="toggle"><input type="checkbox" ${val === (c.onValue ?? 'block') ? 'checked' : ''}
    onchange="hChange('${es(slug)}','${es(c.id)}',this.checked?'${es(c.onValue??'block')}':'${es(c.offValue??'none')}')">
  <span class="toggle-track"></span></label>`;
    }

    return `<div class="ctrl-row${ch}" data-blk="${es(slug)}" data-cid="${es(c.id)}">
  <div class="ctrl-label">${eh(c.label)}<span class="ctrl-target">${eh(c.target)}</span></div>
  ${w}
</div>`;
  }

  // ── Change handlers ───────────────────────────────────────────────────────

  window.hChange = function (slug, id, value) {
    const block = PLUGIN_CONFIG.blocks.find(b => b.slug === slug);
    if (!block) return;
    const c = findCtrl(block, id);
    if (!c) return;
    if (!overrides[slug]) overrides[slug] = {};
    if (value === c.default) delete overrides[slug][id];
    else overrides[slug][id] = value;
    const row = document.querySelector(`[data-blk="${slug}"][data-cid="${id}"]`);
    if (row) row.classList.toggle('changed', value !== c.default);
    pushPreviewCSS();
    updateOutput();
  };

  window.hColor = function (slug, id, value) {
    const row = document.querySelector(`[data-blk="${slug}"][data-cid="${id}"]`);
    if (row) { const t = row.querySelector('.hex-text'); if (t) t.value = value; }
    hChange(slug, id, value);
  };

  window.hSize = function (slug, id, unit, slider) {
    const val = slider.value + unit;
    const t = slider.nextElementSibling; if (t) t.value = val;
    hChange(slug, id, val);
  };

  window.syncSwatch = function (input) {
    if (/^#[0-9a-fA-F]{6}$/.test(input.value)) {
      const sw = input.previousElementSibling; if (sw) sw.value = input.value;
    }
  };

  // ── CSS generation ────────────────────────────────────────────────────────

  function buildCSS(blockIdx) {
    const block = PLUGIN_CONFIG.blocks[blockIdx];
    const bov = overrides[block.slug] || {};
    const bySel = {};

    block.sections.forEach(sec => sec.controls.forEach(c => {
      let v = bov[c.id]; if (v === undefined) return;
      if (c.important) v += ' !important';
      (bySel[c.target] = bySel[c.target] || []).push({ p: c.property, v });
    }));

    if (!Object.keys(bySel).length) return '';
    return [`/* ${PLUGIN_CONFIG.pluginName} — ${block.name} */`]
      .concat(Object.entries(bySel).map(([sel, ps]) =>
        `${sel} {\n${ps.map(({ p, v }) => `  ${p}: ${v};`).join('\n')}\n}`
      )).join('\n');
  }

  function buildAll() {
    return PLUGIN_CONFIG.blocks.map((_, i) => buildCSS(i)).filter(Boolean).join('\n\n');
  }

  // ── Output panel ─────────────────────────────────────────────────────────

  function renderOutputPanel() {
    document.getElementById('css-output-panel').innerHTML = `
<div class="output-header">
  <span class="output-title">Generated CSS Override</span>
  <div class="output-actions">
    <button class="btn-copy" onclick="copyCSS()">📋 Copy</button>
    <button class="btn-dl" onclick="dlCSS()">⬇ Download .css</button>
  </div>
</div>
<pre class="css-pre" id="css-pre">(no changes yet)</pre>`;
  }

  function updateOutput() {
    const el = document.getElementById('css-pre'); if (!el) return;
    el.textContent = buildAll() || '(no changes yet — modify controls above)';
  }

  window.copyCSS = function () {
    const css = buildAll(); if (!css) return;
    navigator.clipboard.writeText(css).then(() => {
      const btn = document.querySelector('.btn-copy');
      btn.textContent = '✓ Copied!';
      setTimeout(() => (btn.textContent = '📋 Copy'), 2000);
    });
  };

  window.dlCSS = function () {
    const css = buildAll(); if (!css) return;
    const a = Object.assign(document.createElement('a'), {
      href: URL.createObjectURL(new Blob([css], { type: 'text/css' })),
      download: PLUGIN_CONFIG.outputFilename,
    });
    a.click(); URL.revokeObjectURL(a.href);
  };

  // ── Helpers ───────────────────────────────────────────────────────────────

  function cur(slug, c) { return overrides[slug]?.[c.id] ?? c.default; }
  function isDiff(slug, c) { return overrides[slug]?.[c.id] !== undefined && overrides[slug][c.id] !== c.default; }
  function findCtrl(block, id) {
    for (const s of block.sections) { const c = s.controls.find(c => c.id === id); if (c) return c; }
    return null;
  }
  function safeHex(v) { return /^#[0-9a-fA-F]{6}$/.test(v) ? v : '#000000'; }
  function eh(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
  function es(s) { return String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }

  document.addEventListener('DOMContentLoaded', init);
})();
```

- [ ] **Verify** — open `myclub-groups.html`. With `blocks: []` you should see "MyClub / CSS Customizer" in the top bar, no JS errors.

- [ ] **Commit**

```bash
git add shared/engine.js && git commit -m "feat: add shared engine"
```

---

## Task 4 — myclub-groups.html — full PLUGIN_CONFIG

**Files:**
- Write: `~/dev/myclub/myclub-customizer/myclub-groups.html`

Replace the `<script>` block (keep the HTML shell from Task 1). The `originalCSS` value is the concatenation of all built CSS files. Paste the content of each path listed after the property name.

Read and concatenate these files (in order) and assign as a template-literal string to `originalCSS`:
- `/home/anku/dev/myclub/myclub-groups/blocks/build/calendar/style-index.css`
- `/home/anku/dev/myclub/myclub-groups/blocks/build/club-calendar/style-index.css`
- `/home/anku/dev/myclub/myclub-groups/blocks/build/leaders/style-index.css`
- `/home/anku/dev/myclub/myclub-groups/blocks/build/members/style-index.css`
- `/home/anku/dev/myclub/myclub-groups/blocks/build/news/style-index.css`
- `/home/anku/dev/myclub/myclub-groups/blocks/build/club-news/style-index.css`
- `/home/anku/dev/myclub/myclub-groups/blocks/build/coming-games/style-index.css`
- `/home/anku/dev/myclub/myclub-groups/blocks/build/menu/style-index.css`
- `/home/anku/dev/myclub/myclub-groups/blocks/build/navigation/style-index.css`
- `/home/anku/dev/myclub/myclub-groups/blocks/build/title/style-index.css`

- [ ] **Write the PLUGIN_CONFIG** (replace the script block in myclub-groups.html):

```javascript
const PLUGIN_CONFIG = {
  pluginName: 'myclub-groups',
  outputFilename: 'myclub-groups-custom.css',
  originalCSS: `/* paste concatenated CSS here */`,
  blocks: [

    // ── Calendar ──────────────────────────────────────────────────────────
    {
      name: 'Calendar', slug: 'calendar',
      mockupHTML: `
        <div class="myclub-groups-calendar">
          <div class="myclub-groups-calendar-container">
            <div style="border:1px solid #e2e8f0;border-radius:4px;overflow:hidden;">
              <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-bottom:1px solid #e2e8f0;">
                <div style="display:flex;gap:4px;">
                  <button class="fc-button fc-button-primary" style="padding:4px 10px;border-radius:4px;cursor:pointer;font-size:13px;">‹</button>
                  <button class="fc-button fc-button-primary" style="padding:4px 10px;border-radius:4px;cursor:pointer;font-size:13px;">›</button>
                </div>
                <strong style="font-size:14px;">May 2026</strong>
                <button class="fc-button fc-button-primary fc-button-active" style="padding:4px 10px;border-radius:4px;cursor:pointer;font-size:13px;">Month</button>
              </div>
              <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:1px;background:#e2e8f0;padding:1px;">
                ${['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].map(d=>`<div style="background:#f8fafc;text-align:center;font-size:11px;padding:4px;color:#64748b;">${d}</div>`).join('')}
                ${Array.from({length:28},(_,i)=>{
                  const day=i+1;
                  const color=day===7?'yellow':day===14?'red':day===21?'green':day===28?'blue':'';
                  return `<div style="background:#fff;min-height:54px;padding:4px;">
                    <div style="font-size:11px;color:#1e293b;">${day}</div>
                    ${color?`<div class="fc-event-title ${color}" style="font-size:11px;padding:2px 4px;border-radius:3px;margin-top:2px;">Training</div>`:''}
                  </div>`;
                }).join('')}
              </div>
            </div>
            <div class="myclub-groups-subscribe-button-wrapper">
              <button class="myclub-groups-subscribe-button">Subscribe to calendar</button>
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'cal-yellow', label: 'Event: Yellow bg', target: '.myclub-groups-calendar .fc .fc-event-title.yellow', property: 'background-color', type: 'color', default: '#9e8c39' },
          { id: 'cal-red', label: 'Event: Red bg', target: '.myclub-groups-calendar .fc .fc-event-title.red', property: 'background-color', type: 'color', default: '#c1272d' },
          { id: 'cal-green', label: 'Event: Green bg', target: '.myclub-groups-calendar .fc .fc-event-title.green', property: 'background-color', type: 'color', default: '#009245' },
          { id: 'cal-blue', label: 'Event: Blue bg', target: '.myclub-groups-calendar .fc .fc-event-title.blue', property: 'background-color', type: 'color', default: '#396b9e' },
          { id: 'cal-event-text', label: 'Event text color', target: '.myclub-groups-calendar .fc .fc-event-title', property: 'color', type: 'color', default: '#ffffff' },
          { id: 'cal-btn-active-bg', label: 'Active button bg', target: '.myclub-groups-calendar .fc .fc-button-primary:not(:disabled).fc-button-active', property: 'background-color', type: 'color', default: '#1a252f', important: true },
          { id: 'cal-btn-active-color', label: 'Active button text', target: '.myclub-groups-calendar .fc .fc-button-primary:not(:disabled).fc-button-active', property: 'color', type: 'color', default: '#ffffff', important: true },
          { id: 'cal-sub-color', label: 'Subscribe text color', target: '.myclub-groups-calendar .myclub-groups-subscribe-button', property: 'color', type: 'color', default: '#21201e' },
          { id: 'cal-sub-border', label: 'Subscribe border color', target: '.myclub-groups-calendar .myclub-groups-subscribe-button', property: 'border-color', type: 'color', default: '#21201e' },
          { id: 'cal-sub-hover-bg', label: 'Subscribe hover bg', target: '.myclub-groups-calendar .myclub-groups-subscribe-button:hover', property: 'background-color', type: 'color', default: '#21201e' },
          { id: 'cal-modal-bg', label: 'Modal background', target: '.myclub-groups-calendar .calendar-modal .modal-content', property: 'background-color', type: 'color', default: '#ffffff' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'cal-event-fs', label: 'Event font size', target: '.myclub-groups-calendar .fc .fc-event-title', property: 'font-size', type: 'size', default: '0.8rem', unit: 'rem', min: 0.5, max: 2, step: 0.05 },
          { id: 'cal-sub-fs', label: 'Subscribe btn font size', target: '.myclub-groups-calendar .myclub-groups-subscribe-button', property: 'font-size', type: 'size', default: '0.9rem', unit: 'rem', min: 0.5, max: 2, step: 0.05 },
          { id: 'cal-modal-name-fs', label: 'Modal event name size', target: '.myclub-groups-calendar .calendar-modal .modal-content .modal-body .name', property: 'font-size', type: 'select', default: 'larger', options: [{label:'smaller',value:'smaller'},{label:'medium',value:'medium'},{label:'larger',value:'larger'},{label:'large',value:'large'},{label:'x-large',value:'x-large'}] },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'cal-mb', label: 'Block bottom margin', target: '.myclub-groups-calendar', property: 'margin-bottom', type: 'size', default: '2rem', unit: 'rem', min: 0, max: 6, step: 0.25 },
        ]},
        { name: 'Borders & Radius', icon: '▭', controls: [
          { id: 'cal-modal-radius', label: 'Modal border radius', target: '.myclub-groups-calendar .calendar-modal .modal-content', property: 'border-radius', type: 'size', default: '8px', unit: 'px', min: 0, max: 30, step: 1 },
          { id: 'cal-event-radius', label: 'Event chip radius', target: '.myclub-groups-calendar .fc .fc-list-event-title .fc-event-title', property: 'border-radius', type: 'size', default: '6px', unit: 'px', min: 0, max: 20, step: 1 },
          { id: 'cal-sub-radius', label: 'Subscribe btn radius', target: '.myclub-groups-calendar .myclub-groups-subscribe-button', property: 'border-radius', type: 'size', default: '4px', unit: 'px', min: 0, max: 20, step: 1 },
        ]},
      ]
    },

    // ── Club Calendar ──────────────────────────────────────────────────────
    {
      name: 'Club Calendar', slug: 'club-calendar',
      mockupHTML: `
        <div class="myclub-groups-club-calendar">
          <div class="myclub-groups-club-calendar-container">
            <div style="border:1px solid #e2e8f0;border-radius:4px;overflow:hidden;">
              <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-bottom:1px solid #e2e8f0;">
                <div style="display:flex;gap:4px;">
                  <button class="fc-button fc-button-primary" style="padding:4px 10px;border-radius:4px;font-size:13px;">‹</button>
                  <button class="fc-button fc-button-primary" style="padding:4px 10px;border-radius:4px;font-size:13px;">›</button>
                </div>
                <strong style="font-size:14px;">May 2026</strong>
                <button class="fc-button fc-button-primary fc-button-active" style="padding:4px 10px;border-radius:4px;font-size:13px;">Month</button>
              </div>
              <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:1px;background:#e2e8f0;padding:1px;">
                ${['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].map(d=>`<div style="background:#f8fafc;text-align:center;font-size:11px;padding:4px;color:#64748b;">${d}</div>`).join('')}
                ${Array.from({length:28},(_,i)=>{const day=i+1;const color=day===5?'yellow':day===15?'blue':'';return `<div style="background:#fff;min-height:54px;padding:4px;"><div style="font-size:11px;">${day}</div>${color?`<div class="fc-event-title ${color}" style="font-size:11px;padding:2px 4px;border-radius:3px;margin-top:2px;">Club event</div>`:''}</div>`;}).join('')}
              </div>
            </div>
            <div class="myclub-groups-subscribe-button-wrapper">
              <button class="myclub-groups-subscribe-button">Subscribe to calendar</button>
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'ccal-yellow', label: 'Event: Yellow bg', target: '.myclub-groups-club-calendar .fc .fc-event-title.yellow', property: 'background-color', type: 'color', default: '#9e8c39' },
          { id: 'ccal-red', label: 'Event: Red bg', target: '.myclub-groups-club-calendar .fc .fc-event-title.red', property: 'background-color', type: 'color', default: '#c1272d' },
          { id: 'ccal-green', label: 'Event: Green bg', target: '.myclub-groups-club-calendar .fc .fc-event-title.green', property: 'background-color', type: 'color', default: '#009245' },
          { id: 'ccal-blue', label: 'Event: Blue bg', target: '.myclub-groups-club-calendar .fc .fc-event-title.blue', property: 'background-color', type: 'color', default: '#396b9e' },
          { id: 'ccal-event-text', label: 'Event text color', target: '.myclub-groups-club-calendar .fc .fc-event-title', property: 'color', type: 'color', default: '#ffffff' },
          { id: 'ccal-btn-active-bg', label: 'Active button bg', target: '.myclub-groups-club-calendar .fc .fc-button-primary:not(:disabled).fc-button-active', property: 'background-color', type: 'color', default: '#1a252f', important: true },
          { id: 'ccal-sub-color', label: 'Subscribe text color', target: '.myclub-groups-club-calendar .myclub-groups-subscribe-button', property: 'color', type: 'color', default: '#21201e' },
          { id: 'ccal-modal-bg', label: 'Modal background', target: '.myclub-groups-club-calendar .club-calendar-modal .modal-content', property: 'background-color', type: 'color', default: '#ffffff' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'ccal-event-fs', label: 'Event font size', target: '.myclub-groups-club-calendar .fc .fc-event-title', property: 'font-size', type: 'size', default: '0.8rem', unit: 'rem', min: 0.5, max: 2, step: 0.05 },
        ]},
        { name: 'Borders & Radius', icon: '▭', controls: [
          { id: 'ccal-modal-radius', label: 'Modal border radius', target: '.myclub-groups-club-calendar .club-calendar-modal .modal-content', property: 'border-radius', type: 'size', default: '8px', unit: 'px', min: 0, max: 30, step: 1 },
        ]},
      ]
    },

    // ── Leaders ───────────────────────────────────────────────────────────
    {
      name: 'Leaders', slug: 'leaders',
      mockupHTML: `
        <div class="myclub-groups-leaders-list">
          <div class="myclub-groups-leaders-container">
            <div class="leaders-list">
              ${['Anna Lindqvist','Erik Karlsson','Maria Svensson','Lars Berg'].map((n,i)=>`
              <div class="leader">
                <div class="leader-picture"><img src="https://picsum.photos/seed/${i+10}/200/200" alt="" style="width:100%;height:auto;border-radius:4px;"></div>
                <div class="leader-name">${n}</div>
                <div class="leader-role">${['Head Coach','Assistant','Manager','Physio'][i]}</div>
              </div>`).join('')}
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'ldr-name-color', label: 'Name color', target: '.myclub-groups-leaders-list .leader .leader-name', property: 'color', type: 'color', default: '#000000' },
          { id: 'ldr-role-color', label: 'Role color', target: '.myclub-groups-leaders-list .leader .leader-role', property: 'color', type: 'color', default: '#000000' },
          { id: 'ldr-card-bg', label: 'Card background', target: '.myclub-groups-leaders-list .leader', property: 'background-color', type: 'color', default: 'transparent' },
          { id: 'ldr-modal-bg', label: 'Modal background', target: '.myclub-groups-leaders-list .leader-modal .modal-content', property: 'background-color', type: 'color', default: '#ffffff' },
          { id: 'ldr-show-more-color', label: 'Show more color', target: '.myclub-groups-leaders-list .leader-show-more', property: 'color', type: 'color', default: '#000000' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'ldr-name-fs', label: 'Name font size', target: '.myclub-groups-leaders-list .leader .leader-name', property: 'font-size', type: 'size', default: '1rem', unit: 'rem', min: 0.6, max: 2.5, step: 0.05 },
          { id: 'ldr-name-fw', label: 'Name font weight', target: '.myclub-groups-leaders-list .leader .leader-name', property: 'font-weight', type: 'select', default: 'normal', options: [{label:'Normal (400)',value:'400'},{label:'Medium (500)',value:'500'},{label:'Semi-bold (600)',value:'600'},{label:'Bold (700)',value:'700'}] },
          { id: 'ldr-name-align', label: 'Name alignment', target: '.myclub-groups-leaders-list .leader .leader-name', property: 'text-align', type: 'select', default: 'center', options: [{label:'Left',value:'left'},{label:'Center',value:'center'},{label:'Right',value:'right'}] },
          { id: 'ldr-role-fs', label: 'Role font size', target: '.myclub-groups-leaders-list .leader .leader-role', property: 'font-size', type: 'select', default: 'smaller', options: [{label:'x-small',value:'x-small'},{label:'smaller',value:'smaller'},{label:'small',value:'small'},{label:'0.85rem',value:'0.85rem'},{label:'1rem',value:'1rem'}] },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'ldr-card-padding', label: 'Card padding', target: '.myclub-groups-leaders-list .leader', property: 'padding', type: 'size', default: '0.5rem', unit: 'rem', min: 0, max: 3, step: 0.25 },
          { id: 'ldr-mb', label: 'Block bottom margin', target: '.myclub-groups-leaders-list', property: 'margin-bottom', type: 'size', default: '2rem', unit: 'rem', min: 0, max: 6, step: 0.25 },
        ]},
        { name: 'Borders & Radius', icon: '▭', controls: [
          { id: 'ldr-card-radius', label: 'Card border radius', target: '.myclub-groups-leaders-list .leader', property: 'border-radius', type: 'size', default: '0px', unit: 'px', min: 0, max: 24, step: 1 },
          { id: 'ldr-modal-radius', label: 'Modal border radius', target: '.myclub-groups-leaders-list .leader-modal .modal-content', property: 'border-radius', type: 'size', default: '8px', unit: 'px', min: 0, max: 30, step: 1 },
        ]},
      ]
    },

    // ── Members ───────────────────────────────────────────────────────────
    {
      name: 'Members', slug: 'members',
      mockupHTML: `
        <div class="myclub-groups-members-list">
          <div class="myclub-groups-members-container">
            <div class="members-list">
              ${['Sofia Berg','Johan Ek','Klara Lund','Oskar Holm'].map((n,i)=>`
              <div class="member">
                <div class="member-picture"><img src="https://picsum.photos/seed/${i+20}/200/200" alt="" style="width:100%;height:auto;border-radius:4px;"></div>
                <div class="member-name">${n}</div>
                <div class="member-role">${['Forward','Midfielder','Defender','Goalkeeper'][i]}</div>
              </div>`).join('')}
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'mbr-name-color', label: 'Name color', target: '.myclub-groups-members-list .member .member-name', property: 'color', type: 'color', default: '#000000' },
          { id: 'mbr-role-color', label: 'Role color', target: '.myclub-groups-members-list .member .member-role', property: 'color', type: 'color', default: '#000000' },
          { id: 'mbr-card-bg', label: 'Card background', target: '.myclub-groups-members-list .member', property: 'background-color', type: 'color', default: 'transparent' },
          { id: 'mbr-modal-bg', label: 'Modal background', target: '.myclub-groups-members-list .member-modal .modal-content', property: 'background-color', type: 'color', default: '#ffffff' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'mbr-name-fs', label: 'Name font size', target: '.myclub-groups-members-list .member .member-name', property: 'font-size', type: 'size', default: '1rem', unit: 'rem', min: 0.6, max: 2.5, step: 0.05 },
          { id: 'mbr-name-fw', label: 'Name font weight', target: '.myclub-groups-members-list .member .member-name', property: 'font-weight', type: 'select', default: 'normal', options: [{label:'Normal (400)',value:'400'},{label:'Semi-bold (600)',value:'600'},{label:'Bold (700)',value:'700'}] },
          { id: 'mbr-name-align', label: 'Name alignment', target: '.myclub-groups-members-list .member .member-name', property: 'text-align', type: 'select', default: 'center', options: [{label:'Left',value:'left'},{label:'Center',value:'center'},{label:'Right',value:'right'}] },
          { id: 'mbr-role-fs', label: 'Role font size', target: '.myclub-groups-members-list .member .member-role', property: 'font-size', type: 'select', default: 'smaller', options: [{label:'x-small',value:'x-small'},{label:'smaller',value:'smaller'},{label:'small',value:'small'},{label:'1rem',value:'1rem'}] },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'mbr-card-padding', label: 'Card padding', target: '.myclub-groups-members-list .member', property: 'padding', type: 'size', default: '0.5rem', unit: 'rem', min: 0, max: 3, step: 0.25 },
          { id: 'mbr-mb', label: 'Block bottom margin', target: '.myclub-groups-members-list', property: 'margin-bottom', type: 'size', default: '2rem', unit: 'rem', min: 0, max: 6, step: 0.25 },
        ]},
        { name: 'Borders & Radius', icon: '▭', controls: [
          { id: 'mbr-card-radius', label: 'Card border radius', target: '.myclub-groups-members-list .member', property: 'border-radius', type: 'size', default: '0px', unit: 'px', min: 0, max: 24, step: 1 },
          { id: 'mbr-modal-radius', label: 'Modal border radius', target: '.myclub-groups-members-list .member-modal .modal-content', property: 'border-radius', type: 'size', default: '8px', unit: 'px', min: 0, max: 30, step: 1 },
        ]},
      ]
    },

    // ── News ──────────────────────────────────────────────────────────────
    {
      name: 'News', slug: 'news',
      mockupHTML: `
        <div class="myclub-groups-news">
          <div class="myclub-groups-news-container">
            <div class="myclub-groups-news-list">
              ${[1,2,3].map(i=>`
              <div class="myclub-news-item" style="flex:0 1 calc(33% - 1rem)">
                <a href="#" onclick="return false">
                  <div class="myclub-news-image"><img src="https://picsum.photos/seed/${i+30}/400/225" alt="" style="width:100%;height:100%;object-fit:cover;display:block;"></div>
                  <div class="myclub-news-image-caption">Caption text</div>
                  <div style="font-size:0.9rem;margin-top:0.5rem;font-weight:600;">News headline ${i}</div>
                </a>
              </div>`).join('')}
              <div class="myclub-more-news"><a href="#" onclick="return false">Show more news</a></div>
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'news-item-bg', label: 'Item background', target: '.myclub-groups-news .myclub-news-item', property: 'background-color', type: 'color', default: 'transparent' },
          { id: 'news-link-color', label: 'Link color', target: '.myclub-groups-news .myclub-news-item a', property: 'color', type: 'color', default: '#000000' },
          { id: 'news-img-bg', label: 'Image placeholder bg', target: '.myclub-groups-news .myclub-news-image', property: 'background-color', type: 'color', default: '#f2f2f2' },
          { id: 'news-caption-color', label: 'Caption color', target: '.myclub-groups-news .myclub-news-image-caption', property: 'color', type: 'color', default: '#000000' },
          { id: 'news-more-color', label: '"More news" link color', target: '.myclub-groups-news .myclub-more-news', property: 'color', type: 'color', default: '#000000' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'news-caption-fs', label: 'Caption font size', target: '.myclub-groups-news .myclub-news-image-caption', property: 'font-size', type: 'select', default: 'small', options: [{label:'x-small',value:'x-small'},{label:'small',value:'small'},{label:'0.85rem',value:'0.85rem'},{label:'1rem',value:'1rem'}] },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'news-item-padding', label: 'Item padding', target: '.myclub-groups-news .myclub-news-item', property: 'padding', type: 'size', default: '0.5rem', unit: 'rem', min: 0, max: 3, step: 0.25 },
          { id: 'news-mb', label: 'Block bottom margin', target: '.myclub-groups-news', property: 'margin-bottom', type: 'size', default: '2rem', unit: 'rem', min: 0, max: 6, step: 0.25 },
        ]},
        { name: 'Borders & Radius', icon: '▭', controls: [
          { id: 'news-img-radius', label: 'Image border radius', target: '.myclub-groups-news .myclub-news-image', property: 'border-radius', type: 'size', default: '0px', unit: 'px', min: 0, max: 20, step: 1 },
          { id: 'news-item-radius', label: 'Item border radius', target: '.myclub-groups-news .myclub-news-item', property: 'border-radius', type: 'size', default: '0px', unit: 'px', min: 0, max: 20, step: 1 },
        ]},
      ]
    },

    // ── Club News ─────────────────────────────────────────────────────────
    {
      name: 'Club News', slug: 'club-news',
      mockupHTML: `
        <div class="myclub-groups-club-news">
          <div class="myclub-groups-club-news-container">
            <div class="myclub-groups-club-news-list">
              ${[1,2,3].map(i=>`
              <div class="myclub-club-news-item" style="flex:0 1 calc(33% - 1rem)">
                <a href="#" onclick="return false">
                  <div class="myclub-club-news-image"><img src="https://picsum.photos/seed/${i+40}/400/225" alt="" style="width:100%;height:100%;object-fit:cover;display:block;"></div>
                  <div class="myclub-club-news-image-caption">Caption text</div>
                  <div style="font-size:0.9rem;margin-top:0.5rem;font-weight:600;">Club news headline ${i}</div>
                </a>
              </div>`).join('')}
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'cnews-item-bg', label: 'Item background', target: '.myclub-groups-club-news .myclub-club-news-item', property: 'background-color', type: 'color', default: 'transparent' },
          { id: 'cnews-link-color', label: 'Link color', target: '.myclub-groups-club-news .myclub-club-news-item a', property: 'color', type: 'color', default: '#000000' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'cnews-caption-fs', label: 'Caption font size', target: '.myclub-groups-club-news .myclub-club-news-image-caption', property: 'font-size', type: 'select', default: 'small', options: [{label:'x-small',value:'x-small'},{label:'small',value:'small'},{label:'1rem',value:'1rem'}] },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'cnews-mb', label: 'Block bottom margin', target: '.myclub-groups-club-news', property: 'margin-bottom', type: 'size', default: '2rem', unit: 'rem', min: 0, max: 6, step: 0.25 },
        ]},
        { name: 'Borders & Radius', icon: '▭', controls: [
          { id: 'cnews-img-radius', label: 'Image border radius', target: '.myclub-groups-club-news .myclub-club-news-image', property: 'border-radius', type: 'size', default: '0px', unit: 'px', min: 0, max: 20, step: 1 },
        ]},
      ]
    },

    // ── Coming Games ──────────────────────────────────────────────────────
    {
      name: 'Coming Games', slug: 'coming-games',
      mockupHTML: `
        <div class="myclub-groups-coming-games">
          <div class="myclub-groups-coming-games-container">
            <div class="coming-games-list">
              ${[
                {title:'Team A vs Team B',group:'Senior Men',venue:'City Stadium',date:'2026-06-01',time:'15:00'},
                {title:'Team C vs Team D',group:'U19',venue:'North Field',date:'2026-06-03',time:'13:00'},
                {title:'Team E vs Team F',group:'Women',venue:'South Arena',date:'2026-06-05',time:'17:00'},
              ].map(g=>`
              <div class="myclub-groups-coming-game">
                <div class="title"><div>${g.title}</div><div class="group-name">${g.group}</div></div>
                <div class="venue">${g.venue}</div>
                <div class="date">${g.date}<div class="time">${g.time}</div></div>
              </div>`).join('')}
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'cg-odd-bg', label: 'Odd row background', target: '.myclub-groups-coming-games .myclub-groups-coming-game:nth-child(odd)', property: 'background-color', type: 'color', default: '#53565a' },
          { id: 'cg-even-bg', label: 'Even row background', target: '.myclub-groups-coming-games .myclub-groups-coming-game:nth-child(even)', property: 'background-color', type: 'color', default: '#9c9ea0' },
          { id: 'cg-text-color', label: 'Text color', target: '.myclub-groups-coming-games .myclub-groups-coming-game', property: 'color', type: 'color', default: '#ffffff' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'cg-group-fs', label: 'Group name font size', target: '.myclub-groups-coming-games .myclub-groups-coming-game .title .group-name', property: 'font-size', type: 'select', default: 'smaller', options: [{label:'x-small',value:'x-small'},{label:'smaller',value:'smaller'},{label:'small',value:'small'},{label:'1rem',value:'1rem'}] },
          { id: 'cg-time-fs', label: 'Time font size', target: '.myclub-groups-coming-games .myclub-groups-coming-game .date .time', property: 'font-size', type: 'select', default: 'smaller', options: [{label:'x-small',value:'x-small'},{label:'smaller',value:'smaller'},{label:'small',value:'small'},{label:'1rem',value:'1rem'}] },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'cg-row-padding', label: 'Row padding', target: '.myclub-groups-coming-games .myclub-groups-coming-game', property: 'padding', type: 'size', default: '1rem', unit: 'rem', min: 0, max: 3, step: 0.25 },
          { id: 'cg-mb', label: 'Block bottom margin', target: '.myclub-groups-coming-games', property: 'margin-bottom', type: 'size', default: '2rem', unit: 'rem', min: 0, max: 6, step: 0.25 },
        ]},
      ]
    },

    // ── Menu ──────────────────────────────────────────────────────────────
    {
      name: 'Menu', slug: 'menu',
      mockupHTML: `
        <div class="myclub-groups-menu">
          <div class="myclub-groups-menu-container" style="max-width:600px;">
            <ul class="menu" style="display:flex;flex-direction:row;flex-wrap:wrap;list-style:none;margin:0;padding:0;">
              <li><a href="#" onclick="return false">Home</a></li>
              <li><a href="#" onclick="return false">About</a></li>
              <li><a href="#" onclick="return false">Teams</a></li>
              <li><a href="#" onclick="return false">News</a></li>
              <li><a href="#" onclick="return false">Contact</a></li>
            </ul>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'menu-link-color', label: 'Link color', target: '.myclub-groups-menu .myclub-groups-menu-container a', property: 'color', type: 'color', default: '#000000' },
          { id: 'menu-hover-bg', label: 'Item hover background', target: '.myclub-groups-menu .myclub-groups-menu-container ul.menu > li.menu-item-has-children:hover', property: 'background-color', type: 'color', default: '#dcdcdc' },
          { id: 'menu-sub-bg', label: 'Submenu background', target: '.myclub-groups-menu .myclub-groups-menu-container ul.sub-menu', property: 'background-color', type: 'color', default: '#ffffff' },
          { id: 'menu-sub-hover-bg', label: 'Submenu item hover bg', target: '.myclub-groups-menu .myclub-groups-menu-container ul.sub-menu li:hover', property: 'background-color', type: 'color', default: '#dcdcdc' },
          { id: 'menu-hamburger-color', label: 'Hamburger icon color', target: '.myclub-groups-menu .myclub-groups-menu-container .mobile-menu-button span', property: 'background-color', type: 'color', default: '#333333' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'menu-link-fs', label: 'Link font size', target: '.myclub-groups-menu .myclub-groups-menu-container a', property: 'font-size', type: 'size', default: '1rem', unit: 'rem', min: 0.6, max: 1.5, step: 0.05 },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'menu-link-padding', label: 'Link padding', target: '.myclub-groups-menu .myclub-groups-menu-container a', property: 'padding', type: 'select', default: '0.5rem 1rem', options: [{label:'Small (0.25rem 0.5rem)',value:'0.25rem 0.5rem'},{label:'Default (0.5rem 1rem)',value:'0.5rem 1rem'},{label:'Large (0.75rem 1.5rem)',value:'0.75rem 1.5rem'}] },
          { id: 'menu-mb', label: 'Block bottom margin', target: '.myclub-groups-menu', property: 'margin-bottom', type: 'size', default: '2rem', unit: 'rem', min: 0, max: 6, step: 0.25 },
        ]},
      ]
    },

    // ── Navigation ────────────────────────────────────────────────────────
    {
      name: 'Navigation', slug: 'navigation',
      mockupHTML: `
        <div class="myclub-groups-navigation">
          <div class="myclub-groups-navigation-container">
            <div class="myclub-groups-navigation-icons">
              ${['🏠','👥','📅','📰','📞'].map((icon,i)=>`
              <a href="#" onclick="return false" style="text-align:center;margin:0 1rem;">
                <div style="font-size:1.5rem;">${icon}</div>
                <div>${['Home','Team','Calendar','News','Contact'][i]}</div>
              </a>`).join('')}
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'nav-link-color', label: 'Link color', target: '.myclub-groups-navigation .myclub-groups-navigation-icons a', property: 'color', type: 'color', default: '#000000' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'nav-label-fs', label: 'Label font size', target: '.myclub-groups-navigation .myclub-groups-navigation-icons a div', property: 'font-size', type: 'size', default: '0.85rem', unit: 'rem', min: 0.5, max: 1.5, step: 0.05 },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'nav-icon-size', label: 'Icon height', target: '.myclub-groups-navigation .myclub-groups-navigation-icons a img', property: 'height', type: 'size', default: '1rem', unit: 'rem', min: 0.5, max: 4, step: 0.25 },
          { id: 'nav-link-margin', label: 'Link horizontal margin', target: '.myclub-groups-navigation .myclub-groups-navigation-icons a', property: 'margin-left', type: 'size', default: '1rem', unit: 'rem', min: 0, max: 3, step: 0.25 },
        ]},
      ]
    },

    // ── Title ─────────────────────────────────────────────────────────────
    {
      name: 'Title', slug: 'title',
      mockupHTML: `
        <div class="myclub-groups-title">
          <div class="myclub-groups-title-container">
            <div class="myclub-groups-title-box">
              <div class="myclub-groups-title-image" style="max-width:300px;">
                <img src="https://picsum.photos/seed/club/400/200" alt="" style="width:100%;height:auto;">
              </div>
              <div class="myclub-groups-title-information">
                <div class="myclub-groups-title-name">IK Exempel</div>
                <div class="myclub-groups-info-text">Senior Men • Division 1</div>
                <div class="myclub-groups-information">
                  <div class="label">Venue</div>
                  <div class="value">City Stadium</div>
                </div>
                <div class="myclub-groups-information">
                  <div class="label">Head Coach</div>
                  <div class="value">Anna Lindqvist</div>
                </div>
              </div>
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'title-box-bg', label: 'Box background', target: '.myclub-groups-title .myclub-groups-title-box', property: 'background-color', type: 'color', default: '#ffffff' },
          { id: 'title-name-color', label: 'Club name color', target: '.myclub-groups-title .myclub-groups-title-name', property: 'color', type: 'color', default: '#000000' },
          { id: 'title-info-text-color', label: 'Info text color', target: '.myclub-groups-title .myclub-groups-info-text', property: 'color', type: 'color', default: '#000000' },
          { id: 'title-label-color', label: 'Label color', target: '.myclub-groups-title .myclub-groups-title-information .label', property: 'color', type: 'color', default: '#000000' },
          { id: 'title-value-color', label: 'Value color', target: '.myclub-groups-title .myclub-groups-title-information .value', property: 'color', type: 'color', default: '#000000' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'title-name-fs', label: 'Club name font size', target: '.myclub-groups-title .myclub-groups-title-name', property: 'font-size', type: 'size', default: '2.5rem', unit: 'rem', min: 1, max: 5, step: 0.1 },
          { id: 'title-info-fs', label: 'Info text font size', target: '.myclub-groups-title .myclub-groups-info-text', property: 'font-size', type: 'select', default: 'smaller', options: [{label:'x-small',value:'x-small'},{label:'smaller',value:'smaller'},{label:'small',value:'small'},{label:'1rem',value:'1rem'}] },
          { id: 'title-label-fs', label: 'Label font size', target: '.myclub-groups-title .myclub-groups-title-information .label', property: 'font-size', type: 'select', default: 'small', options: [{label:'x-small',value:'x-small'},{label:'small',value:'small'},{label:'0.85rem',value:'0.85rem'},{label:'1rem',value:'1rem'}] },
          { id: 'title-value-fs', label: 'Value font size', target: '.myclub-groups-title .myclub-groups-title-information .value', property: 'font-size', type: 'size', default: '1.25rem', unit: 'rem', min: 0.8, max: 2.5, step: 0.05 },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'title-box-padding', label: 'Box padding', target: '.myclub-groups-title .myclub-groups-title-box', property: 'padding', type: 'size', default: '0.5rem', unit: 'rem', min: 0, max: 4, step: 0.25 },
          { id: 'title-mb', label: 'Block bottom margin', target: '.myclub-groups-title', property: 'margin-bottom', type: 'size', default: '2rem', unit: 'rem', min: 0, max: 6, step: 0.25 },
        ]},
      ]
    },

  ] // end blocks
}; // end PLUGIN_CONFIG
```

- [ ] **Verify** — open `myclub-groups.html`. All 9 tabs should appear. Click each tab — preview and controls should load. Change a color — the CSS output panel should show the override.

- [ ] **Commit**

```bash
git add myclub-groups.html && git commit -m "feat: add myclub-groups plugin config"
```

---

## Task 5 — myclub-sections.html — full PLUGIN_CONFIG

**Files:**
- Write: `~/dev/myclub/myclub-customizer/myclub-sections.html`

Concatenate for `originalCSS` (in order):
- `/home/anku/dev/myclub/myclub-sections/blocks/build/calendar/style-index.css`
- `/home/anku/dev/myclub/myclub-sections/blocks/build/club-calendar/style-index.css`
- `/home/anku/dev/myclub/myclub-sections/blocks/build/news/style-index.css`
- `/home/anku/dev/myclub/myclub-sections/blocks/build/club-news/style-index.css`
- `/home/anku/dev/myclub/myclub-sections/blocks/build/coming-games/style-index.css`
- `/home/anku/dev/myclub/myclub-sections/blocks/build/description/style-index.css`

- [ ] **Write the PLUGIN_CONFIG** (replace the script block in myclub-sections.html):

```javascript
const PLUGIN_CONFIG = {
  pluginName: 'myclub-sections',
  outputFilename: 'myclub-sections-custom.css',
  originalCSS: `/* paste concatenated CSS here */`,
  blocks: [

    // ── Calendar ──────────────────────────────────────────────────────────
    {
      name: 'Calendar', slug: 'sec-calendar',
      mockupHTML: `
        <div class="myclub-sections-calendar">
          <div class="myclub-sections-calendar-container">
            <div style="border:1px solid #e2e8f0;border-radius:4px;overflow:hidden;">
              <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-bottom:1px solid #e2e8f0;">
                <div style="display:flex;gap:4px;">
                  <button class="fc-button fc-button-primary" style="padding:4px 10px;border-radius:4px;font-size:13px;">‹</button>
                  <button class="fc-button fc-button-primary" style="padding:4px 10px;border-radius:4px;font-size:13px;">›</button>
                </div>
                <strong style="font-size:14px;">May 2026</strong>
                <button class="fc-button fc-button-primary fc-button-active" style="padding:4px 10px;border-radius:4px;font-size:13px;">Month</button>
              </div>
              <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:1px;background:#e2e8f0;padding:1px;">
                ${['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].map(d=>`<div style="background:#f8fafc;text-align:center;font-size:11px;padding:4px;color:#64748b;">${d}</div>`).join('')}
                ${Array.from({length:28},(_,i)=>{const day=i+1;const color=day===7?'yellow':day===14?'red':day===21?'green':day===28?'blue':'';return `<div style="background:#fff;min-height:54px;padding:4px;"><div style="font-size:11px;">${day}</div>${color?`<div class="fc-event-title ${color}" style="font-size:11px;padding:2px 4px;border-radius:3px;margin-top:2px;color:#fff;">Event</div>`:''}</div>`;}).join('')}
              </div>
            </div>
            <div class="myclub-sections-subscribe-button-wrapper">
              <button class="myclub-sections-subscribe-button">Subscribe to calendar</button>
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'scal-yellow', label: 'Event: Yellow bg', target: '.myclub-sections-calendar .fc .fc-event-title.yellow', property: 'background-color', type: 'color', default: '#9e8c39' },
          { id: 'scal-red', label: 'Event: Red bg', target: '.myclub-sections-calendar .fc .fc-event-title.red', property: 'background-color', type: 'color', default: '#c1272d' },
          { id: 'scal-green', label: 'Event: Green bg', target: '.myclub-sections-calendar .fc .fc-event-title.green', property: 'background-color', type: 'color', default: '#009245' },
          { id: 'scal-blue', label: 'Event: Blue bg', target: '.myclub-sections-calendar .fc .fc-event-title.blue', property: 'background-color', type: 'color', default: '#396b9e' },
          { id: 'scal-event-text', label: 'Event text color', target: '.myclub-sections-calendar .fc .fc-event-title', property: 'color', type: 'color', default: '#ffffff' },
          { id: 'scal-btn-active-bg', label: 'Active button bg', target: '.myclub-sections-calendar .fc .fc-button-primary:not(:disabled).fc-button-active', property: 'background-color', type: 'color', default: '#1a252f', important: true },
          { id: 'scal-sub-color', label: 'Subscribe text color', target: '.myclub-sections-calendar .myclub-sections-subscribe-button', property: 'color', type: 'color', default: '#21201e' },
          { id: 'scal-modal-bg', label: 'Modal background', target: '.myclub-sections-calendar .calendar-modal .modal-content', property: 'background-color', type: 'color', default: '#ffffff' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'scal-event-fs', label: 'Event font size', target: '.myclub-sections-calendar .fc .fc-event-title', property: 'font-size', type: 'size', default: '0.8rem', unit: 'rem', min: 0.5, max: 2, step: 0.05 },
        ]},
        { name: 'Borders & Radius', icon: '▭', controls: [
          { id: 'scal-modal-radius', label: 'Modal border radius', target: '.myclub-sections-calendar .calendar-modal .modal-content', property: 'border-radius', type: 'size', default: '8px', unit: 'px', min: 0, max: 30, step: 1 },
          { id: 'scal-event-radius', label: 'Event chip radius', target: '.myclub-sections-calendar .fc .fc-list-event-title .fc-event-title', property: 'border-radius', type: 'size', default: '6px', unit: 'px', min: 0, max: 20, step: 1 },
        ]},
      ]
    },

    // ── Club Calendar ─────────────────────────────────────────────────────
    {
      name: 'Club Calendar', slug: 'sec-club-calendar',
      mockupHTML: `
        <div class="myclub-sections-club-calendar">
          <div class="myclub-sections-club-calendar-container">
            <div style="border:1px solid #e2e8f0;border-radius:4px;overflow:hidden;padding:12px;">
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <button class="fc-button fc-button-primary" style="padding:4px 10px;border-radius:4px;font-size:13px;">‹ Prev</button>
                <strong>May 2026</strong>
                <button class="fc-button fc-button-primary fc-button-active" style="padding:4px 10px;border-radius:4px;font-size:13px;">Next ›</button>
              </div>
              <div style="display:flex;flex-direction:column;gap:4px;">
                ${[{day:'Mon 12',ev:'Club Training',color:'blue'},{day:'Wed 14',ev:'Board Meeting',color:'yellow'},{day:'Sat 17',ev:'Match Day',color:'red'}].map(r=>`
                <div style="display:flex;align-items:center;gap:8px;padding:4px 0;border-bottom:1px solid #f1f5f9;">
                  <span style="font-size:12px;min-width:60px;color:#64748b;">${r.day}</span>
                  <div class="fc-event-title ${r.color}" style="font-size:12px;padding:2px 8px;border-radius:4px;color:#fff;">${r.ev}</div>
                </div>`).join('')}
              </div>
            </div>
            <div class="myclub-sections-subscribe-button-wrapper">
              <button class="myclub-sections-subscribe-button">Subscribe to calendar</button>
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'sccal-yellow', label: 'Event: Yellow bg', target: '.myclub-sections-club-calendar .fc .fc-event-title.yellow', property: 'background-color', type: 'color', default: '#9e8c39' },
          { id: 'sccal-red', label: 'Event: Red bg', target: '.myclub-sections-club-calendar .fc .fc-event-title.red', property: 'background-color', type: 'color', default: '#c1272d' },
          { id: 'sccal-green', label: 'Event: Green bg', target: '.myclub-sections-club-calendar .fc .fc-event-title.green', property: 'background-color', type: 'color', default: '#009245' },
          { id: 'sccal-blue', label: 'Event: Blue bg', target: '.myclub-sections-club-calendar .fc .fc-event-title.blue', property: 'background-color', type: 'color', default: '#396b9e' },
          { id: 'sccal-event-text', label: 'Event text color', target: '.myclub-sections-club-calendar .fc .fc-event-title', property: 'color', type: 'color', default: '#ffffff' },
          { id: 'sccal-sub-color', label: 'Subscribe text color', target: '.myclub-sections-club-calendar .myclub-sections-subscribe-button', property: 'color', type: 'color', default: '#21201e' },
          { id: 'sccal-modal-bg', label: 'Modal background', target: '.myclub-sections-club-calendar .club-calendar-modal .modal-content', property: 'background-color', type: 'color', default: '#ffffff' },
        ]},
        { name: 'Borders & Radius', icon: '▭', controls: [
          { id: 'sccal-modal-radius', label: 'Modal border radius', target: '.myclub-sections-club-calendar .club-calendar-modal .modal-content', property: 'border-radius', type: 'size', default: '8px', unit: 'px', min: 0, max: 30, step: 1 },
        ]},
      ]
    },

    // ── News ──────────────────────────────────────────────────────────────
    {
      name: 'News', slug: 'sec-news',
      mockupHTML: `
        <div class="myclub-sections-news">
          <div class="myclub-sections-news-container">
            <div class="myclub-sections-news-list">
              ${[1,2,3].map(i=>`
              <div class="myclub-news-item" style="flex:0 1 calc(33% - 1rem)">
                <a href="#" onclick="return false">
                  <div class="myclub-news-image"><img src="https://picsum.photos/seed/${i+50}/400/225" alt="" style="width:100%;height:100%;object-fit:cover;display:block;"></div>
                  <div class="myclub-news-image-caption">Caption text</div>
                  <div style="font-size:0.9rem;margin-top:0.5rem;font-weight:600;">Section news ${i}</div>
                </a>
              </div>`).join('')}
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'snews-item-bg', label: 'Item background', target: '.myclub-sections-news .myclub-news-item', property: 'background-color', type: 'color', default: 'transparent' },
          { id: 'snews-link-color', label: 'Link color', target: '.myclub-sections-news .myclub-news-item a', property: 'color', type: 'color', default: '#000000' },
          { id: 'snews-img-bg', label: 'Image placeholder bg', target: '.myclub-sections-news .myclub-news-image', property: 'background-color', type: 'color', default: '#f2f2f2' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'snews-caption-fs', label: 'Caption font size', target: '.myclub-sections-news .myclub-news-image-caption', property: 'font-size', type: 'select', default: 'small', options: [{label:'x-small',value:'x-small'},{label:'small',value:'small'},{label:'1rem',value:'1rem'}] },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'snews-mb', label: 'Block bottom margin', target: '.myclub-sections-news', property: 'margin-bottom', type: 'size', default: '2rem', unit: 'rem', min: 0, max: 6, step: 0.25 },
        ]},
        { name: 'Borders & Radius', icon: '▭', controls: [
          { id: 'snews-img-radius', label: 'Image border radius', target: '.myclub-sections-news .myclub-news-image', property: 'border-radius', type: 'size', default: '0px', unit: 'px', min: 0, max: 20, step: 1 },
        ]},
      ]
    },

    // ── Club News ─────────────────────────────────────────────────────────
    {
      name: 'Club News', slug: 'sec-club-news',
      mockupHTML: `
        <div class="myclub-sections-club-news">
          <div class="myclub-sections-club-news-container">
            <div class="myclub-sections-club-news-list">
              ${[1,2,3].map(i=>`
              <div class="myclub-club-news-item" style="flex:0 1 calc(33% - 1rem)">
                <a href="#" onclick="return false">
                  <div class="myclub-club-news-image"><img src="https://picsum.photos/seed/${i+60}/400/225" alt="" style="width:100%;height:100%;object-fit:cover;display:block;"></div>
                  <div class="myclub-club-news-image-caption">Caption text</div>
                  <div style="font-size:0.9rem;margin-top:0.5rem;font-weight:600;">Club news headline ${i}</div>
                </a>
              </div>`).join('')}
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'scnews-item-bg', label: 'Item background', target: '.myclub-sections-club-news .myclub-club-news-item', property: 'background-color', type: 'color', default: 'transparent' },
          { id: 'scnews-link-color', label: 'Link color', target: '.myclub-sections-club-news .myclub-club-news-item a', property: 'color', type: 'color', default: '#000000' },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'scnews-mb', label: 'Block bottom margin', target: '.myclub-sections-club-news', property: 'margin-bottom', type: 'size', default: '2rem', unit: 'rem', min: 0, max: 6, step: 0.25 },
        ]},
        { name: 'Borders & Radius', icon: '▭', controls: [
          { id: 'scnews-img-radius', label: 'Image border radius', target: '.myclub-sections-club-news .myclub-club-news-image', property: 'border-radius', type: 'size', default: '0px', unit: 'px', min: 0, max: 20, step: 1 },
        ]},
      ]
    },

    // ── Coming Games ──────────────────────────────────────────────────────
    {
      name: 'Coming Games', slug: 'sec-coming-games',
      mockupHTML: `
        <div class="myclub-sections-coming-games">
          <div class="myclub-sections-coming-games-container">
            <div class="coming-games-list">
              ${[
                {title:'Team A vs Team B',group:'Senior',venue:'Main Arena',date:'2026-06-01',time:'15:00'},
                {title:'Team C vs Team D',group:'U17',venue:'West Field',date:'2026-06-04',time:'11:00'},
              ].map(g=>`
              <div class="myclub-sections-coming-game">
                <div class="title"><div>${g.title}</div><div class="group-name">${g.group}</div></div>
                <div class="venue">${g.venue}</div>
                <div class="date">${g.date}<div class="time">${g.time}</div></div>
              </div>`).join('')}
            </div>
          </div>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'scg-odd-bg', label: 'Odd row background', target: '.myclub-sections-coming-games .myclub-sections-coming-game:nth-child(odd)', property: 'background-color', type: 'color', default: '#53565a' },
          { id: 'scg-even-bg', label: 'Even row background', target: '.myclub-sections-coming-games .myclub-sections-coming-game:nth-child(even)', property: 'background-color', type: 'color', default: '#9c9ea0' },
          { id: 'scg-text-color', label: 'Text color', target: '.myclub-sections-coming-games .myclub-sections-coming-game', property: 'color', type: 'color', default: '#ffffff' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'scg-group-fs', label: 'Group name font size', target: '.myclub-sections-coming-games .myclub-sections-coming-game .title .group-name', property: 'font-size', type: 'select', default: 'smaller', options: [{label:'x-small',value:'x-small'},{label:'smaller',value:'smaller'},{label:'small',value:'small'},{label:'1rem',value:'1rem'}] },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'scg-row-padding', label: 'Row padding', target: '.myclub-sections-coming-games .myclub-sections-coming-game', property: 'padding', type: 'size', default: '1rem', unit: 'rem', min: 0, max: 3, step: 0.25 },
        ]},
      ]
    },

    // ── Description ───────────────────────────────────────────────────────
    {
      name: 'Description', slug: 'sec-description',
      mockupHTML: `
        <div class="myclub-sections-description">
          <div class="myclub-sections-news-container">
            <p style="font-size:1rem;line-height:1.7;color:#1e293b;">
              This is a description block. It displays rich text content about the section.
              The text can be styled to match the club's visual identity.
            </p>
          </div>
        </div>`,
      sections: [
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'sdesc-mb', label: 'Block bottom margin', target: '.myclub-sections-description', property: 'margin-bottom', type: 'size', default: '2rem', unit: 'rem', min: 0, max: 6, step: 0.25 },
        ]},
      ]
    },

  ] // end blocks
}; // end PLUGIN_CONFIG
```

- [ ] **Verify** — open `myclub-sections.html`. All 6 tabs appear and work correctly.

- [ ] **Commit**

```bash
git add myclub-sections.html && git commit -m "feat: add myclub-sections plugin config"
```

---

## Task 6 — myclub-booking.html — full PLUGIN_CONFIG

**Files:**
- Write: `~/dev/myclub/myclub-customizer/myclub-booking.html`

`originalCSS`: content of `/home/anku/dev/myclub/myclub-booking/blocks/build/calendar/style-index.css`

- [ ] **Write the PLUGIN_CONFIG**:

```javascript
const PLUGIN_CONFIG = {
  pluginName: 'myclub-booking',
  outputFilename: 'myclub-booking-custom.css',
  originalCSS: `/* paste booking style-index.css content here */`,
  blocks: [

    // ── Booking Calendar ──────────────────────────────────────────────────
    {
      name: 'Booking Calendar', slug: 'booking-calendar',
      mockupHTML: `
        <div class="myclub-booking-calendar">
          <div class="myclub-booking-calendar-container">
            <div style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
              <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-bottom:1px solid #e2e8f0;">
                <div style="display:flex;gap:4px;">
                  <button class="fc-button fc-button-primary" style="padding:4px 10px;border-radius:4px;font-size:13px;">‹</button>
                  <button class="fc-button fc-button-primary" style="padding:4px 10px;border-radius:4px;font-size:13px;">›</button>
                </div>
                <strong style="font-size:14px;">May 2026</strong>
                <button class="fc-button fc-button-primary fc-button-active" style="padding:4px 10px;border-radius:4px;font-size:13px;">Month</button>
              </div>
              <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:1px;background:#e2e8f0;padding:1px;">
                ${['Mon','Tue','Wed','Thu','Fri','Sat','Sun'].map(d=>`<div style="background:#f8fafc;text-align:center;font-size:11px;padding:4px;color:#64748b;">${d}</div>`).join('')}
                ${Array.from({length:28},(_,i)=>{const day=i+1;const hasSlot=[3,7,10,14,17,21,24,28].includes(day);return `<div style="background:#fff;min-height:54px;padding:4px;"><div style="font-size:11px;">${day}</div>${hasSlot?`<div class="fc-event-title" style="background:#395b9e;font-size:11px;padding:2px 4px;border-radius:3px;margin-top:2px;color:#fff;cursor:pointer;">09:00 Slot</div>`:''}</div>`;}).join('')}
              </div>
            </div>
          </div>
        </div>
        <div class="myclub-selected-slots-panel is-visible" style="position:relative;transform:none;margin-top:12px;border-radius:8px;border-top:none;">
          <span class="myclub-panel-label">Selected</span>
          <div class="myclub-selected-slots-list">
            <div class="myclub-selected-slot-item"><span>Mon 3 May · 09:00</span><button class="myclub-remove-slot">×</button></div>
            <div class="myclub-selected-slot-item"><span>Wed 7 May · 14:00</span><button class="myclub-remove-slot">×</button></div>
          </div>
          <button class="myclub-book-btn">Book 2 slots</button>
        </div>`,
      sections: [
        { name: 'Colors', icon: '🎨', controls: [
          { id: 'bk-event-text', label: 'Event text color', target: '.myclub-booking-calendar .fc .fc-event-title', property: 'color', type: 'color', default: '#ffffff' },
          { id: 'bk-btn-active-bg', label: 'Active calendar btn bg', target: '.myclub-booking-calendar .fc .fc-button-primary:not(:disabled).fc-button-active', property: 'background-color', type: 'color', default: '#1a252f', important: true },
          { id: 'bk-modal-bg', label: 'Booking modal background', target: '.myclub-booking-calendar .calendar-modal .modal-content', property: 'background', type: 'color', default: '#ffffff' },
          { id: 'bk-modal-name-color', label: 'Modal title color', target: '.myclub-booking-calendar .calendar-modal .modal-content .modal-body .name', property: 'color', type: 'color', default: '#0f172a' },
          { id: 'bk-input-bg', label: 'Form input background', target: '.myclub-booking-calendar .calendar-modal .modal-content .modal-body .myclub-input', property: 'background', type: 'color', default: '#f8fafc' },
          { id: 'bk-input-border', label: 'Form input border color', target: '.myclub-booking-calendar .calendar-modal .modal-content .modal-body .myclub-input', property: 'border-color', type: 'color', default: '#e2e8f0' },
          { id: 'bk-input-focus-border', label: 'Input focus border', target: '.myclub-booking-calendar .calendar-modal .modal-content .modal-body .myclub-input:focus', property: 'border-color', type: 'color', default: '#395b9e' },
          { id: 'bk-submit-bg', label: 'Submit button bg', target: '.myclub-booking-calendar .calendar-modal .modal-content .modal-body .myclub-button', property: 'background', type: 'color', default: '#395b9e' },
          { id: 'bk-submit-color', label: 'Submit button text', target: '.myclub-booking-calendar .calendar-modal .modal-content .modal-body .myclub-button', property: 'color', type: 'color', default: '#ffffff' },
          { id: 'bk-panel-bg', label: 'Selected slots panel bg', target: '.myclub-selected-slots-panel', property: 'background', type: 'color', default: '#0f172a' },
          { id: 'bk-chip-bg', label: 'Slot chip background', target: '.myclub-selected-slots-panel .myclub-selected-slot-item', property: 'background', type: 'color', default: 'rgba(57,91,158,0.35)' },
          { id: 'bk-chip-text', label: 'Slot chip text color', target: '.myclub-selected-slots-panel .myclub-selected-slot-item span', property: 'color', type: 'color', default: '#cbd5e1' },
          { id: 'bk-book-btn-bg', label: 'Book button background', target: '.myclub-selected-slots-panel .myclub-book-btn', property: 'background', type: 'color', default: '#395b9e' },
          { id: 'bk-book-btn-color', label: 'Book button text', target: '.myclub-selected-slots-panel .myclub-book-btn', property: 'color', type: 'color', default: '#ffffff' },
        ]},
        { name: 'Typography', icon: '✏️', controls: [
          { id: 'bk-event-fs', label: 'Event font size', target: '.myclub-booking-calendar .fc .fc-event-title', property: 'font-size', type: 'size', default: '0.8rem', unit: 'rem', min: 0.5, max: 2, step: 0.05 },
          { id: 'bk-input-fs', label: 'Form input font size', target: '.myclub-booking-calendar .calendar-modal .modal-content .modal-body .myclub-input', property: 'font-size', type: 'size', default: '15px', unit: 'px', min: 10, max: 20, step: 1 },
          { id: 'bk-submit-fs', label: 'Submit button font size', target: '.myclub-booking-calendar .calendar-modal .modal-content .modal-body .myclub-button', property: 'font-size', type: 'size', default: '15px', unit: 'px', min: 10, max: 20, step: 1 },
        ]},
        { name: 'Spacing', icon: '📐', controls: [
          { id: 'bk-modal-padding', label: 'Modal padding', target: '.myclub-booking-calendar .calendar-modal .modal-content', property: 'padding', type: 'size', default: '28px', unit: 'px', min: 10, max: 60, step: 2 },
          { id: 'bk-panel-padding', label: 'Slots panel padding', target: '.myclub-selected-slots-panel', property: 'padding', type: 'select', default: '13px 24px', options: [{label:'Small (8px 14px)',value:'8px 14px'},{label:'Default (13px 24px)',value:'13px 24px'},{label:'Large (18px 32px)',value:'18px 32px'}] },
        ]},
        { name: 'Borders & Radius', icon: '▭', controls: [
          { id: 'bk-modal-radius', label: 'Modal border radius', target: '.myclub-booking-calendar .calendar-modal .modal-content', property: 'border-radius', type: 'size', default: '14px', unit: 'px', min: 0, max: 30, step: 1 },
          { id: 'bk-input-radius', label: 'Form input radius', target: '.myclub-booking-calendar .calendar-modal .modal-content .modal-body .myclub-input', property: 'border-radius', type: 'size', default: '8px', unit: 'px', min: 0, max: 20, step: 1 },
          { id: 'bk-submit-radius', label: 'Submit button radius', target: '.myclub-booking-calendar .calendar-modal .modal-content .modal-body .myclub-button', property: 'border-radius', type: 'size', default: '8px', unit: 'px', min: 0, max: 20, step: 1 },
          { id: 'bk-chip-radius', label: 'Slot chip radius', target: '.myclub-selected-slots-panel .myclub-selected-slot-item', property: 'border-radius', type: 'size', default: '100px', unit: 'px', min: 0, max: 100, step: 4 },
          { id: 'bk-book-btn-radius', label: 'Book button radius', target: '.myclub-selected-slots-panel .myclub-book-btn', property: 'border-radius', type: 'size', default: '8px', unit: 'px', min: 0, max: 20, step: 1 },
        ]},
      ]
    },

  ] // end blocks
}; // end PLUGIN_CONFIG
```

- [ ] **Verify** — open `myclub-booking.html`. The booking calendar tab renders with the slots panel mock. Changing colors updates the preview and the CSS panel.

- [ ] **Commit**

```bash
git add myclub-booking.html && git commit -m "feat: add myclub-booking plugin config"
```

---

## Task 7 — Embed original CSS + final end-to-end verification

**Files:**
- Modify: all three HTML files (inject originalCSS)

- [ ] **Concatenate and embed the CSS for each plugin**

Run this to get the concatenated CSS strings:

```bash
# Groups
cat \
  ~/dev/myclub/myclub-groups/blocks/build/calendar/style-index.css \
  ~/dev/myclub/myclub-groups/blocks/build/club-calendar/style-index.css \
  ~/dev/myclub/myclub-groups/blocks/build/leaders/style-index.css \
  ~/dev/myclub/myclub-groups/blocks/build/members/style-index.css \
  ~/dev/myclub/myclub-groups/blocks/build/news/style-index.css \
  ~/dev/myclub/myclub-groups/blocks/build/club-news/style-index.css \
  ~/dev/myclub/myclub-groups/blocks/build/coming-games/style-index.css \
  ~/dev/myclub/myclub-groups/blocks/build/menu/style-index.css \
  ~/dev/myclub/myclub-groups/blocks/build/navigation/style-index.css \
  ~/dev/myclub/myclub-groups/blocks/build/title/style-index.css \
  > /tmp/groups-css.css

# Sections
cat \
  ~/dev/myclub/myclub-sections/blocks/build/calendar/style-index.css \
  ~/dev/myclub/myclub-sections/blocks/build/club-calendar/style-index.css \
  ~/dev/myclub/myclub-sections/blocks/build/news/style-index.css \
  ~/dev/myclub/myclub-sections/blocks/build/club-news/style-index.css \
  ~/dev/myclub/myclub-sections/blocks/build/coming-games/style-index.css \
  ~/dev/myclub/myclub-sections/blocks/build/description/style-index.css \
  > /tmp/sections-css.css

# Booking
cp ~/dev/myclub/myclub-booking/blocks/build/calendar/style-index.css /tmp/booking-css.css
```

Then replace the `/* paste ... */` placeholder in each HTML file's `originalCSS` with the file contents, wrapped in a template literal:

```javascript
originalCSS: `<contents of /tmp/groups-css.css>`,
```

- [ ] **Verify all three apps end-to-end**

Open each file in the browser and confirm:
1. All tabs load with a styled preview (not just a white box — colors from the default CSS should be visible)
2. Change a color control — preview updates immediately
3. Change a size control — preview updates immediately
4. The CSS panel shows only changed properties, formatted cleanly
5. Click "Copy" — paste into a text editor, CSS is valid
6. Click "Download .css" — a file is saved with the correct filename

- [ ] **Final commit**

```bash
git add . && git commit -m "feat: embed original CSS, complete all three customizer apps"
```

---

## Self-Review

**Spec coverage:**
- ✅ Three separate HTML apps (one per plugin)
- ✅ Shared engine + theme in `shared/`
- ✅ Tabbed layout, one tab per block
- ✅ Live iframe preview with original CSS embedded
- ✅ Controls grouped into collapsible sections (Colors, Typography, Spacing, Borders)
- ✅ All four control types: color, size, select, toggle
- ✅ Only changed values emitted in CSS output
- ✅ Copy to clipboard + Download .css buttons
- ✅ CSS selectors shown under each control label
- ✅ `important: true` flag for FC overrides

**Placeholder scan:** No TBDs — only `/* paste ... */` in originalCSS which is resolved in Task 7 with exact bash commands.

**Type consistency:** All control `id` values are unique per plugin. `hChange`, `hColor`, `hSize` match usage in `ctrlHTML`. `buildCSS` calls `block.sections` which is defined in every block. `important: true` is checked in `buildCSS` via `c.important`.
