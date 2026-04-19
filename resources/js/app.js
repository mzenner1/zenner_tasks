import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';

/**
 * Boot every <textarea data-easymde> on the page.
 * data-image-upload-url  – endpoint that accepts POST {image} and returns {url}
 * data-csrf              – CSRF token for the upload request
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea[data-easymde]').forEach(el => {
        const uploadUrl = el.dataset.imageUploadUrl;
        const csrf      = el.dataset.csrf;

        const mde = new EasyMDE({
            element: el,
            spellChecker: false,
            autosave: { enabled: false },
            toolbar: [
                'bold','italic','heading','|',
                'quote','unordered-list','ordered-list','|',
                'link','image','|',
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

        // Keep the hidden textarea in sync so the form submits the markdown value
        mde.codemirror.on('change', () => { el.value = mde.value(); });
    });
});
