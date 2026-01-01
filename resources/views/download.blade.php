<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>send.lee.io</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .drag-drop-zone {
            transition: background-color 0.3s;
        }

        .drag-drop-zone.dragover {
            background-color: #e0f2fe;
        }
    </style>
</head>

<body class="flex flex-col min-h-screen bg-gray-100">
    <!-- Main Content -->

    <div class="flex-grow flex flex-col items-center p-6 pt-20">
        <!-- Download Box -->
        <div class="w-full max-w-lg p-8 bg-white rounded-lg shadow-lg mt-4">
            <h2 class="text-2xl font-semibold text-gray-700 text-center mb-6">File Download</h2>

            <!-- File Info Display -->
            <div id="file-info" class="mb-6 p-4 bg-gray-50 rounded-md" style="display: none;">
                <h3 class="text-lg font-medium text-gray-700 mb-2">File Information</h3>
                <p id="file-name" class="text-gray-600"></p>
                <p id="file-size" class="text-gray-600"></p>
            </div>

            <!-- Download Button -->
            <div class="text-center mb-4">
                <button
                    id="download-btn"
                    class="px-6 py-3 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled>
                    <span id="download-text">Loading...</span>
                </button>
            </div>

            <!-- Progress Bar -->
            <div id="progress-container" class="mb-4" style="display: none;">
                <div class="w-full h-3 bg-gray-300 rounded-full overflow-hidden">
                    <div id="progress-bar" class="h-full bg-blue-500 transition-all duration-300" style="width: 0%"></div>
                </div>
                <p id="progress-text" class="text-sm text-gray-600 text-center mt-2">0%</p>
            </div>

            <!-- Status Messages -->
            <div id="status-message" class="text-center text-sm"></div>
        </div>
    </div>

    <script>
        // Get file ID from URL path and passphrase from hash
        const pathParts = window.location.pathname.split('/');
        const fileId = pathParts[pathParts.length - 1];
        const passphrase = window.location.hash.substring(1); // Remove the # symbol

        // Select elements
        const downloadBtn = document.getElementById('download-btn');
        const downloadText = document.getElementById('download-text');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const statusMessage = document.getElementById('status-message');

        //-- Encryption/Decryption --//
        var decryptWorker = new Worker("/js/decrypt-worker.js");

        function decryptData(ciphertext, passphrase, callback) {
            if (!passphrase) {
                return ciphertext; // No passphrase, return as is
            }
            var payload = {
                ciphertext: ciphertext,
                passphrase: passphrase
            }

            decryptWorker.onmessage = function(e) {
                callback(e.data);
            }

            decryptWorker.postMessage(payload);
        }

        // Initialize page
        function init() {
            if (!fileId) {
                showError('No file ID provided in URL');
                return;
            }

            loadFileInfo();
        }

        // Load file information
        function loadFileInfo() {
            updateStatus('Loading file information...', 'text-blue-600');

            const xhr = new XMLHttpRequest();

            xhr.addEventListener('error', function() {
                showError('Failed to load file information');
            });

            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        displayFileInfo(data);
                        enableDownload();
                    } catch (e) {
                        showError('Invalid response from server');
                    }
                } else if (xhr.status === 404) {
                    showError('File not found or has expired');
                } else {
                    showError('Failed to load file information');
                }
            });

            xhr.open('GET', `/api/upload/${fileId}`);
            xhr.send();
        }

        // Display file information
        function displayFileInfo(data) {
            fileName.textContent = `Name: ${data.file_name || 'Unknown'}`;
            fileSize.textContent = `Uploaded Size: ${formatFileSize(data.size || 0)}`;

            fileInfo.style.display = 'block';
            updateStatus('Ready to download', 'text-green-600');
        }

        // Enable download button
        function enableDownload() {
            downloadBtn.disabled = false;
            downloadText.textContent = 'Download File';

            downloadBtn.addEventListener('click', startDownload);
        }

        // Start download process
        function startDownload() {
            downloadBtn.disabled = true;
            downloadText.textContent = 'Downloading...';
            progressContainer.style.display = 'block';
            updateStatus('Downloading encrypted file...', 'text-blue-600');

            const xhr = new XMLHttpRequest();

            // Set up progress tracking
            xhr.addEventListener('progress', function(evt) {
                if (evt.lengthComputable) {
                    const percentComplete = (evt.loaded / evt.total) * 100;
                    const percentage = Math.round(percentComplete);

                    progressBar.style.width = percentage + '%';
                    progressText.textContent = `Downloading: ${percentage}%`;
                }
            });

            xhr.addEventListener('error', function() {
                showError('Download failed');
                resetDownload();
            });

            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    if (!passphrase) {
                        // If no passphrase, save the file directly
                        saveBlob(xhr.response);
                        return;
                    }
                    updateStatus('Decrypting file...', 'text-blue-600');
                    progressText.textContent = 'Decrypting...';

                    try {
                        decryptAndSaveFile(xhr.responseText);
                    } catch (e) {
                        console(e);
                        showError('Failed to decrypt file');
                        resetDownload();
                    }
                } else if (xhr.status === 404) {
                    showError('File not found or has expired');
                    resetDownload();
                } else {
                    showError('Download failed');
                    resetDownload();
                }
            });
            if (!passphrase) {
                xhr.responseType = 'blob';
            } else {
                xhr.responseType = 'text';
            }
            xhr.open('GET', `/download/${fileId}`);
            xhr.send();
        }

        function saveBlob(blob) {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = getOriginalFileName();
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            updateStatus('Download completed successfully!', 'text-green-600');
            progressText.textContent = 'Complete';
            progressBar.style.width = '100%';
            setTimeout(resetDownload, 3000);
        }

        function saveFile(dataUrl) {
            const blob = dataUrlToBlob(dataUrl);
            saveBlob(blob);
        }

        // Decrypt and save file
        function decryptAndSaveFile(encryptedData) {
            decryptData(encryptedData, passphrase, function(decrypted) {
                if (decrypted.success !== true) {
                    showError('Failed to decrypt file: ' + (decrypted.data || 'Invalid passphrase'));
                    resetDownload();
                    return;
                }

                return saveFile(decrypted.data);
            });
        }

        // Get original filename from file info
        function getOriginalFileName() {
            const nameText = fileName.textContent;
            const match = nameText.match(/Name: (.+)/);
            return match ? match[1] : 'downloaded_file';
        }

        // Reset download state
        function resetDownload() {
            downloadBtn.disabled = false;
            downloadText.textContent = 'Download File';
            progressContainer.style.display = 'none';
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
        }

        // Show error message
        function showError(message) {
            updateStatus(message, 'text-red-600');
            downloadBtn.disabled = true;
            downloadText.textContent = 'Error';
        }

        // Update status message
        function updateStatus(message, className = '') {
            statusMessage.textContent = message;
            statusMessage.className = `text-center text-sm ${className}`;
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Convert data URL to blob (same as upload page)
        function dataUrlToBlob(strUrl) {
            var parts = strUrl.split(/[:;,]/),
                type = parts[1],
                decoder = parts[2] == "base64" ? atob : decodeURIComponent,
                binData = decoder(parts.pop()),
                mx = binData.length,
                i = 0,
                uiArr = new Uint8Array(mx);
            for (i; i < mx; ++i) uiArr[i] = binData.charCodeAt(i);
            return new Blob([uiArr], {
                type: type
            });
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', init);
    </script>
    <script src="/js/progressbar.min.js"></script>
</body>

</html>