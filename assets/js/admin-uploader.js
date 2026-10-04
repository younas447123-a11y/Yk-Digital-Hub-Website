(function () {
    'use strict';

    function initAll() {
        document.querySelectorAll('[data-uploader]').forEach(initUploader);
    }

    function initUploader(root) {
        if (root.dataset.initialized) return;
        root.dataset.initialized = '1';

        var hidden    = root.querySelector('[data-hidden]');
        var fileInput = root.querySelector('[data-file-input]');
        var preview   = root.querySelector('[data-preview]');
        var uploadBtn = root.querySelector('[data-upload-btn]');
        var removeBtn = root.querySelector('[data-remove-btn]');
        var textInput = root.querySelector('[data-text-input]');
        var folder    = root.dataset.folder || 'general';
        var uploadUrl = root.dataset.uploadUrl;
        var csrf      = root.dataset.csrf;

        // Sync manual text input
        if (textInput) {
            textInput.addEventListener('input', function () {
                var v = textInput.value.trim();
                hidden.value = v;
                updatePreview(v);
            });
        }

        uploadBtn.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function () {
            if (!fileInput.files.length) return;
            doUpload(fileInput.files[0]);
        });

        removeBtn.addEventListener('click', function () {
            hidden.value = '';
            if (textInput) textInput.value = '';
            fileInput.value = '';
            updatePreview('');
        });

        function doUpload(file) {
            // Size check (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File too large. Maximum 5 MB.');
                fileInput.value = '';
                return;
            }

            var fd = new FormData();
            fd.append('file', file);
            fd.append('folder', folder);
            fd.append('csrf_token', csrf);

            var originalHtml = uploadBtn.innerHTML;
            uploadBtn.disabled = true;
            uploadBtn.style.opacity = '0.6';
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

            fetch(uploadUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) {
                    return r.text().then(function (body) {
                        var data;
                        try {
                            data = JSON.parse(body);
                        } catch (parseError) {
                            throw new Error('The upload server returned an unexpected response. Please check your admin session and try again.');
                        }
                        if (!r.ok && !data.error) {
                            data.error = 'Upload failed with HTTP ' + r.status + '.';
                        }
                        return data;
                    });
                })
                .then(function (data) {
                    if (!data.success) {
                        alert('Upload failed: ' + (data.error || 'Unknown error'));
                        return;
                    }
                    hidden.value = data.path;
                    if (textInput) textInput.value = data.path;
                    updatePreview(data.path);
                })
                .catch(function (err) {
                    alert('Upload error: ' + err.message);
                })
                .finally(function () {
                    uploadBtn.disabled = false;
                    uploadBtn.style.opacity = '1';
                    uploadBtn.innerHTML = originalHtml;
                    fileInput.value = '';
                });
        }

        function updatePreview(path) {
            if (path) {
                preview.innerHTML = '<img src="' + path + '" alt="Preview" style="max-width:100%; max-height:220px; border-radius:6px; display:block;">';
                removeBtn.style.display = 'inline-flex';
            } else {
                preview.innerHTML = '<div style="color:#94a3b8; font-family:Inter,sans-serif; font-size:14px; padding:24px 0; text-align:center;">'
                                  + '<i class="fas fa-image" style="font-size:32px; display:block; margin-bottom:8px; opacity:.5;"></i>'
                                  + 'No image selected</div>';
                removeBtn.style.display = 'none';
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    // Expose for dynamically-injected forms (like _children.php inline add/edit forms)
    window.ykInitUploaders = initAll;
})();