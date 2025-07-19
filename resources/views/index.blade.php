<x-layout>
    <x-slot:section>
        Upload
        </x-slot>
        <!-- Main Content -->

        <div class="flex-grow flex flex-col items-center p-6 pt-20">
            <!-- Expiration and Password Box -->
            <div class="w-full max-w-lg p-6 bg-white rounded-lg shadow-lg mb-4 mt-6">
                <h2 class="text-xl font-semibold text-gray-700 text-center mb-4">Settings</h2>

                <!-- Expiration Dropdown -->
                <label for="expiration" class="block text-gray-700 font-medium mb-2">Expiration</label>
                <select id="expiration" name="expiration" class="w-full p-2 border border-gray-300 rounded-md mb-4">
                    <option value="-1">Burn after download</option>
                    <option value="3600">1 Hour</option>
                    <option value="86400">1 Day</option>
                    <option value="604800">7 Days</option>
                    <option value="2592000">30 Days</option>
                    <option value="0">Never</option>
                </select>
                <!-- Local Encryption Checkbox (NEW) -->
                <div class="flex items-start space-x-2">
                    <input type="checkbox" id="local-encryption" name="local_encryption" class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded" checked>
                    <label for="local-encryption" class="text-gray-700 text-sm leading-5">
                        Encrypt locally before upload
                        <span class="block text-xs text-gray-500">When enabled, files are encrypted in your browser and only a decryption key (hash) is stored in the link you share.</span>
                    </label>
                </div>
            </div>

            <!-- Original Content -->
            <div class="w-full max-w-lg p-8 bg-white rounded-lg shadow-lg mt-4">
                <h2 class="text-2xl font-semibold text-gray-700 text-center mb-6">File Upload</h2>

                <!-- Drag and Drop Zone for File Upload -->
                <div
                    id="drop-zone"
                    class="drag-drop-zone flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-md bg-gray-50 text-gray-500 cursor-pointer">
                    <p class="text-center">Drag & Drop your files here, or <span class="text-blue-500 underline">click to upload</span></p>
                </div>

                <div id="content" class="mt-4 text-center text-gray-600"></div>
                <!-- List of uploaded files -->
                <ul id="file-list" class="mt-6 space-y-2 text-sm">
                    <!-- List items will be inserted dynamically -->
                </ul>
            </div>
        </div>

        <script>
            // Select elements
            const dropZone = document.getElementById("drop-zone");
            const fileList = document.getElementById("file-list");
            const contentElement = document.getElementById("content");
            const localEncryptionCheckbox = document.getElementById("local-encryption");

            //-- Encryption/Decryption --//

            var encryptWorker = new Worker("/js/encrypt-worker.js");
            var decryptWorker = new Worker("/js/decrypt-worker.js");

            function encryptData(plaintext, passphrase, callback) {
                var payload = {
                    plaintext: plaintext,
                    passphrase: passphrase
                }

                encryptWorker.onmessage = function(e) {
                    callback(e.data);
                }

                encryptWorker.postMessage(payload);
            }

            function decryptData(ciphertext, passphrase, callback) {
                var payload = {
                    ciphertext: ciphertext,
                    passphrase: passphrase
                }

                decryptWorker.onmessage = function(e) {
                    callback(e.data);
                }

                decryptWorker.postMessage(payload);
            }


            // Handle drag over and drag leave for drag and drop styling
            dropZone.addEventListener("dragover", (event) => {
                event.preventDefault();
                dropZone.classList.add("dragover");
            });

            dropZone.addEventListener("dragleave", () => {
                dropZone.classList.remove("dragover");
            });

            // Handle drop event
            dropZone.addEventListener("drop", (event) => {
                event.preventDefault();
                dropZone.classList.remove("dragover");

                // Get the dropped files
                const files = event.dataTransfer.files;
                handleFiles(files);
            });

            // Handle click to upload
            dropZone.addEventListener("click", () => {
                const fileInput = document.createElement("input");
                fileInput.type = "file";
                fileInput.multiple = true;
                fileInput.onchange = () => handleFiles(fileInput.files);
                fileInput.click();
            });

            // Handle files - Display the list of uploaded files
            function handleFiles(files) {
                console.log('Generating upload session');
                const sessionXhr = new XMLHttpRequest();

                sessionXhr.addEventListener("error", function() {
                    alert('Failed to generate upload session. Please try again later');
                });

                sessionXhr.addEventListener("load", function() {
                    if (sessionXhr.status >= 200 && sessionXhr.status < 300) {
                        console.log('Upload session generated');
                        const sessionData = JSON.parse(sessionXhr.responseText);
                        const uploadToken = sessionData.token; // Adjust property name based on your API response

                        // Now upload all files with the session token
                        Array.from(files).forEach(file => {
                            uploadFile(file, uploadToken);
                        });
                    } else {
                        alert('Failed to generate upload session. Please try again later');
                    }
                });

                // Generate upload session request
                sessionXhr.open('POST', '/api/session');
                sessionXhr.setRequestHeader('Content-Type', 'application/json');

                // Send session request with files metadata
                const sessionPayload = {
                    _token: "{{ csrf_token() }}",
                    expires: -1,
                    upload_expires: parseInt(document.getElementById('expiration').value),
                    // Add any other required session parameters
                };

                sessionXhr.send(JSON.stringify(sessionPayload));
            }

            function uploadFile(file, uploadToken) {
                const encryptionEnabled = localEncryptionCheckbox.checked;

                var max_size_mb = 128;
                if (file.size > (max_size_mb * 1024 * 1024)) {
                    alert('File larger than maximum supported size (' + max_size_mb + 'MB)');
                    return;
                }

                // Create the list item immediately when upload begins
                const listItem = document.createElement("li");
                listItem.className = "p-3 bg-gray-100 rounded-md flex flex-col space-y-1";

                // Top row: File info and status
                const topRow = document.createElement("div");
                topRow.className = "flex justify-between items-center";

                // File name with truncation
                const fileInfo = document.createElement("span");
                fileInfo.className = "font-medium truncate max-w-[60%]";
                fileInfo.title = file.name;
                fileInfo.textContent = `${file.name} (${(file.size / 1024).toFixed(2)} KB)`;

                // Status text
                const statusText = document.createElement("span");
                statusText.className = "text-xs text-gray-500";
                statusText.textContent = "Preparing...";

                topRow.appendChild(fileInfo);
                topRow.appendChild(statusText);

                // Progress bar
                const progressContainer = document.createElement("div");
                progressContainer.className = "w-full h-2 bg-gray-300 rounded-full overflow-hidden";
                progressContainer.style.display = "none";

                const progressBar = document.createElement("div");
                progressBar.className = "h-full bg-blue-500 transition-all duration-300";
                progressBar.style.width = "0%";

                progressContainer.appendChild(progressBar);

                // Download link row
                const linkRow = document.createElement("div");
                linkRow.className = "hidden text-xs text-blue-600 truncate max-w-full cursor-pointer";
                linkRow.title = ""; // Will set later

                listItem.appendChild(topRow);
                listItem.appendChild(progressContainer);
                listItem.appendChild(linkRow);
                fileList.appendChild(listItem);

                PasswordGenerator.symbols = false;
                var passphrase = PasswordGenerator.generate();

                let uploadFileItem = function(data, encrypted) {
                    console.log('Uploading file');
                    statusText.textContent = "Uploading...";
                    progressContainer.style.display = "block"; // Show progress bar

                    // Using XMLHttpRequest (closest to original jQuery implementation)
                    const xhr = new XMLHttpRequest();

                    // Set up progress tracking
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            var percentage = Math.round(percentComplete * 100);
                            console.log("Upload progress: " + percentage + "%");

                            // Update progress bar
                            progressBar.style.width = percentage + "%";
                            statusText.textContent = `Uploading... ${percentage}%`;
                        }
                    }, false);

                    // Set up error handler
                    xhr.addEventListener("error", function() {
                        statusText.textContent = "Upload failed";
                        statusText.className = "text-sm text-red-500";
                        progressContainer.style.display = "none";
                        alert('Upload failed. Please try again later');
                    });

                    // Set up success handler
                    xhr.addEventListener("load", function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            console.log('Upload complete');
                            const data = JSON.parse(xhr.responseText);

                            // Update status to show completion with ID and passphrase
                            statusText.textContent = "Complete";
                            statusText.className = "text-xs text-green-500";
                            progressContainer.style.display = "none";

                            let downloadUrl = `{{ config('app.url') }}/d/${data.id}`;
                            if (encrypted) {
                                downloadUrl += `#${passphrase}`;
                            }

                            // Show link
                            linkRow.textContent = downloadUrl;
                            linkRow.title = downloadUrl;
                            linkRow.classList.remove("hidden");

                            // Optional: Copy to clipboard on click
                            linkRow.addEventListener("click", () => {
                                navigator.clipboard.writeText(downloadUrl);
                                alert("Link copied to clipboard!");
                            });

                        } else {
                            statusText.textContent = "Upload failed";
                            statusText.className = "text-sm text-red-500";
                            progressContainer.style.display = "none";
                            alert('Upload failed. Please try again later');
                        }
                    });

                    xhr.open('PUT', '/' + file.name + '?json=1');
                    xhr.setRequestHeader('X-UPLOAD-SESSION-ID', uploadToken);

                    xhr.send(data);
                }

                if (encryptionEnabled) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        console.log('Encrypting file');
                        statusText.textContent = "Encrypting...";

                        var result = encryptData(e.target.result, passphrase, function(encrypted) {
                            if (encrypted.success != true) {
                                statusText.textContent = "Encryption failed";
                                statusText.className = "text-sm text-red-500";
                                alert('Failed to encrypt file data: ' + encrypted.data);
                                return;
                            }
                            console.log('File encrypted successfully');
                            uploadFileItem(encrypted.data, true);
                        });
                    };
                    reader.readAsDataURL(file);
                } else {
                    console.log('Uploading file without encryption');
                    uploadFileItem(file, false);
                }
            }

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

            var PasswordGenerator = {
                length: 12,
                lowercase: true,
                uppercase: true,
                numbers: true,
                symbols: true,
                setLength: function(length) {
                    this.length = length;
                },
                getRandom: function() {
                    var result = new Uint32Array(1);
                    window.crypto.getRandomValues(result);
                    return (result[0] / (0xffffffff + 1));
                },
                shuffleArray: function(array) {
                    for (var i = array.length - 1; i > 0; i--) {
                        var j = Math.floor(this.getRandom() * (i + 1));
                        var temp = array[i];
                        array[i] = array[j];
                        array[j] = temp;
                    }

                    return array;
                },
                generate: function() {

                    var lowerChars = "abcdefghijklmnopqrstuvwxyz";
                    var upperChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    var numberChars = "0123456789";
                    var symbolChars = "!$%^&*()_-?+=";

                    var allChars = ""
                    var randPasswordArray = Array(this.length);
                    var idx = 0;

                    function addChars(chars) {
                        allChars += chars;
                        randPasswordArray[idx] = chars;
                        idx++;
                    }

                    if (this.lowercase) {
                        addChars(lowerChars);
                    }

                    if (this.uppercase) {
                        addChars(upperChars);
                    }

                    if (this.numbers) {
                        addChars(numberChars);
                    }

                    if (this.symbols) {
                        addChars(symbolChars);
                    }

                    randPasswordArray = randPasswordArray.fill(allChars, idx);
                    var y = this;
                    return this.shuffleArray(randPasswordArray.map(function(x) {
                        return x[Math.floor(y.getRandom() * x.length)]
                    })).join('');
                }
            }
        </script>
        <script src="/js/progressbar.min.js"></script>
</x-layout>