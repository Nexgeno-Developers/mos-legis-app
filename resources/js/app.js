import Alpine from 'alpinejs';
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
    Save, ScanSearch, Search, Send, Settings, ShieldCheck,
    Shuffle, SquareUser, Star, Summary, Tag, Tags,
    Text, ToggleLeft, ToggleRight, Trash2, Type, Upload,
    User, UserCheck, UserPlus, Users, Volume,
} from 'lucide';
import { countDocxWords } from './word-count';

const usedIcons = { Activity, Archive, ArrowLeft, ArrowRight, Award, BadgeCheck, BadgeIndianRupee, Ban, BookOpen, Briefcase, CalendarRange, Check, ChevronDown, ChevronLeft, ChevronRight, CircleAlert, CircleCheck, Clock, Columns, Contact, Copy, CreditCard, Delete, Download, ExternalLink, Eye, EyeOff, File, FileText, Files, Filter, Folder, Form, Gauge, Gavel, Ghost, GitBranch, Heading, Home, Image, Inbox, Info, KeyRound, Layout, Lock, LogIn, LogOut, Mail, Menu, MessageSquare, Network, PanelLeft, Pencil, Phone, Plus, Receipt, RefreshCw, Reply, Route, Rows, Save, ScanSearch, Search, Send, Settings, ShieldCheck, Shuffle, SquareUser, Star, Summary, Tag, Tags, Text, ToggleLeft, ToggleRight, Trash2, Type, Upload, User, UserCheck, UserPlus, Users, Volume };

window.Alpine = Alpine;

/**
 * Reusable modal state: `x-data="modal(open)"`, `@click="show(record)"`.
 * The optional record is exposed as `form` so inputs can bind `x-model="form.name"`.
 */
Alpine.data('modal', (initiallyOpen = false, defaults = {}) => ({
    open: initiallyOpen,
    form: { ...defaults },
    show(record = {}) {
        this.form = { ...defaults, ...record };
        this.open = true;
        this.$nextTick(() => renderIcons());
    },
    hide() {
        this.open = false;
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
        const file = event.target.files?.[0];
        this.error = null;
        if (!file) return;
        if (!file.name.toLowerCase().endsWith('.docx')) {
            this.error = 'Please upload a .docx file.';
            return;
        }
        this.counting = true;
        try {
            const words = await countDocxWords(file);
            const input = document.querySelector(target);
            if (input) {
                input.value = words;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        } catch (e) {
            this.error = 'Could not read this document. Enter the word count manually.';
        } finally {
            this.counting = false;
        }
    },
}));

/** Repeatable rows for JSON page metas (team members, patron entries, FAQs) and co-authors. */
Alpine.data('repeater', (rows = [], blank = {}) => ({
    rows: rows.length ? rows : [{ ...blank }],
    add() {
        this.rows.push({ ...blank });
        this.$nextTick(() => renderIcons());
    },
    remove(index) {
        this.rows.splice(index, 1);
        if (!this.rows.length) this.rows.push({ ...blank });
    },
}));

export function renderIcons() {
    createIcons({ icons: usedIcons, attrs: { 'stroke-width': 1.5 } });
}

window.renderIcons = renderIcons;

document.addEventListener('DOMContentLoaded', renderIcons);
document.addEventListener('alpine:initialized', renderIcons);

// Block file attachments in Trix; images are managed through dedicated upload fields.
document.addEventListener('trix-file-accept', (event) => event.preventDefault());

Alpine.start();
