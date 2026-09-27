<script>
    (() => {
        'use strict';

        const normalizeAlertType = (type) => {
            const allowedTypes = [
                'success',
                'error',
                'warning',
                'info',
                'question',
            ];

            const normalizedType = type === 'danger'
                ? 'error'
                : String(type || 'success').toLowerCase();

            return allowedTypes.includes(normalizedType)
                ? normalizedType
                : 'info';
        };

        window.showAlert = (message, type = 'success', options = {}) => {
            if (!window.Swal || typeof window.Swal.fire !== 'function') {
                console.warn('SweetAlert2 is not loaded.');

                return Promise.resolve();
            }

            const normalizedType = normalizeAlertType(type);

            const text = Array.isArray(message)
                ? message.join('\n')
                : String(message ?? '');

            if (!text.trim()) {
                return Promise.resolve();
            }

            return window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: normalizedType,
                title: text,
                showConfirmButton: false,
                timer: normalizedType === 'error' ? 5000 : 3500,
                timerProgressBar: true,
                allowEscapeKey: true,
                showCloseButton: true,

                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', window.Swal.stopTimer);
                    toast.addEventListener('mouseleave', window.Swal.resumeTimer);
                },

                ...options,
            });
        };

        window.showConfirm = (options = {}) => {
            if (!window.Swal || typeof window.Swal.fire !== 'function') {
                return Promise.resolve({
                    isConfirmed: false,
                });
            }

            return window.Swal.fire({
                title: options.title || 'Are you sure?',
                text: options.text || 'This action may not be reversible.',
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: options.confirmButtonText || 'Yes, continue',
                cancelButtonText: options.cancelButtonText || 'Cancel',
                reverseButtons: true,
                focusCancel: true,
            });
        };

        window.initializeTinyMce = (root = document) => {
            if (!window.tinymce) {
                console.warn('TinyMCE is not loaded.');

                return;
            }

            const editors = root.querySelectorAll(
                'textarea.tinymce-editor:not([data-tinymce-initialized="true"])'
            );

            editors.forEach((textarea) => {
                textarea.dataset.tinymceInitialized = 'true';

                window.tinymce.init({
                    target: textarea,

                    /*
                     * TinyMCE 7-এর self-hosted/GPL distribution।
                     * Commercial project হলে licence requirement যাচাই করুন।
                     */
                    license_key: 'gpl',

                    height: Number(textarea.dataset.editorHeight || 450),

                    menubar: true,
                    branding: false,
                    promotion: false,

                    plugins: [
                        'advlist',
                        'autolink',
                        'lists',
                        'link',
                        'image',
                        'charmap',
                        'preview',
                        'anchor',
                        'searchreplace',
                        'visualblocks',
                        'code',
                        'fullscreen',
                        'insertdatetime',
                        'media',
                        'table',
                        'wordcount',
                        'autoresize',
                    ].join(' '),

                    toolbar: [
                        'undo redo',
                        'blocks',
                        'bold italic underline strikethrough',
                        'forecolor backcolor',
                        'alignleft aligncenter alignright alignjustify',
                        'bullist numlist outdent indent',
                        'link image media table',
                        'removeformat code preview fullscreen',
                    ].join(' | '),

                    toolbar_mode: 'sliding',

                    content_style: `
                        body {
                            font-family: Arial, sans-serif;
                            font-size: 16px;
                            line-height: 1.6;
                        }

                        img {
                            max-width: 100%;
                            height: auto;
                        }
                    `,

                    /*
                     * Base64 image database-এ save হওয়া বন্ধ থাকবে।
                     * Image upload Spatie Media Library দিয়ে handle করতে হবে।
                     */
                    paste_data_images: false,
                    automatic_uploads: false,

                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: true,

                    setup: (editor) => {
                        editor.on('change input undo redo', () => {
                            editor.save();
                        });

                        editor.on('remove', () => {
                            delete textarea.dataset.tinymceInitialized;
                        });
                    },
                });
            });
        };

        const initializeGlobalAdminFeatures = () => {
            window.initializeTinyMce(document);

            const forms = document.querySelectorAll('form');

            forms.forEach((form) => {
                if (form.dataset.tinyMceSubmitListener === 'true') {
                    return;
                }

                form.dataset.tinyMceSubmitListener = 'true';

                form.addEventListener('submit', () => {
                    if (window.tinymce) {
                        window.tinymce.triggerSave();
                    }
                });
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            const flashMessages = [
                {
                    message: @json(session('success')),
                    type: 'success',
                },
                {
                    message: @json(session('error')),
                    type: 'error',
                },
                {
                    message: @json(session('warning')),
                    type: 'warning',
                },
                {
                    message: @json(session('info')),
                    type: 'info',
                },
                {
                    message: @json(session('status')),
                    type: 'success',
                },
            ];

            const flash = flashMessages.find((item) => {
                return item.message !== null
                    && item.message !== undefined
                    && String(item.message).trim() !== '';
            });

            const validationErrors = @json($errors->all());

            if (validationErrors.length > 0) {
                window.showAlert(validationErrors, 'error');
            } else if (flash) {
                window.showAlert(flash.message, flash.type);
            }

            initializeGlobalAdminFeatures();
        });

        /*
         * AJAX/modal দিয়ে নতুন textarea যোগ করার পর:
         *
         * document.dispatchEvent(new CustomEvent('admin:content-updated'));
         */
        document.addEventListener('admin:content-updated', () => {
            initializeGlobalAdminFeatures();
        });
    })();
</script>