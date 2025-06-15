<x-layout>
    <x-slot:section>
        Sessions
        </x-slot>
        <!-- Main Content -->
        <div class="flex-grow flex flex-col items-center p-6 pt-20">
            <div class="w-full max-w-lg p-8 bg-white rounded-lg shadow-lg mt-6">
                <h2 class="text-2xl font-semibold text-gray-700 text-center mb-6">Create New Upload Session</h2>
                <!-- New Upload Session Form -->
                <form id="new-upload-session-form" class="space-y-4" method="POST">
                    @csrf
                    <div class="relative">
                        <!-- Description Field -->
                        <label for="description" class="block text-gray-700 font-medium mb-2">Description (Optional)</label>
                        <textarea id="description" name="description" rows="3" placeholder="Enter a description for this upload session..." class="w-full p-2 border border-gray-300 rounded-md resize-vertical focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div class="relative">
                        <!-- Session Expiration Dropdown -->
                        <label for="expires" class="block text-gray-700 font-medium mb-2">Session Expiration</label>
                        <select id="expires" name="expires" class="w-full p-2 border border-gray-300 rounded-md">
                            <option value="-1">Burn after upload</option>
                            <option value="3600">1 Hour</option>
                            <option value="86400">1 Day</option>
                            <option value="604800">7 Days</option>
                            <option value="2592000">30 Days</option>
                            <option value="0">Never</option>
                        </select>
                    </div>
                    <div class="relative">
                        <!-- Expiration Dropdown -->
                        <label for="upload_expires" class="block text-gray-700 font-medium mb-2">Upload Expiration</label>
                        <select id="upload_expires" name="upload_expires" class="w-full p-2 border border-gray-300 rounded-md">
                            <option value="-1">Burn after download</option>
                            <option value="3600">1 Hour</option>
                            <option value="86400">1 Day</option>
                            <option value="604800">7 Days</option>
                            <option value="2592000">30 Days</option>
                            <option value="0">Never</option>
                        </select>
                    </div>
                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-2 bg-blue-500 text-white rounded-md font-medium hover:bg-blue-600 transition duration-200">
                        Start Upload Session
                    </button>
                </form>
            </div>
        </div>
</x-layout>