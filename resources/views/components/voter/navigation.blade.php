@props(['user'])

<div class="text-gray-800 dark:text-gray-200">
    <!-- Desktop Sidebar -->
    <aside class="hidden md:flex flex-col w-64 h-screen px-4 py-8 bg-white border-r dark:bg-gray-900 dark:border-gray-700">
        <a href="{{ route('voter.dashboard') }}" class="flex items-center space-x-2">
            <span class="text-2xl font-bold text-gray-800 dark:text-white">Echara Youth</span>
        </a>

        <div class="flex flex-col justify-between flex-1 mt-6">
            <nav>
                <a href="{{ route('voter.dashboard') }}"
                   class="flex items-center px-4 py-2 mt-5 text-gray-600 transition-colors duration-300 transform rounded-md dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700 {{ request()->routeIs('voter.dashboard') ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200' : '' }}">
                    <span class="mx-4 font-medium">Dashboard</span>
                </a>
                <a href="{{ route('voter.elections') }}"
                   class="flex items-center px-4 py-2 mt-5 text-gray-600 transition-colors duration-300 transform rounded-md dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700 {{ request()->routeIs('voter.elections') ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200' : '' }}">
                    <span class="mx-4 font-medium">Elections</span>
                </a>
                <a href="{{ route('voter.history') }}"
                   class="flex items-center px-4 py-2 mt-5 text-gray-600 transition-colors duration-300 transform rounded-md dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700 {{ request()->routeIs('voter.history') ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200' : '' }}">
                    <span class="mx-4 font-medium">History</span>
                </a>
                <a href="{{ route('voter.profile') }}"
                   class="flex items-center px-4 py-2 mt-5 text-gray-600 transition-colors duration-300 transform rounded-md dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700 {{ request()->routeIs('voter.profile') ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200' : '' }}">
                    <span class="mx-4 font-medium">Profile</span>
                </a>
            </nav>

            <div class="flex items-center px-4 -mx-2">
                <img class="object-cover w-10 h-10 mx-2 rounded-full" src="{{ Auth::user()->profile_image_url ?? 'https://via.placeholder.com/40' }}" alt="avatar">
                <div class="mx-2">
                    <h4 class="font-medium text-gray-800 dark:text-gray-200">{{ Auth::user()->full_name }}</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Voter</p>
                </div>
            </div>

            <!-- Logout Form -->
            <form method="POST" action="{{ route('voter.logout') }}" class="mt-4">
                @csrf
                <button type="submit"
                        class="flex items-center w-full px-4 py-2 text-gray-600 transition-colors duration-300 transform rounded-md dark:text-gray-400 hover:bg-red-100 dark:hover:bg-red-800 dark:hover:text-gray-200 hover:text-red-700">
                    <span class="mx-4 font-medium">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile Bottom Nav -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t dark:bg-gray-900 dark:border-gray-700 md:hidden">
        <div class="flex justify-around">
            <a href="{{ route('voter.dashboard') }}"
               class="flex flex-col items-center justify-center flex-1 px-2 py-3 text-center text-gray-600 transition-colors duration-300 transform rounded-md dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('voter.dashboard') ? 'text-indigo-500 dark:text-indigo-400' : '' }}">
                <span class="text-xs font-medium">Dashboard</span>
            </a>
            <a href="{{ route('voter.elections') }}"
               class="flex flex-col items-center justify-center flex-1 px-2 py-3 text-center text-gray-600 transition-colors duration-300 transform rounded-md dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('voter.elections') ? 'text-indigo-500 dark:text-indigo-400' : '' }}">
                <span class="text-xs font-medium">Elections</span>
            </a>
            <a href="{{ route('voter.history') }}"
               class="flex flex-col items-center justify-center flex-1 px-2 py-3 text-center text-gray-600 transition-colors duration-300 transform rounded-md dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('voter.history') ? 'text-indigo-500 dark:text-indigo-400' : '' }}">
                <span class="text-xs font-medium">History</span>
            </a>
            <a href="{{ route('voter.profile') }}"
               class="flex flex-col items-center justify-center flex-1 px-2 py-3 text-center text-gray-600 transition-colors duration-300 transform rounded-md dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('voter.profile') ? 'text-indigo-500 dark:text-indigo-400' : '' }}">
                <span class="text-xs font-medium">Profile</span>
            </a>
            <form id="logout-form-mobile" action="{{ route('voter.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <a href="{{ route('voter.logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();"
               class="flex flex-col items-center justify-center flex-1 px-2 py-3 text-center text-gray-600 transition-colors duration-300 transform rounded-md dark:text-gray-400 hover:bg-red-100 dark:hover:bg-red-800 hover:text-red-700">
                <span class="text-xs font-medium">Logout</span>
            </a>
        </div>
    </nav>
</div>