<div class="mb-16">
    <div class="flex items-center justify-between mb-4">
        <span class="text-sm font-medium text-gray-900">{{ $completed }} of {{ $total }} completed</span>
        <span class="text-sm text-gray-500">{{ $percentage }}%</span>
    </div>
    <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
        <div class="h-full bg-gray-900 transition-all duration-1000 ease-out" style="width: {{ $percentage }}%"></div>
    </div>
</div>