/**
 * SMS Import with Dynamic Variables
 * Handles file upload, column detection, and variable insertion
 */

(function() {
    'use strict';

    // Global state
    let fileColumnsData = {
        headers: [],
        phoneColumn: null,
        variableColumns: [],
        fullData: [],
        previewData: []
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeFileImport();
    });

    function initializeFileImport() {
        const fileInput = document.getElementById('recipients_file');
        if (!fileInput) return;

        // Listen to file selection
        fileInput.addEventListener('change', handleFileSelect);
    }

    async function handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Show loading
        showLoading('Analyse du fichier en cours...');

        try {
            // Upload and parse file
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_csrf_token', window.CSRF_TOKEN || '');

            const baseUrl = window.APP_BASE_URL || '';
            const response = await fetch(baseUrl + '/admin/sms/parse-file', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Check if response is ok
            if (!response.ok) {
                const text = await response.text();
                console.error('Response status:', response.status);
                console.error('Response text:', text);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                fileColumnsData = result.data;
                displayColumnsInfo();
                displayVariableButtons();
                displayPreview();
                hideLoading();
            } else {
                throw new Error(result.message || 'Erreur lors de l\'analyse du fichier');
            }
        } catch (error) {
            hideLoading();
            showError('Erreur: ' + error.message);
        }
    }

    function displayColumnsInfo() {
        const container = document.getElementById('columns-info');
        if (!container) {
            // Create container if doesn't exist
            const fileGroup = document.querySelector('#recipients_file').closest('.form-group');
            const infoDiv = document.createElement('div');
            infoDiv.id = 'columns-info';
            infoDiv.className = 'alert alert-info mt-2';
            fileGroup.appendChild(infoDiv);
        }

        const infoContainer = document.getElementById('columns-info');
        infoContainer.innerHTML = `
            <h6><i class="fa fa-info-circle"></i> Informations du fichier</h6>
            <p class="mb-1"><strong>Nombre de lignes:</strong> ${fileColumnsData.total_rows}</p>
            <p class="mb-1"><strong>Colonne téléphone:</strong> <span class="badge bg-primary">${fileColumnsData.phone_column}</span></p>
            <p class="mb-0"><strong>Variables disponibles:</strong> ${fileColumnsData.variable_columns.length} colonnes</p>
        `;
    }

    function displayVariableButtons() {
        // Create variables container if doesn't exist
        let varsContainer = document.getElementById('variables-buttons');
        if (!varsContainer) {
            const messageGroup = document.querySelector('#message_file').closest('.form-group');
            const varsDiv = document.createElement('div');
            varsDiv.id = 'variables-buttons';
            varsDiv.className = 'card border-success mb-3';
            varsDiv.innerHTML = `
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fa fa-magic"></i> Variables disponibles (cliquez pour insérer)</h6>
                </div>
                <div class="card-body" id="variables-buttons-content">
                </div>
            `;
            messageGroup.insertBefore(varsDiv, messageGroup.firstChild);
        }

        const buttonsContent = document.getElementById('variables-buttons-content');
        if (!buttonsContent) return;

        // Generate variable buttons
        let buttonsHTML = '<div class="d-flex flex-wrap gap-2">';

        fileColumnsData.variable_columns.forEach(col => {
            buttonsHTML += `
                <button type="button" class="btn btn-sm btn-outline-success variable-btn" data-variable="${col}">
                    <i class="fa fa-plus-circle"></i> {{${col}}}
                </button>
            `;
        });

        buttonsHTML += '</div>';
        buttonsHTML += '<small class="text-muted d-block mt-2">💡 Cliquez sur une variable pour l\'insérer dans votre message</small>';

        buttonsContent.innerHTML = buttonsHTML;

        // Add click handlers
        document.querySelectorAll('.variable-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                insertVariable(this.dataset.variable);
            });
        });
    }

    function insertVariable(variableName) {
        const messageField = document.getElementById('message_file');
        if (!messageField) return;

        const cursorPos = messageField.selectionStart;
        const textBefore = messageField.value.substring(0, cursorPos);
        const textAfter = messageField.value.substring(cursorPos);

        const variableText = `{{${variableName}}}`;
        messageField.value = textBefore + variableText + textAfter;

        // Update cursor position
        const newCursorPos = cursorPos + variableText.length;
        messageField.setSelectionRange(newCursorPos, newCursorPos);
        messageField.focus();

        // Update character count
        const event = new Event('input', { bubbles: true });
        messageField.dispatchEvent(event);

        // Update preview
        updatePreview();

        // Visual feedback
        const btn = document.querySelector(`[data-variable="${variableName}"]`);
        if (btn) {
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-success');
            setTimeout(() => {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
            }, 300);
        }
    }

    function displayPreview() {
        // Create preview container if doesn't exist
        let previewContainer = document.getElementById('sms-preview');
        if (!previewContainer) {
            const varsButtons = document.getElementById('variables-buttons');
            if (!varsButtons) return;

            const previewDiv = document.createElement('div');
            previewDiv.id = 'sms-preview';
            previewDiv.className = 'card border-warning mb-3';
            previewDiv.innerHTML = `
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fa fa-eye"></i> Aperçu (3 premiers SMS)</h6>
                </div>
                <div class="card-body" id="sms-preview-content">
                    <p class="text-muted"><i>Composez votre message avec des variables pour voir l'aperçu</i></p>
                </div>
            `;
            varsButtons.parentNode.insertBefore(previewDiv, varsButtons.nextSibling);
        }

        // Listen to message changes
        const messageField = document.getElementById('message_file');
        if (messageField) {
            messageField.addEventListener('input', updatePreview);
        }
    }

    function updatePreview() {
        const messageField = document.getElementById('message_file');
        const previewContent = document.getElementById('sms-preview-content');

        if (!messageField || !previewContent || !fileColumnsData.preview_data) return;

        const messageTemplate = messageField.value.trim();

        if (!messageTemplate) {
            previewContent.innerHTML = '<p class="text-muted"><i>Composez votre message avec des variables pour voir l\'aperçu</i></p>';
            return;
        }

        let previewHTML = '';

        fileColumnsData.preview_data.forEach((row, index) => {
            let personalizedMessage = messageTemplate;

            // Replace variables
            for (const [key, value] of Object.entries(row)) {
                const regex = new RegExp(`\\{\\{${key}\\}\\}`, 'gi');
                personalizedMessage = personalizedMessage.replace(regex, value);
            }

            const phoneNumber = row[fileColumnsData.phone_column] || 'N/A';

            previewHTML += `
                <div class="alert alert-light border mb-2">
                    <small class="text-muted">→ ${phoneNumber}</small><br>
                    <strong>"${personalizedMessage}"</strong>
                </div>
            `;
        });

        previewContent.innerHTML = previewHTML;
    }

    function showLoading(message) {
        const fileGroup = document.querySelector('#recipients_file').closest('.form-group');
        let loadingDiv = document.getElementById('file-loading');

        if (!loadingDiv) {
            loadingDiv = document.createElement('div');
            loadingDiv.id = 'file-loading';
            loadingDiv.className = 'alert alert-info mt-2';
            fileGroup.appendChild(loadingDiv);
        }

        loadingDiv.innerHTML = `
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            ${message}
        `;
        loadingDiv.style.display = 'block';
    }

    function hideLoading() {
        const loadingDiv = document.getElementById('file-loading');
        if (loadingDiv) {
            loadingDiv.style.display = 'none';
        }
    }

    function showError(message) {
        alert(message);
    }

    // Form submission handler
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.querySelector('#recipients_file')) {
            // Add hidden fields with file data
            if (fileColumnsData.fullData && fileColumnsData.fullData.length > 0) {
                // Add hidden field for file_data
                let fileDataInput = form.querySelector('input[name="file_data"]');
                if (!fileDataInput) {
                    fileDataInput = document.createElement('input');
                    fileDataInput.type = 'hidden';
                    fileDataInput.name = 'file_data';
                    form.appendChild(fileDataInput);
                }
                fileDataInput.value = JSON.stringify(fileColumnsData.fullData);

                // Add hidden field for phone_column
                let phoneColInput = form.querySelector('input[name="phone_column"]');
                if (!phoneColInput) {
                    phoneColInput = document.createElement('input');
                    phoneColInput.type = 'hidden';
                    phoneColInput.name = 'phone_column';
                    form.appendChild(phoneColInput);
                }
                phoneColInput.value = fileColumnsData.phone_column;
            }
        }
    });

})();
