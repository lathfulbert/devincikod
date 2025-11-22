<?php

/**
 * File Upload Demo
 * 
 * To make this work:
 * 1. Register the routes in your routes/web.php:
 * 
 * use App\Core\Files\Controllers\FileController;
 * 
 * $router->post('/api/files/upload', [FileController::class, 'upload']);
 * $router->get('/api/files/list', [FileController::class, 'list']);
 * $router->delete('/api/files/delete', [FileController::class, 'delete']);
 * 
 * 2. Access this file via your browser (e.g., create a route for it or include it in a view).
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager Demo</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .drop-zone {
            border: 2px dashed #ccc;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .drop-zone.dragover {
            background-color: #f0f8ff;
            border-color: #007bff;
        }

        .file-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }

        .file-item {
            border: 1px solid #eee;
            padding: 0.5rem;
            border-radius: 4px;
            text-align: center;
            position: relative;
        }

        .file-item img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }

        .file-name {
            font-size: 0.8rem;
            margin-top: 0.5rem;
            word-break: break-all;
        }

        .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .progress-bar {
            height: 5px;
            background: #eee;
            margin-top: 10px;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #28a745;
            width: 0%;
            transition: width 0.3s;
        }
    </style>
</head>

<body>
    <h1>File Manager</h1>

    <div class="drop-zone" id="dropZone">
        <p>Drag & Drop files here or click to select</p>
        <input type="file" id="fileInput" multiple style="display: none;">
    </div>
    <div id="uploadProgress" class="progress-bar" style="display: none;">
        <div class="progress-fill"></div>
    </div>

    <h2>Uploaded Files</h2>
    <div class="file-list" id="fileList">
        <!-- Files will be loaded here -->
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const progressBar = document.getElementById('uploadProgress');
        const progressFill = progressBar.querySelector('.progress-fill');

        // API Endpoints (Adjust if needed)
        const API_URL = '/api/files';

        // Load files on start
        loadFiles();

        // Event Listeners
        dropZone.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', handleFiles);

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                uploadFiles(e.dataTransfer.files);
            }
        });

        function handleFiles() {
            if (fileInput.files.length) {
                uploadFiles(fileInput.files);
            }
        }

        async function uploadFiles(files) {
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }

            progressBar.style.display = 'block';
            progressFill.style.width = '0%';

            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', `${API_URL}/upload`);

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const percent = (e.loaded / e.total) * 100;
                        progressFill.style.width = percent + '%';
                    }
                };

                xhr.onload = () => {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        console.log('Upload result:', response);
                        loadFiles(); // Refresh list
                    } else {
                        alert('Upload failed');
                    }
                    setTimeout(() => progressBar.style.display = 'none', 1000);
                };

                xhr.send(formData);
            } catch (error) {
                console.error('Error:', error);
                alert('Upload error');
            }
        }

        async function loadFiles() {
            try {
                const response = await fetch(`${API_URL}/list`);
                const data = await response.json();

                fileList.innerHTML = '';
                if (data.files) {
                    data.files.forEach(file => {
                        const div = document.createElement('div');
                        div.className = 'file-item';

                        // Check if image for thumbnail
                        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(file.name.split('.').pop().toLowerCase());
                        const preview = isImage ? `<img src="${file.url}" alt="${file.name}">` : '📄';

                        div.innerHTML = `
                            <button class="delete-btn" onclick="deleteFile('${file.path}')">×</button>
                            ${preview}
                            <div class="file-name"><a href="${file.url}" target="_blank">${file.name}</a></div>
                        `;
                        fileList.appendChild(div);
                    });
                }
            } catch (error) {
                console.error('Load error:', error);
            }
        }

        async function deleteFile(path) {
            if (!confirm('Delete this file?')) return;

            try {
                const response = await fetch(`${API_URL}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        path: path
                    })
                });

                if (response.ok) {
                    loadFiles();
                } else {
                    alert('Delete failed');
                }
            } catch (error) {
                console.error('Delete error:', error);
            }
        }
    </script>
</body>

</html>