<div class="space-y-6 animate-pulse">
    <!-- Header Skeleton -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="h-8 bg-gray-200 rounded w-48 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-64"></div>
            </div>
            <div class="mt-4 lg:mt-0">
                <div class="h-10 bg-gray-200 rounded w-32"></div>
            </div>
        </div>
        
        <!-- Stats Skeleton -->
        <div class="mt-6 grid grid-cols-2 md:grid-cols-5 gap-4">
            @for($i = 0; $i < 5; $i++)
            <div class="p-4 rounded-lg border bg-gray-50 border-gray-200">
                <div class="h-8 bg-gray-200 rounded w-12 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-16"></div>
            </div>
            @endfor
        </div>
    </div>

    <!-- Filters Skeleton -->
    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
        <div class="h-6 bg-gray-200 rounded w-32 mb-4"></div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="h-10 bg-gray-200 rounded"></div>
            <div class="h-10 bg-gray-200 rounded"></div>
            <div class="h-10 bg-gray-200 rounded"></div>
        </div>
    </div>

    <!-- Table Skeleton -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="h-6 bg-gray-200 rounded w-24"></div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @for($i = 0; $i < 8; $i++)
                        <th class="px-6 py-3">
                            <div class="h-4 bg-gray-200 rounded w-20"></div>
                        </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @for($i = 0; $i < 10; $i++)
                    <tr>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-4"></div></td>
                        <td class="px-6 py-4">
                            <div class="h-4 bg-gray-200 rounded w-48 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-32"></div>
                        </td>
                        <td class="px-6 py-4"><div class="h-6 bg-gray-200 rounded-full w-16"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-8"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-8"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-12"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
                        <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>