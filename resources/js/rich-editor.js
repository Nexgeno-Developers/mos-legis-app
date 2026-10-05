// Full rich-text editor for the admin panel (Jodit). Loaded on demand, only on pages that use it.
import { Jodit } from 'jodit';
// The package entry loads only core plugins; load all of them (indent, line height, source view, full screen…).
import 'jodit/esm/plugins/all.js';
import 'jodit/es2021/jodit.min.css';

const BUTTONS = [
    'bold', 'italic', 'underline', 'strikethrough', '|',
    'paragraph', 'fontsize', 'brush', '|',
    'ul', 'ol', 'outdent', 'indent', '|',
    'align', 'lineHeight', '|',
    'link', 'image', 'table', 'hr', 'symbols', '|',
    'superscript', 'subscript', 'eraser', 'copyformat', '|',
    'undo', 'redo', '|',
    'source', 'fullsize',
];

export function initRichEditors(root = document) {
    root.querySelectorAll('textarea[data-rich-editor]:not([data-ready])').forEach((textarea) => {
        textarea.dataset.ready = '1';
        const input = document.getElementById(textarea.dataset.input);

        const editor = Jodit.make(textarea, {
            minHeight: 360,
            height: 520,
            buttons: BUTTONS,
            buttonsMD: BUTTONS,
            buttonsSM: ['bold', 'italic', 'underline', '|', 'paragraph', 'ul', 'ol', '|', 'link', 'image', 'table', '|', 'dots'],
            buttonsXS: ['bold', 'italic', '|', 'paragraph', 'ul', '|', 'link', 'image', '|', 'dots'],
            editorClassName: 'prose-legis',
            placeholder: '',
            showCharsCounter: false,
            showXPathInStatusbar: false,
            askBeforePasteHTML: false,
            askBeforePasteFromWord: false,
            defaultActionOnPaste: 'insert_clear_html',
            disablePlugins: ['ai-assistant', 'speech-recognize', 'powered-by-jodit', 'video', 'file', 'media', 'iframe', 'print'],
            link: { noFollowCheckbox: false, modeClassName: false },
            image: { editSrc: true, editTitle: true, editAlt: true, useImageEditor: false },
            uploader: {
                url: textarea.dataset.uploadUrl,
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                insertImageAsBase64URI: false,
                imagesExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            },
        });

        // Keep the posted hidden input in sync (and let form validation react).
        editor.events.on('change', (value) => {
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });
}
