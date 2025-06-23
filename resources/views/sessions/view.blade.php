<x-layout>
    <x-slot:section>
        Upload Session Details
        </x-slot>
        <!-- Main Content -->
        <div class="flex-grow flex flex-col items-center p-6 pt-20">

            <!-- Back Button -->
            <div class="w-full max-w-2xl mb-6">
                <a href="/sessions" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Sessions
                </a>
            </div>

            <!-- Session Details Card -->
            <div class="w-full max-w-2xl bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-700">Upload Session: {{ $uploadSession->id }}</h2>

                    <!-- Delete Button -->
                    <form method="POST" action="/sessions/{{ $uploadSession->id }}/delete" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this upload session? This action cannot be undone.');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded-md font-medium hover:bg-red-600 transition duration-200 shadow-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>

                <div class="px-6 py-6 space-y-6">
                    <!-- Description -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Description</h3>
                        <p class="text-gray-900">{{ $uploadSession->description ?: 'No description provided' }}</p>
                    </div>

                    <!-- Upload Session Expiry -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Upload Session Expiry</h3>
                        <p class="text-gray-900">
                            @if($uploadSession->expires == -1)
                            <span class="text-orange-600 font-medium">Burn after upload</span>
                            <span class="text-sm text-gray-500 block">Session will be deleted immediately after first upload</span>
                            @elseif($uploadSession->expires == 0)
                            <span class="text-green-600 font-medium">Never expires</span>
                            <span class="text-sm text-gray-500 block">Session will remain active indefinitely</span>
                            @else
                            <span class="font-medium">{{ \Carbon\Carbon::createFromTimestamp($uploadSession->expires)->format('M j, Y g:i A') }}</span>
                            <span class="text-sm text-gray-500 block">
                                {{ \Carbon\Carbon::createFromTimestamp($uploadSession->expires)->diffForHumans() }}
                            </span>
                            @endif
                        </p>
                    </div>

                    <!-- Configured Upload Expiry -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Configured Upload Expiry</h3>
                        <p class="text-gray-900">
                            @if($uploadSession->upload_expires == -1)
                            <span class="text-orange-600 font-medium">Burn after download</span>
                            <span class="text-sm text-gray-500 block">Uploaded files will be deleted after first download</span>
                            @elseif($uploadSession->upload_expires == 0)
                            <span class="text-green-600 font-medium">Never expires</span>
                            <span class="text-sm text-gray-500 block">Uploaded files will remain available indefinitely</span>
                            @else
                            <span class="font-medium">{{ \Carbon\Carbon::createFromTimestamp($uploadSession->upload_expires)->format('M j, Y g:i A') }}</span>
                            <span class="text-sm text-gray-500 block">
                                Uploaded files will expire {{ \Carbon\Carbon::createFromTimestamp($uploadSession->upload_expires)->diffForHumans() }}
                            </span>
                            @endif
                        </p>
                    </div>

                    <!-- Created Date -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Created</h3>
                        <p class="text-gray-900">
                            <span class="font-medium">{{ $uploadSession->created_at->format('M j, Y g:i A') }}</span>
                            <span class="text-sm text-gray-500 block">{{ $uploadSession->created_at->diffForHumans() }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Upload Instructions Card -->
            <div class="w-full max-w-2xl mt-6 bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-blue-50 border-b">
                    <h3 class="text-lg font-semibold text-blue-900">Upload Instructions</h3>
                    <p class="text-sm text-blue-700 mt-1">Use the following command to upload files to this session</p>
                </div>

                <div class="px-6 py-6">
                    <!-- cURL Command -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">cURL Command:</label>
                        <div class="relative">
                            <code class="block w-full p-3 bg-gray-100 border border-gray-300 rounded-md text-sm font-mono break-all whitespace-pre-wrap" id="curl-command">curl -H'X-UPLOAD-SESSION-ID: {{ $uploadSession->token }}' --upload-file myfile.sh https://send.lee.io</code>
                            <button onclick="copyToClipboard('curl-command')" class="absolute top-2 right-2 px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-50 transition duration-200">
                                Copy
                            </button>
                        </div>
                    </div>

                    <!-- Usage Notes -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-yellow-800">Usage Notes:</h4>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Replace <code class="bg-yellow-100 px-1 rounded">myfile.sh</code> with the path to your actual file</li>
                                        <li>The session ID header is required for all uploads</li>
                                        <li>Each upload will return a unique download link</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- JavaScript for Copy Functionality -->
        <script>
            function copyToClipboard(elementId) {
                const element = document.getElementById(elementId);
                const text = element.textContent;

                navigator.clipboard.writeText(text).then(function() {
                    // Show success feedback
                    const button = element.parentNode.querySelector('button');
                    const originalText = button.textContent;
                    button.textContent = 'Copied!';
                    button.classList.add('bg-green-100', 'text-green-800');

                    setTimeout(function() {
                        button.textContent = originalText;
                        button.classList.remove('bg-green-100', 'text-green-800');
                    }, 2000);
                }).catch(function(err) {
                    console.error('Could not copy text: ', err);
                });
            }
        </script>
</x-layout>