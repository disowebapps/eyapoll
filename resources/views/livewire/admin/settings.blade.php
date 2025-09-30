<div class="space-y-6" x-data="{ activeTab: 'general' }">
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="flex flex-wrap gap-1 sm:gap-2 px-4 sm:px-6 overflow-x-auto" aria-label="Tabs">
                <button @click="activeTab = 'general'" 
                    class="py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors whitespace-nowrap" 
                    :class="activeTab === 'general' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <svg class="w-5 h-5 sm:w-5 sm:h-5 text-blue-600 border border-blue-300 rounded p-0.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z"/>
                            <path stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="hidden sm:inline">General</span>
                    </div>
                </button>
                <button @click="activeTab = 'election'" 
                    class="py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors whitespace-nowrap" 
                    :class="activeTab === 'election' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <svg class="w-5 h-5 sm:w-5 sm:h-5 text-blue-600 border border-blue-300 rounded p-0.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M7.864 4.243A7.5 7.5 0 0119.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 004.5 10.5a7.464 7.464 0 01-1.15 3.993m1.989 3.559A11.209 11.209 0 008.25 10.5a3.75 3.75 0 117.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a14.94 14.94 0 01-3.6 9.75m6.633-4.596a18.666 18.666 0 01-2.485 5.33"/>
                        </svg>
                        <span class="hidden sm:inline">Election</span>
                    </div>
                </button>
                <button @click="activeTab = 'security'" 
                    class="py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors whitespace-nowrap" 
                    :class="activeTab === 'security' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <svg class="w-5 h-5 sm:w-5 sm:h-5 text-blue-600 border border-blue-300 rounded p-0.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                        </svg>
                        <span class="hidden sm:inline">Security</span>
                    </div>
                </button>
                <button @click="activeTab = 'notifications'"
                    class="py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors whitespace-nowrap" 
                    :class="activeTab === 'notifications' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <svg class="w-5 h-5 sm:w-5 sm:h-5 text-blue-600 border border-blue-300 rounded p-0.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        <span class="hidden sm:inline">Notifications</span>
                    </div>
                </button>
                <button @click="activeTab = 'credentials'"
                    class="py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors whitespace-nowrap" 
                    :class="activeTab === 'credentials' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <svg class="w-5 h-5 sm:w-5 sm:h-5 text-blue-600 border border-blue-300 rounded p-0.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                        </svg>
                        <span class="hidden sm:inline">Credentials</span>
                    </div>
                </button>
                <button @click="activeTab = 'verification'"
                    class="py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors whitespace-nowrap"
                    :class="activeTab === 'verification' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <svg class="w-5 h-5 sm:w-5 sm:h-5 text-blue-600 border border-blue-300 rounded p-0.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="hidden sm:inline">Verification</span>
                    </div>
                </button>
                <button @click="activeTab = 'kyc'"
                    class="py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors whitespace-nowrap"
                    :class="activeTab === 'kyc' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <svg class="w-5 h-5 sm:w-5 sm:h-5 text-blue-600 border border-blue-300 rounded p-0.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="hidden sm:inline">KYC</span>
                    </div>
                </button>
                <button @click="activeTab = 'payment'"
                    class="py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors whitespace-nowrap"
                    :class="activeTab === 'payment' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <svg class="w-5 h-5 sm:w-5 sm:h-5 text-blue-600 border border-blue-300 rounded p-0.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                        </svg>
                        <span class="hidden sm:inline">Payment</span>
                    </div>
                </button>
            </nav>
        </div>

        <div class="p-4 sm:p-6">
            <!-- General Settings -->
            <div x-show="activeTab === 'general'">
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">General Settings</h3>
                            <p class="text-sm text-gray-500">Configure basic platform information and settings</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($maintenance_mode)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Maintenance Mode
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Online
                                </span>
                            @endif
                        </div>
                    </div>

                    <form wire:submit="saveGeneralSettings" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Platform Name</label>
                                <input wire:model="platform_name" type="text" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    placeholder="Enter platform name">
                                @error('platform_name') 
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Contact Email</label>
                                <input wire:model="contact_email" type="email" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                    placeholder="admin@example.com">
                                @error('contact_email') 
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Platform Description</label>
                            <textarea wire:model="platform_description" rows="3" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none"
                                placeholder="Describe your platform..."></textarea>
                            @error('platform_description') 
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex items-center h-5">
                                    <input wire:model="maintenance_mode" type="checkbox" 
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                </div>
                                <div class="flex-1">
                                    <label class="text-sm font-medium text-gray-900">Maintenance Mode</label>
                                    <p class="text-sm text-gray-500">When enabled, the platform will be temporarily unavailable to users</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <button type="submit" 
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Save General Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Election Settings -->
            <div x-show="activeTab === 'election'">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Election Settings</h3>
                        <p class="text-sm text-gray-500">Configure default election parameters and rules</p>
                    </div>

                    <form wire:submit="saveElectionSettings" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Default Duration (hours)</label>
                                <input wire:model="default_election_duration" type="number" min="1" max="168" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                <p class="text-xs text-gray-500">1-168 hours (1 week max)</p>
                                @error('default_election_duration') 
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Min Candidates</label>
                                <input wire:model="min_candidates_per_position" type="number" min="1" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                <p class="text-xs text-gray-500">Minimum per position</p>
                                @error('min_candidates_per_position') 
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Max Candidates</label>
                                <input wire:model="max_candidates_per_position" type="number" min="1" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                <p class="text-xs text-gray-500">Maximum per position</p>
                                @error('max_candidates_per_position') 
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <h4 class="text-sm font-medium text-gray-900">Candidate Rules</h4>
                            <div class="space-y-3">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex items-center h-5">
                                            <input wire:model="allow_candidate_withdrawal" type="checkbox" 
                                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </div>
                                        <div class="flex-1">
                                            <label class="text-sm font-medium text-gray-900">Allow Candidate Withdrawal</label>
                                            <p class="text-sm text-gray-500">Candidates can withdraw from elections before voting starts</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex items-center h-5">
                                            <input wire:model="require_candidate_approval" type="checkbox" 
                                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </div>
                                        <div class="flex-1">
                                            <label class="text-sm font-medium text-gray-900">Require Candidate Approval</label>
                                            <p class="text-sm text-gray-500">Admin approval required before candidates can participate</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <button type="submit" 
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Save Election Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Settings -->
            <div x-show="activeTab === 'security'">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Security Settings</h3>
                        <p class="text-sm text-gray-500">Configure security policies and authentication requirements</p>
                    </div>

                    <form wire:submit="saveSecuritySettings" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Session Timeout</label>
                                <div class="relative">
                                    <input wire:model="session_timeout" type="number" min="15" max="480" 
                                        class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 text-sm">min</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">15-480 minutes</p>
                                @error('session_timeout') 
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Max Login Attempts</label>
                                <input wire:model="max_login_attempts" type="number" min="3" max="10" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                <p class="text-xs text-gray-500">3-10 attempts before lockout</p>
                                @error('max_login_attempts') 
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Password Min Length</label>
                                <input wire:model="password_min_length" type="number" min="6" max="32" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                <p class="text-xs text-gray-500">6-32 characters</p>
                                @error('password_min_length') 
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Audit Retention</label>
                                <div class="relative">
                                    <input wire:model="audit_retention_days" type="number" min="30" max="2555" 
                                        class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 text-sm">days</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">30-2555 days (7 years max)</p>
                                @error('audit_retention_days') 
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex items-center h-5">
                                    <input wire:model="require_2fa" type="checkbox" 
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                </div>
                                <div class="flex-1">
                                    <label class="text-sm font-medium text-gray-900">Require Two-Factor Authentication</label>
                                    <p class="text-sm text-gray-500">All users must enable 2FA for enhanced security</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <button type="submit" 
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Save Security Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Notifications Settings -->
            <div x-show="activeTab === 'notifications'">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Notification Settings</h3>
                        <p class="text-sm text-gray-500">Configure notification channels and delivery preferences</p>
                    </div>

                    <form wire:submit="saveNotificationSettings" class="space-y-6">
                        <div class="space-y-4">
                            <h4 class="text-sm font-medium text-gray-900">Notification Channels</h4>
                            <div class="space-y-3">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex items-center h-5">
                                            <input wire:model="email_notifications" type="checkbox" 
                                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </div>
                                        <div class="flex-1">
                                            <label class="text-sm font-medium text-gray-900">Email Notifications</label>
                                            <p class="text-sm text-gray-500">Send notifications via email to users</p>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $email_notifications ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $email_notifications ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex items-center h-5">
                                            <input wire:model="sms_notifications" type="checkbox" 
                                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </div>
                                        <div class="flex-1">
                                            <label class="text-sm font-medium text-gray-900">SMS Notifications</label>
                                            <p class="text-sm text-gray-500">Send notifications via SMS to users</p>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sms_notifications ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $sms_notifications ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Notification Frequency</label>
                            <select wire:model="notification_frequency" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                <option value="immediate">Immediate</option>
                                <option value="hourly">Hourly Digest</option>
                                <option value="daily">Daily Digest</option>
                            </select>
                            <p class="text-xs text-gray-500">How often to send notification batches</p>
                        </div>
                        
                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <button type="submit" 
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Save Notification Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Credentials Settings -->
            <div x-show="activeTab === 'credentials'">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Third-Party Credentials</h3>
                        <p class="text-sm text-gray-500">Configure credentials for email, SMS, and file storage services</p>
                    </div>

                    <form wire:submit="saveCredentialsSettings" class="space-y-8">
                        <!-- SMTP Settings -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4 flex items-center space-x-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>SMTP Configuration</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">SMTP Host</label>
                                    <input wire:model="smtp_host" type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                        placeholder="smtp.gmail.com">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">SMTP Port</label>
                                    <input wire:model="smtp_port" type="number"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                        placeholder="587">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Username</label>
                                    <input wire:model="smtp_username" type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                        placeholder="your-email@gmail.com">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Password</label>
                                    <input wire:model="smtp_password" type="password"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                        placeholder="your-app-password">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Save Credentials</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Verification Settings -->
            <div x-show="activeTab === 'verification'">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Identity Verification</h3>
                        <p class="text-sm text-gray-500">Configure identity verification and re-verification settings</p>
                    </div>

                    <form wire:submit="saveReVerificationSettings" class="space-y-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex items-center h-5">
                                    <input wire:model="allow_re_verification" type="checkbox"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                </div>
                                <div class="flex-1">
                                    <label class="text-sm font-medium text-gray-900">Allow Re-verification</label>
                                    <p class="text-sm text-gray-500">Allow users to re-verify their identity documents after the specified period</p>
                                </div>
                            </div>
                        </div>

                        <div x-show="$wire.allow_re_verification" class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Re-verification Period (days)</label>
                            <div class="relative">
                                <input wire:model="re_verification_period_days" type="number" min="30" max="3650"
                                    class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-gray-500 text-sm">days</span>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">How often users can re-verify their identity (30 days - 10 years)</p>
                            @error('re_verification_period_days')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Save Verification Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- KYC Settings -->
            <div x-show="activeTab === 'kyc'">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">KYC Settings</h3>
                        <p class="text-sm text-gray-500">Configure Know Your Customer (KYC) document verification settings</p>
                    </div>

                    <form wire:submit="saveKycSettings" class="space-y-6">
                        <div class="space-y-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-start space-x-3">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-900">Document Resubmission Limits</h4>
                                        <p class="text-sm text-blue-700 mt-1">Control how many times users can resubmit rejected KYC documents to prevent abuse while allowing legitimate corrections.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Maximum Resubmission Attempts</label>
                                <div class="relative">
                                    <input wire:model="max_kyc_resubmissions" type="number" min="1" max="10"
                                        class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 text-sm">attempts</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500">1-10 attempts (recommended: 3)</p>
                                <p class="text-xs text-gray-600 mt-1">After reaching this limit, users cannot upload new KYC documents and must contact support.</p>
                                @error('max_kyc_resubmissions')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start space-x-3">
                                    <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-yellow-900">Important Notes</h4>
                                        <ul class="text-sm text-yellow-700 mt-1 space-y-1">
                                            <li>• Changes take effect immediately for new uploads</li>
                                            <li>• Existing users with pending documents are not affected</li>
                                            <li>• Lower values increase security but may frustrate legitimate users</li>
                                            <li>• Higher values allow more abuse but provide better user experience</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Save KYC Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Payment Settings -->
            <div x-show="activeTab === 'payment'">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Payment Settings</h3>
                        <p class="text-sm text-gray-500">Configure payment accounts where candidates can pay application fees</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Current Payment Settings Display -->
                        @if($bank_name || $account_name || $account_number)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-3">Current Payment Account</h4>
                            <div class="bg-white rounded-lg p-4 border border-blue-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold text-gray-900">{{ $account_name ?: 'Account Name Not Set' }}</div>
                                        <div class="text-sm text-gray-600">{{ $bank_name ?: 'Bank Name Not Set' }}</div>
                                        @if($account_number)
                                        <div class="text-xs text-gray-500 mt-1">Account: {{ $account_number }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                    <form wire:submit="savePaymentSettings" class="space-y-6" x-data="{ showConfirm: false }" @submit.prevent="showConfirm = true">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4 flex items-center space-x-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span>Bank Account Details</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                                    <input wire:model="bank_name" type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="e.g., First Bank of Nigeria">
                                    @error('bank_name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Account Name</label>
                                    <input wire:model="account_name" type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Account holder name">
                                    @error('account_name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2 md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Account Number</label>
                                    <input wire:model="account_number" type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="10-digit account number">
                                    @error('account_number') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Payment Instructions</label>
                            <textarea wire:model="payment_instructions" rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                                placeholder="Provide detailed instructions on how candidates should make payments (e.g., include reference, contact details, etc.)"></textarea>
                            @error('payment_instructions') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Save Payment Settings</span>
                            </button>
                        </div>
                        
                        <!-- Confirmation Modal -->
                        <div x-show="showConfirm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-cloak>
                            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                <div class="mt-3 text-center">
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2">Confirm Payment Settings</h3>
                                    <div class="mt-2 px-7 py-3">
                                        <p class="text-sm text-gray-500">Are you sure you want to save these payment settings? Candidates will see this information when paying application fees.</p>
                                    </div>
                                    <div class="items-center px-4 py-3">
                                        <button wire:click="savePaymentSettings" @click="showConfirm = false"
                                            class="px-4 py-2 bg-indigo-600 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-indigo-700">
                                            Save
                                        </button>
                                        <button @click="showConfirm = false"
                                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>