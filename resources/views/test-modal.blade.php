<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-900 text-white p-8">
    <div x-data="{ open: false }">
        <button @click="open = true" class="px-4 py-2 bg-blue-600 rounded">
            Open Modal
        </button>

        <template x-if="open">
            <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-50" @click.self="open = false">
                <div class="bg-gray-800 p-8 rounded-lg max-w-md" @click.stop>
                    <h2 class="text-2xl mb-4">Test Modal</h2>
                    <p>If you can see this, Alpine.js x-if works!</p>
                    <button @click="open = false" class="mt-4 px-4 py-2 bg-red-600 rounded">
                        Close
                    </button>
                </div>
            </div>
        </template>

        <div x-show="open" style="display: none;">
            <p class="mt-4">X-show test: You should see this when modal is open</p>
        </div>
    </div>
</body>
</html>
