<header class="border-b border-gray-100">
    <div class="max-w-5xl mx-auto px-8 py-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-light text-gray-900 tracking-tight">{{ $election->title }}</h1>
                <p class="text-gray-500 mt-1">Digital Ballot • {{ now()->format('F j, Y') }}</p>
            </div>
            <div class="text-right">
                <div class="text-xs uppercase tracking-widest text-gray-400 mb-1">Session</div>
                <div class="font-mono text-sm text-gray-600">{{ substr($sessionId, 0, 12) }}</div>
            </div>
        </div>
    </div>
</header>