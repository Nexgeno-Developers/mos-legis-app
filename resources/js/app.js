import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import 'trix';
// Only the icons used in the Blade views (run `grep` for icon names when adding new ones).
import {
    createIcons,
    Activity, Archive, ArrowLeft, ArrowRight, Award, BadgeCheck,
    BadgeIndianRupee, Ban, BookOpen, Briefcase, CalendarRange, Check,
    ChevronDown, ChevronLeft, ChevronRight, CircleAlert, CircleCheck, Clock,
    Columns, Contact, Copy, CreditCard, Delete, Download,
    ExternalLink, Eye, EyeOff, File, FileText, Files,
    Filter, Folder, Form, Gauge, Gavel, Ghost,
    GitBranch, Heading, Home, Image, Inbox, Info,
    KeyRound, Layout, Lock, LogIn, LogOut, Mail,
    Menu, MessageSquare, Network, PanelLeft, Pencil, Phone,
    Plus, Receipt, RefreshCw, Reply, Route, Rows,
    Save, ScanSearch, Search, Send, Settings, ShieldCheck, CircleX, LoaderCircle, Hourglass, EllipsisVertical, Hash,
    Shuffle, SquareUser, Star, Summary, Tag, Tags,
    Text, ToggleLeft, ToggleRight, Trash2, Type, Upload,
    User, UserCheck, UserPlus, Users, Volume, X, MapPin, GripVertical, Link,
} from 'lucide';
import { countDocxWords } from './word-count';
import { initForms, refreshSelects } from './forms';

const usedIcons = { CircleX, LoaderCircle, Hourglass, EllipsisVertical, Hash, Activity, Archive, ArrowLeft, ArrowRight, Award, BadgeCheck, BadgeIndianRupee, Ban, BookOpen, Briefcase, CalendarRange, Check, ChevronDown, ChevronLeft, ChevronRight, CircleAlert, CircleCheck, Clock, Columns, Contact, Copy, CreditCard, Delete, Download, ExternalLink, Eye, EyeOff, File, FileText, Files, Filter, Folder, Form, Gauge, Gavel, Ghost, GitBranch, Heading, Home, Image, Inbox, Info, KeyRound, Layout, Lock, LogIn, LogOut, Mail, Menu, MessageSquare, Network, PanelLeft, Pencil, Phone, Plus, Receipt, RefreshCw, Reply, Route, Rows, Save, ScanSearch, Search, Send, Settings, ShieldCheck, Shuffle, CircleX, LoaderCircle, Hourglass, SquareUser, Star, Summary, Tag, Tags, Text, ToggleLeft, ToggleRight, Trash2, Type, Upload, User, UserCheck, UserPlus, Users, Volume, X, MapPin, GripVertical, Link };

window.Alpine = Alpine;
Alpine.plugin(collapse);

// "⋯" row-actions menu: fixed position next to its button (right-aligned), flipped above when there is no room below.
Alpine.data('rowActions', () => ({
    open: false,
    style: '',
    toggle() {
        if (this.open) return this.close();
        this.style = 'visibility: hidden;'; // measured first, then placed
        this.open = true;
        this.$nextTick(() => {
            const button = this.$refs.trigger.getBoundingClientRect();
            const menu = this.$refs.menu.getBoundingClientRect();
            const left = Math.max(8, Math.min(button.right - menu.width, window.innerWidth - menu.width - 8));
            const below = button.bottom + 4;
            const top = below + menu.height > window.innerHeight - 8 ? Math.max(8, button.top - menu.height - 4) : below;
            this.style = `top: ${top}px; left: ${left}px;`;
        });
    },
    close() {
        this.open = false;
    },
}));

/**
 * Reusable modal state: `x-data="modal(open)"`, `@click="show(record)"`.
 * The optional record is exposed as `form` so inputs can bind `x-model="form.name"`.
 */
Alpine.data('modal', (initiallyOpen = false, defaults = {}) => ({
    open: initiallyOpen,
    form: { ...defaults },
    show(record = {}) {
        this.form = { ...defaults, ...record };
        this.style = 'visibility: hidden;'; // measured first, then placed
        this.open = true;
        this.$nextTick(() => {
            renderIcons();
            refreshSelects(this.$el);
            // Clear errors left from a previous open.
            const form = this.$el.querySelector('form');
            if (form && window.$(form).data('validator')) window.$(form).validate().resetForm();
        });
    },
    hide() {
        this.open = false;
    },
}));

/**
 * "Connect your ORCID iD": opens ORCID sign-in in a pop-up; the pop-up posts the verified iD back
 * (the server keeps the trusted copy). If pop-ups are blocked, the page redirects instead and the
 * form's typed values are restored on return.
 */
const ORCID_DRAFT_KEY = 'orcid-form-draft';

Alpine.data('orcidConnect', ({ orcid = null, connectUrl, forgetUrl = null }) => ({
    orcid,
    busy: false,
    error: null,
    init() {
        this.restoreDraft();
        window.addEventListener('message', (event) => {
            if (event.origin !== window.location.origin || event.data?.type !== 'orcid-connect') return;
            this.busy = false;
            if (!event.data.ok) {
                this.error = event.data.message;
                return;
            }
            this.orcid = event.data.orcid;
            this.error = null;
            const name = this.$root.closest('form')?.querySelector('[name=name]');
            if (name && !name.value && event.data.name) {
                name.value = event.data.name.toLowerCase().replace(/\b\p{L}/gu, (c) => c.toUpperCase());
            }
        });
    },
    connect() {
        this.error = null;
        const popup = window.open(`${connectUrl}?popup=1`, 'orcid-connect', 'width=520,height=720,menubar=no,toolbar=no,location=yes');
        if (!popup) {
            this.saveDraft();
            window.location.href = connectUrl;
            return;
        }
        this.busy = true;
        const timer = setInterval(() => {
            if (popup.closed) {
                clearInterval(timer);
                this.busy = false;
            }
        }, 700);
    },
    async forget() {
        if (!forgetUrl) return;
        await fetch(forgetUrl, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
        });
        this.orcid = null;
    },
    saveDraft() {
        const form = this.$root.closest('form');
        if (!form) return;
        const values = {};
        new FormData(form).forEach((value, key) => {
            if (!/password|_token|_method/.test(key) && typeof value === 'string') values[key] = value;
        });
        try { sessionStorage.setItem(ORCID_DRAFT_KEY, JSON.stringify(values)); } catch (e) { /* storage unavailable */ }
    },
    restoreDraft() {
        let values = null;
        try {
            values = JSON.parse(sessionStorage.getItem(ORCID_DRAFT_KEY) || 'null');
            sessionStorage.removeItem(ORCID_DRAFT_KEY);
        } catch (e) { return; }
        const form = this.$root.closest('form');
        if (!values || !form) return;
        Object.entries(values).forEach(([key, value]) => {
            const field = form.elements.namedItem(key);
            if (!field) return;
            if (field instanceof RadioNodeList) {
                // e.g. a checkbox with its hidden "0" companion
                [...field].forEach((el) => { if (el.type === 'checkbox') el.checked = value === '1'; });
                return;
            }
            if (field.readOnly || field.value) return;
            field.value = value;
            field.dispatchEvent(new Event('change', { bubbles: true }));
        });
        this.$nextTick(() => refreshSelects(form));
    },
}));

/**
 * SOW B.04: count words in the uploaded .docx and fill the word-count field.
 * The server recounts on submit, so this is only a convenience.
 */
Alpine.data('docxWordCount', () => ({
    counting: false,
    error: null,
    async count(event, target) {
        const fileInput = event.target;
        const file = fileInput.files?.[0];
        const output = document.querySelector(target);
        this.error = null;
        delete fileInput.dataset.words;
        delete fileInput.dataset.unreadable;
        if (output) output.value = '';
        // Type, size and word range are reported by jQuery Validate (rules on the input).
        if (!file || !file.name.toLowerCase().endsWith('.docx')) return;
        this.counting = true;
        try {
            const words = await countDocxWords(file);
            fileInput.dataset.words = words;
            if (output) {
                output.value = words;
                output.dispatchEvent(new Event('input', { bubbles: true }));
            }
        } catch (e) {
            fileInput.dataset.unreadable = '1';
        } finally {
            this.counting = false;
            if (window.jQuery && fileInput.form && window.jQuery(fileInput.form).data('validator')) window.jQuery(fileInput).valid();
        }
    },
}));

/** Repeatable rows for JSON page metas (team members, patron entries, FAQs) and co-authors. */
// Repeatable rows. `min` rows are always kept (0 = the list may be empty, e.g. co-authors).
Alpine.data('repeater', (rows = [], blank = {}, min = 1) => ({
    rows: rows.length >= min ? rows : [...rows, ...Array.from({ length: min - rows.length }, () => ({ ...blank }))],
    add() {
        this.rows.push({ ...blank });
        this.$nextTick(() => {
            renderIcons();
            // Put the cursor in the new row.
            [...this.$root.querySelectorAll('input:not([type=hidden]), textarea')].pop()?.focus();
        });
    },
    remove(index) {
        this.rows.splice(index, 1);
        while (this.rows.length < min) this.rows.push({ ...blank });
    },
    // Drag rows by their [data-drag-handle] to reorder them (rows are [data-row] inside x-ref="list").
    init() {
        const list = this.$refs.list;
        if (!list) return;
        import('sortablejs').then(({ default: Sortable }) => {
            Sortable.create(list, {
                handle: '[data-drag-handle]',
                draggable: '[data-row]',
                animation: 150,
                ghostClass: 'menu-ghost',
                onEnd: ({ item, from, oldIndex, newIndex, oldDraggableIndex, newDraggableIndex }) => {
                    if (oldDraggableIndex === newDraggableIndex) return;
                    // Put the element back where Alpine left it, then reorder the data and let Alpine re-render.
                    from.insertBefore(item, from.children[oldIndex + (oldIndex > newIndex ? 1 : 0)] ?? null);
                    const [moved] = this.rows.splice(oldDraggableIndex, 1);
                    this.rows.splice(newDraggableIndex, 0, moved);
                },
            });
        });
    },
}));

export function renderIcons() {
    createIcons({ icons: usedIcons, attrs: { 'stroke-width': 1.5 } });
}

window.renderIcons = renderIcons;

document.addEventListener('DOMContentLoaded', () => {
    renderIcons();
    // After Alpine has applied x-model values, enhance selects and attach validation.
    initForms(document);

    const menuTree = document.querySelector('[data-menu-tree]');
    if (menuTree) import('./menu-builder').then(({ initMenuBuilder }) => initMenuBuilder(menuTree));

    // Admin full text editor (loaded only where used).
    if (document.querySelector('textarea[data-rich-editor]')) import('./rich-editor').then(({ initRichEditors }) => initRichEditors());
});
document.addEventListener('alpine:initialized', renderIcons);

// Block file attachments in the simple (author) editor; its attach button is hidden too.
document.addEventListener('trix-file-accept', (event) => event.preventDefault());

Alpine.start();
