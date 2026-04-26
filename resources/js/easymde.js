import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';
import '../css/easymde.css';

/** Matches @[Name](user:ID) anywhere in a string. */
const MENTION_RE = /@\[([^\]]+)\]\(user:([^)]+)\)/g;

/** Matches ![alt](url) anywhere in a string. */
const IMAGE_RE = /!\[([^\]]*)\]\(([^)]+)\)/g;

// ── Mention helpers ───────────────────────────────────────────────────────────

/**
 * Apply a CodeMirror markText() over a mention range, visually replacing the
 * raw syntax with a pretty @Name chip. The underlying markdown is unchanged.
 */
function applyMentionMark(cm, from, to, name) {
    const chip = document.createElement('span');
    chip.className = 'mention mention--editor';
    chip.textContent = '@' + name;

    chip.addEventListener('mousedown', e => {
        e.preventDefault();
        cm.setCursor(to);
        cm.focus();
    });

    cm.markText(from, to, {
        replacedWith: chip,
        atomic: true,
        handleMouseEvents: false,
    });
}

/**
 * Scan every line and chip-ify any @mention syntax not already marked.
 * Called once on load to handle pre-existing content.
 */
function scanAndMarkMentions(cm) {
    const lineCount = cm.lineCount();
    for (let lineNo = 0; lineNo < lineCount; lineNo++) {
        const lineText = cm.getLine(lineNo);
        MENTION_RE.lastIndex = 0;
        let match;
        while ((match = MENTION_RE.exec(lineText)) !== null) {
            const from = { line: lineNo, ch: match.index };
            const to   = { line: lineNo, ch: match.index + match[0].length };
            if (cm.findMarksAt(from).some(m => m.replacedWith)) continue;
            applyMentionMark(cm, from, to, match[1]);
        }
    }
}

// ── Image helpers ─────────────────────────────────────────────────────────────

/**
 * Apply a CodeMirror markText() over an image markdown range, visually
 * replacing ![alt](url) with a thumbnail preview. The underlying markdown
 * is unchanged so the form submits the correct value.
 */
function applyImageMark(cm, from, to, url, alt) {
    const wrapper = document.createElement('span');
    wrapper.className = 'editor-image-preview';
    wrapper.title = alt || url;

    const img = document.createElement('img');
    img.src = url;
    img.alt = alt || '';
    img.className = 'editor-image-preview__img';

    // On load error (e.g. broken URL) fall back to showing the raw syntax.
    img.addEventListener('error', () => {
        const mark = cm.findMarksAt(from).find(m => m.replacedWith === wrapper);
        mark?.clear();
    });

    wrapper.appendChild(img);

    // Clicking the preview moves the cursor just past it.
    wrapper.addEventListener('mousedown', e => {
        e.preventDefault();
        cm.setCursor(to);
        cm.focus();
    });

    cm.markText(from, to, {
        replacedWith: wrapper,
        atomic: true,
        handleMouseEvents: false,
    });
}

/**
 * Scan every line and preview-ify any image syntax not already marked.
 * Called once on load to handle pre-existing content.
 */
function scanAndMarkImages(cm) {
    const lineCount = cm.lineCount();
    for (let lineNo = 0; lineNo < lineCount; lineNo++) {
        const lineText = cm.getLine(lineNo);
        IMAGE_RE.lastIndex = 0;
        let match;
        while ((match = IMAGE_RE.exec(lineText)) !== null) {
            const from = { line: lineNo, ch: match.index };
            const to   = { line: lineNo, ch: match.index + match[0].length };
            if (cm.findMarksAt(from).some(m => m.replacedWith)) continue;
            applyImageMark(cm, from, to, match[2], match[1]);
        }
    }
}

/**
 * Scan only the lines touched by a CodeMirror change event for new image syntax.
 */
function scanChangedLinesForImages(cm, changeObj) {
    const fromLine = changeObj.from.line;
    // After the change, the affected range may span multiple lines.
    const toLine   = changeObj.from.line + (changeObj.text.length - 1);
    for (let lineNo = fromLine; lineNo <= toLine; lineNo++) {
        const lineText = cm.getLine(lineNo);
        if (!lineText) continue;
        IMAGE_RE.lastIndex = 0;
        let match;
        while ((match = IMAGE_RE.exec(lineText)) !== null) {
            const from = { line: lineNo, ch: match.index };
            const to   = { line: lineNo, ch: match.index + match[0].length };
            if (cm.findMarksAt(from).some(m => m.replacedWith)) continue;
            applyImageMark(cm, from, to, match[2], match[1]);
        }
    }
}

// ── EasyMDE bootstrap ─────────────────────────────────────────────────────────

/**
 * Boot every <textarea data-easymde> on the page.
 * data-image-upload-url  – endpoint that accepts POST {image} and returns {url}
 * data-csrf              – CSRF token for the upload request
 * data-mention-url       – endpoint for @mention member search (returns [{id, name}])
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea[data-easymde]').forEach(el => {
        const uploadUrl  = el.dataset.imageUploadUrl;
        const csrf       = el.dataset.csrf;
        const mentionUrl = el.dataset.mentionUrl;

        const mde = new EasyMDE({
            element: el,
            spellChecker: false,
            autosave: { enabled: false },
            toolbar: [
                'bold','italic','heading','|',
                'quote','unordered-list','ordered-list','|',
                'link','|',
                'preview','side-by-side','fullscreen','|',
                'guide',
            ],
            uploadImage: !!uploadUrl,
            imageUploadFunction: uploadUrl ? (file, onSuccess, onError) => {
                const fd = new FormData();
                fd.append('image', file);
                fd.append('_token', csrf);
                fetch(uploadUrl, { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.url) onSuccess(data.url);
                        else onError(data.message || 'Upload failed');
                    })
                    .catch(() => onError('Upload failed'));
            } : undefined,
        });

        const cm = mde.codemirror;

        // Keep the hidden textarea in sync so the form submits the markdown value.
        cm.on('change', () => { el.value = mde.value(); });

        // Scan changed lines for newly inserted image syntax (fires after upload).
        cm.on('change', (instance, changeObj) => {
            scanChangedLinesForImages(instance, changeObj);
        });

        // Apply marks for any content already in the editor on load
        // (e.g. editing an existing task description or comment).
        scanAndMarkMentions(cm);
        scanAndMarkImages(cm);

        // Boot @mention autocomplete if a search URL is provided.
        if (mentionUrl) {
            bootMentionAutocomplete(cm, mentionUrl);
        }
    });
});

// ── @mention autocomplete ─────────────────────────────────────────────────────

/**
 * Wire up @mention autocomplete on a CodeMirror instance.
 * Typing "@" followed by characters opens a dropdown of matching project members.
 * Navigate with ↑/↓, confirm with Enter/Tab, or click a result.
 * Inserts @[Name](user:ID) markdown syntax, then immediately chips it.
 */
function bootMentionAutocomplete(cm, searchUrl) {
    const dropdown = document.createElement('ul');
    dropdown.className = 'mention-dropdown';
    document.body.appendChild(dropdown);

    let active        = false;
    let triggerPos    = null;
    let results       = [];
    let selectedIndex = 0;
    let fetchTimer    = null;

    function show(items, query) {
        results       = items;
        selectedIndex = 0;
        renderDropdown(query);
    }

    function hide() {
        active     = false;
        triggerPos = null;
        results    = [];
        dropdown.innerHTML     = '';
        dropdown.style.display = 'none';
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function renderDropdown(query) {
        dropdown.innerHTML = '';
        if (!results.length) { hide(); return; }

        active = true;

        results.forEach((user, idx) => {
            const li = document.createElement('li');
            li.className = 'mention-item' + (idx === selectedIndex ? ' mention-item--active' : '');

            const name   = user.name;
            const qLower = query.toLowerCase();
            const pos    = name.toLowerCase().indexOf(qLower);
            if (pos !== -1 && query.length) {
                li.innerHTML =
                    escHtml(name.slice(0, pos)) +
                    '<mark>' + escHtml(name.slice(pos, pos + query.length)) + '</mark>' +
                    escHtml(name.slice(pos + query.length));
            } else {
                li.textContent = name;
            }

            li.addEventListener('mousedown', e => {
                e.preventDefault();
                insertMention(user);
            });

            dropdown.appendChild(li);
        });

        const coords = cm.cursorCoords(triggerPos, 'window');
        dropdown.style.display  = 'block';
        dropdown.style.left     = coords.left + 'px';
        dropdown.style.top      = (coords.bottom + 4) + 'px';
        dropdown.style.minWidth = '200px';
    }

    function updateSelection(newIdx) {
        const items = dropdown.querySelectorAll('.mention-item');
        items[selectedIndex]?.classList.remove('mention-item--active');
        selectedIndex = (newIdx + results.length) % results.length;
        items[selectedIndex]?.classList.add('mention-item--active');
        items[selectedIndex]?.scrollIntoView({ block: 'nearest' });
    }

    function insertMention(user, atPos) {
        const pos = atPos ?? triggerPos;
        if (!pos) return;

        const cursor = cm.getCursor();
        const syntax = `@[${user.name}](user:${user.id})`;
        cm.replaceRange(syntax, pos, cursor);

        const endPos = { line: pos.line, ch: pos.ch + syntax.length };
        applyMentionMark(cm, pos, endPos, user.name);

        hide();
        cm.focus();
    }

    function fetchMembers(query) {
        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(() => {
            fetch(searchUrl + '?q=' + encodeURIComponent(query), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => show(data, query))
            .catch(() => hide());
        }, 150);
    }

    cm.on('change', (instance) => {
        const cursor   = instance.getCursor();
        const lineText = instance.getLine(cursor.line);

        let atPos = -1;
        for (let i = cursor.ch - 1; i >= 0; i--) {
            if (lineText[i] === '@') { atPos = i; break; }
            if (/\s/.test(lineText[i])) break;
        }

        if (atPos === -1) { if (active) hide(); return; }

        const query = lineText.slice(atPos + 1, cursor.ch);
        if (/[^a-zA-Z0-9 '\-]/.test(query)) { if (active) hide(); return; }

        triggerPos = { line: cursor.line, ch: atPos };
        fetchMembers(query);
    });

    cm.on('keydown', (instance, e) => {
        if (!active) return;

        const snapPos     = triggerPos;
        const snapResults = results.slice();
        const snapIndex   = selectedIndex;

        if (e.key === 'ArrowDown')    { e.preventDefault(); updateSelection(selectedIndex + 1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); updateSelection(selectedIndex - 1); }
        else if (e.key === 'Enter' || e.key === 'Tab') {
            if (snapResults.length && snapPos) {
                e.preventDefault();
                insertMention(snapResults[snapIndex], snapPos);
            }
        }
        else if (e.key === 'Escape') { hide(); }
    });

    cm.on('blur', () => { setTimeout(() => { if (active) hide(); }, 200); });

    window.addEventListener('scroll', () => { if (active) hide(); }, true);
    window.addEventListener('resize', () => { if (active) hide(); });
}
