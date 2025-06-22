<x-layout>
    <x-slot:section>
        Sessions
        </x-slot>
        <!-- Main Content -->
        <div class="flex-grow flex flex-col items-center p-6 pt-20">

            <!-- Session Messages -->
            @if(session('success'))
            <div class="w-full max-w-4xl mb-6">
                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="w-full max-w-4xl mb-6">
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(session('warning'))
            <div class="w-full max-w-4xl mb-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">
                                {{ session('warning') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(session('info'))
            <div class="w-full max-w-4xl mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-800">
                                {{ session('info') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($sessions) && $sessions->count() > 0)
            <div class="w-full max-w-4xl bg-white rounded-lg shadow-lg overflow-hidden mt-6">
                <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-700">Existing Upload Sessions</h3>
                    <!-- New Session Button -->
                    <a href="/sessions/new" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md font-medium hover:bg-blue-600 transition duration-200 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Session
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session Expiration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload Expiration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sessions as $uploadSession)
                            <tr class="hover:bg-gray-50 cursor-pointer transition duration-200" onclick="window.location.href='/sessions/{{ $uploadSession->id }}'">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 hover:text-blue-800">
                                    {{ $uploadSession->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $uploadSession->description ?: 'No description' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($uploadSession->expires == -1)
                                    Burn after upload
                                    @elseif($uploadSession->expires == 0)
                                    Never
                                    @else
                                    {{ \Carbon\Carbon::createFromTimestamp($uploadSession->expires)->format('M j, Y g:i A') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($uploadSession->upload_expires == -1)
                                    Burn after download
                                    @elseif($uploadSession->upload_expires == 0)
                                    Never
                                    @else
                                    {{ \Carbon\Carbon::createFromTimestamp($uploadSession->upload_expires)->format('M j, Y g:i A') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $uploadSession->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form method="POST" action="/sessions/{{ $uploadSession->id }}/delete" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this upload session?');" onclick="event.stopPropagation();">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-900 transition duration-200">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="w-full max-w-4xl bg-white rounded-lg shadow-lg p-6 mt-6">
                <div class="text-center">
                    <div class="text-gray-500 mb-4">
                        <p>No upload sessions found.</p>
                    </div>
                    <!-- New Session Button for empty state -->
                    <a href="/sessions/new" class="inline-flex items-center px-6 py-3 bg-blue-500 text-white rounded-md font-medium hover:bg-blue-600 transition duration-200 shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Your First Session
                    </a>
                </div>
            </div>
            @endif
        </div>
</x-layout>