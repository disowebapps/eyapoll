<div role="main" aria-labelledby="application-heading">
    <!-- Header -->
    <header class="mb-6">
        <div class="bg-white shadow rounded-lg p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <div>
                    <h1 id="application-heading" class="text-xl sm:text-2xl font-bold text-gray-900">Apply for Candidacy</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">{{ $election->title ?? 'Select an election to continue' }}</p>
                    <div class="flex flex-col sm:flex-row sm:items-center mt-2 space-y-2 sm:space-y-0 sm:space-x-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 w-fit">
                            {{ $election ? $election->type->label() : 'Election' }}
                        </span>
                        <span class="text-xs sm:text-sm text-gray-500">
                            Deadline: {{ $election && $election->candidate_application_deadline ? $election->candidate_application_deadline->format('M j, Y g:i A') : 'Open until election starts' }}
                        </span>
                    </div>
                </div>
                <div class="text-left sm:text-right">
                    <div class="text-sm text-gray-500">Application Fee</div>
                    <div class="text-lg font-semibold text-gray-900">
                        @if($election && $election->candidate_application_fee > 0)
                            ${{ number_format($election->candidate_application_fee, 2) }}
                        @else
                            Free
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Progress Bar -->
    <nav class="mb-6" aria-label="Application Progress">
        <div class="bg-white shadow rounded-lg p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 space-y-2 sm:space-y-0">
                <h2 class="text-lg font-semibold text-gray-900">Application Progress</h2>
                <span class="text-sm text-gray-500" aria-live="polite">{{ $progress['completed'] }} of {{ $progress['total'] }} steps completed</span>
            </div>

            <!-- Progress Steps -->
            <div class="flex items-center space-x-2 sm:space-x-4 mb-4 overflow-x-auto">
                @for($i = 1; $i <= $totalSteps; $i++)
                <button
                    wire:click="goToStep({{ $i }})"
                    class="flex items-center justify-center w-10 h-10 rounded-full transition-colors duration-200 flex-shrink-0 {{ $currentStep === $i ? 'bg-blue-600 text-white' : ($progress['steps'][$i] ?? false ? 'bg-green-600 text-white' : 'bg-gray-300 text-gray-600') }}"
                    :disabled="!$progress['steps'][$i] && $i > 1"
                    aria-label="Go to step {{ $i }}: {{ ['Position', 'Manifesto', 'Documents', 'Review'][$i-1] }}"
                    aria-current="{{ $currentStep === $i ? 'step' : 'false' }}"
                >
                    @if($progress['steps'][$i] ?? false)
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    @else
                        {{ $i }}
                    @endif
                </button>
                @if($i < $totalSteps)
                <div class="flex-1 h-1 bg-gray-200 rounded min-w-[20px]">
                    <div class="h-1 bg-blue-600 rounded transition-all duration-300" style="width: {{ $i < $currentStep ? '100%' : '0%' }}"></div>
                </div>
                @endif
                @endfor
            </div>

            <!-- Step Labels -->
            <div class="grid gap-2 sm:gap-4 text-center" style="grid-template-columns: repeat({{ $totalSteps }}, 1fr);">
                @if($isAdminFlow)
                    <div class="text-xs font-medium text-gray-600">Election</div>
                    <div class="text-xs font-medium text-gray-600">Voter</div>
                @endif
                <div class="text-xs font-medium text-gray-600">Position</div>
                <div class="text-xs font-medium text-gray-600">Manifesto</div>
                <div class="text-xs font-medium text-gray-600">Documents</div>
                @if($isAdminFlow || $requiresPayment)
                    <div class="text-xs font-medium text-gray-600">Payment</div>
                @endif
                <div class="text-xs font-medium text-gray-600">Review</div>
            </div>
        </div>
    </nav>

    <!-- Step Content -->
    <div class="bg-white shadow rounded-lg">
        <!-- Step 0: Election Selection (Admin Only) -->
        @if($isAdminFlow && $currentStep === 1)
        <section aria-labelledby="step0-heading">
            <header class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 id="step0-heading" class="text-lg font-semibold text-gray-900">Step 1: Select Election</h3>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">Choose which election to create this application for.</p>
            </header>

            <div class="px-4 sm:px-6 py-4">
                <div class="space-y-4">
                    <select wire:model="selectedElectionId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select an election...</option>
                        @foreach($elections as $electionOption)
                            <option value="{{ $electionOption['id'] }}">{{ $electionOption['title'] }} @if($electionOption['candidate_application_fee'] > 0)(Fee: ${{ number_format($electionOption['candidate_application_fee'], 2) }})@endif</option>
                        @endforeach
                    </select>
                    @error('selectedElectionId')
                    <p class="text-red-600 text-sm mt-2" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>
        @endif

        <!-- Step 1: Voter Selection (Admin Only) -->
        @if($isAdminFlow && $currentStep === 2)
        <section aria-labelledby="step1-heading">
            <header class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 id="step1-heading" class="text-lg font-semibold text-gray-900">Step 2: Select Voter</h3>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">Choose which voter you are creating this application for.</p>
            </header>

            <div class="px-4 sm:px-6 py-4">
                <div class="space-y-4">
                    <select wire:model="selectedVoterId" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a voter...</option>
                        @foreach($voters as $voter)
                            <option value="{{ $voter['id'] }}">{{ $voter['name'] }} ({{ $voter['email'] }})</option>
                        @endforeach
                    </select>
                    @error('selectedVoterId')
                    <p class="text-red-600 text-sm mt-2" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>
        @endif

        <!-- Step 2: Position Selection -->
        @if(($isAdminFlow && $currentStep === 3) || (!$isAdminFlow && $currentStep === 1))
        <section aria-labelledby="step1-heading">
            <header class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 id="step1-heading" class="text-lg font-semibold text-gray-900">Step 1: Select Position</h3>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">Choose the position you want to apply for in this election.</p>
            </header>

            <div class="px-4 sm:px-6 py-4">
                <div class="space-y-4" role="radiogroup" aria-labelledby="step1-heading">
                    @foreach($positions as $position)
                    <div class="relative">
                        <label class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:border-blue-300 cursor-pointer transition-colors duration-200 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500">
                            <input
                                type="radio"
                                wire:model="selectedPositionId"
                                value="{{ $position['id'] }}"
                                class="mt-1 text-blue-600 focus:ring-blue-500 h-5 w-5"
                                aria-describedby="position-{{ $position['id'] }}-desc"
                            >
                            <div class="flex-1">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $position['title'] }}</h4>
                                    <div class="flex flex-wrap items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $position['current_candidates'] }}/{{ $position['max_selections'] }} candidates
                                        </span>
                                        @if(!$position['is_available'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Full
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                @if($position['description'])
                                <p id="position-{{ $position['id'] }}-desc" class="text-sm text-gray-600 mt-1">{{ $position['description'] }}</p>
                                @endif
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>

                @error('selectedPositionId')
                <p class="text-red-600 text-sm mt-2" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </section>
        @endif

        <!-- Step 3: Manifesto -->
        @if(($isAdminFlow && $currentStep === 4) || (!$isAdminFlow && $currentStep === 2))
        <section aria-labelledby="step2-heading">
            <header class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 id="step2-heading" class="text-lg font-semibold text-gray-900">Step 2: Your Manifesto</h3>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">Write your manifesto explaining why voters should choose you for this position.</p>
            </header>

            <div class="px-4 sm:px-6 py-4">
                <div class="space-y-4">
                    @if($this->currentPosition)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4" role="status">
                        <h4 class="font-medium text-blue-900">Applying for: {{ $this->currentPosition['title'] }}</h4>
                        <p class="text-sm text-blue-700 mt-1">{{ $this->currentPosition['description'] }}</p>
                    </div>
                    @endif

                    <div>
                        <label for="manifesto" class="block text-sm font-medium text-gray-700 mb-2">
                            Manifesto <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <textarea
                            wire:model="manifesto"
                            id="manifesto"
                            rows="6 sm:rows-8"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-vertical min-h-[120px]"
                            placeholder="Explain your vision, experience, and why you should be elected..."
                            aria-describedby="manifesto-help manifesto-count"
                        ></textarea>
                        <div class="flex flex-col sm:flex-row sm:justify-between mt-2 space-y-1 sm:space-y-0">
                            <p id="manifesto-help" class="text-sm text-gray-500">
                                Minimum 50 characters
                            </p>
                            <p id="manifesto-count" class="text-sm text-gray-500" aria-live="polite">
                                {{ strlen($manifesto) }} characters entered, {{ 2000 - strlen($manifesto) }} remaining
                            </p>
                        </div>
                    </div>

                    @error('manifesto')
                    <p class="text-red-600 text-sm mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>
        @endif

        <!-- Step 5: Document Upload -->
        @if(($isAdminFlow && $currentStep === 5) || (!$isAdminFlow && $currentStep === 3))
        <section aria-labelledby="step3-heading">
            <header class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 id="step3-heading" class="text-lg font-semibold text-gray-900">Step 3: Upload Documents</h3>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">Upload required documents to support your application. All documents will be reviewed by administrators.</p>
            </header>

            <div class="px-4 sm:px-6 py-4">
                <div class="space-y-6">
                    {{-- Upload Progress --}}
                    @if(isset($uploadProgress) && $uploadProgress > 0)
                        <div role="progressbar" aria-valuenow="{{ $uploadProgress }}" aria-valuemin="0" aria-valuemax="100" aria-label="Upload progress">
                            <div class="bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $uploadProgress }}%"></div>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Uploading... {{ $uploadProgress }}%</p>
                        </div>
                    @endif

                    <!-- CV/Resume -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            CV/Resume <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <div class="mt-1 flex justify-center px-4 sm:px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors duration-200 focus-within:ring-2 focus-within:ring-blue-500">
                            <div class="space-y-1 text-center w-full">
                                @if($uploadedDocuments['cv'])
                                <div class="flex flex-col items-center space-y-2">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="text-sm text-gray-900 text-center">{{ $uploadedDocuments['cv']->getClientOriginalName() }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($uploadedDocuments['cv']->getSize() / 1024, 1) }} KB</div>
                                    <button
                                        wire:click="removeDocument('cv')"
                                        class="mt-2 text-red-600 hover:text-red-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 rounded px-2 py-1 min-h-[44px]"
                                        aria-label="Remove CV/Resume"
                                    >
                                        Remove
                                    </button>
                                </div>
                                @else
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <div class="flex flex-col sm:flex-row text-sm text-gray-600 items-center space-y-2 sm:space-y-0 sm:space-x-2">
                                    <label for="cv-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 px-2 py-1">
                                        <span>Upload CV/Resume</span>
                                        <input
                                            wire:model="uploadedDocuments.cv"
                                            id="cv-upload"
                                            type="file"
                                            class="sr-only"
                                            accept=".pdf,.doc,.docx"
                                            aria-describedby="cv-help"
                                        >
                                    </label>
                                    <p class="text-center sm:text-left">or drag and drop</p>
                                </div>
                                <p id="cv-help" class="text-xs text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Certificates -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Certificates/Diplomas <span class="text-gray-500 text-xs">(Optional)</span>
                        </label>
                        <div class="mt-1 flex justify-center px-4 sm:px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors duration-200 focus-within:ring-2 focus-within:ring-blue-500">
                            <div class="space-y-1 text-center w-full">
                                @if($uploadedDocuments['certificates'])
                                <div class="flex flex-col items-center space-y-2">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="text-sm text-gray-900 text-center">{{ $uploadedDocuments['certificates']->getClientOriginalName() }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($uploadedDocuments['certificates']->getSize() / 1024, 1) }} KB</div>
                                    <button
                                        wire:click="removeDocument('certificates')"
                                        class="mt-2 text-red-600 hover:text-red-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 rounded px-2 py-1 min-h-[44px]"
                                        aria-label="Remove certificates"
                                    >
                                        Remove
                                    </button>
                                </div>
                                @else
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <div class="flex flex-col sm:flex-row text-sm text-gray-600 items-center space-y-2 sm:space-y-0 sm:space-x-2">
                                    <label for="certificates-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 px-2 py-1">
                                        <span>Upload Certificates</span>
                                        <input
                                            wire:model="uploadedDocuments.certificates"
                                            id="certificates-upload"
                                            type="file"
                                            class="sr-only"
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                            aria-describedby="certificates-help"
                                        >
                                    </label>
                                    <p class="text-center sm:text-left">or drag and drop</p>
                                </div>
                                <p id="certificates-help" class="text-xs text-gray-500">PDF, DOC, DOCX, Images up to 5MB</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Photo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Profile Photo <span class="text-gray-500 text-xs">(Optional)</span>
                        </label>
                        <div class="mt-1 flex justify-center px-4 sm:px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors duration-200 focus-within:ring-2 focus-within:ring-blue-500">
                            <div class="space-y-1 text-center w-full">
                                @if($uploadedDocuments['photo'])
                                <div class="flex flex-col items-center space-y-2">
                                    <img src="{{ $uploadedDocuments['photo']->temporaryUrl() }}" alt="Profile photo preview" class="w-16 h-16 rounded-full object-cover" loading="lazy">
                                    <div class="text-sm text-gray-900 text-center">{{ $uploadedDocuments['photo']->getClientOriginalName() }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($uploadedDocuments['photo']->getSize() / 1024, 1) }} KB</div>
                                    <button
                                        wire:click="removeDocument('photo')"
                                        class="mt-2 text-red-600 hover:text-red-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 rounded px-2 py-1 min-h-[44px]"
                                        aria-label="Remove profile photo"
                                    >
                                        Remove
                                    </button>
                                </div>
                                @else
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div class="flex flex-col sm:flex-row text-sm text-gray-600 items-center space-y-2 sm:space-y-0 sm:space-x-2">
                                    <label for="photo-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 px-2 py-1">
                                        <span>Upload Photo</span>
                                        <input
                                            wire:model="uploadedDocuments.photo"
                                            id="photo-upload"
                                            type="file"
                                            class="sr-only"
                                            accept=".jpg,.jpeg,.png"
                                            aria-describedby="photo-help"
                                        >
                                    </label>
                                    <p class="text-center sm:text-left">or drag and drop</p>
                                </div>
                                <p id="photo-help" class="text-xs text-gray-500">JPG, PNG up to 5MB</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- Step 6: Payment (Admin) -->
        @if($isAdminFlow && $currentStep === 6)
        <section aria-labelledby="step6-heading">
            <header class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 id="step6-heading" class="text-lg font-semibold text-gray-900">Step 6: Payment Management</h3>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">Choose how to handle the application fee for this candidate.</p>
            </header>

            <div class="px-4 sm:px-6 py-4">
                <div class="space-y-6">
                    <!-- Fee Information -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-medium text-blue-900 mb-2">Application Fee</h4>
                        <p class="text-2xl font-bold text-blue-900">${{ $election ? number_format($election->candidate_application_fee, 2) : '0.00' }}</p>
                    </div>

                    <!-- Payment Action Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Payment Action</label>
                        <div class="space-y-3">
                            <label class="flex items-start space-x-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input wire:model="adminPaymentAction" type="radio" value="waive" class="mt-1 text-blue-600">
                                <div>
                                    <div class="font-medium text-gray-900">Waive Payment</div>
                                    <div class="text-sm text-gray-600">No payment required - fee waived by admin</div>
                                </div>
                            </label>
                            <label class="flex items-start space-x-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input wire:model="adminPaymentAction" type="radio" value="manual" class="mt-1 text-blue-600">
                                <div>
                                    <div class="font-medium text-gray-900">Manual Payment</div>
                                    <div class="text-sm text-gray-600">Mark as paid - payment received outside system</div>
                                </div>
                            </label>
                            <label class="flex items-start space-x-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input wire:model="adminPaymentAction" type="radio" value="require_proof" class="mt-1 text-blue-600">
                                <div>
                                    <div class="font-medium text-gray-900">Require Payment Proof</div>
                                    <div class="text-sm text-gray-600">Candidate must provide payment proof</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Manual Payment Amount -->
                    @if($adminPaymentAction === 'manual')
                    <div>
                        <label for="admin-payment-amount" class="block text-sm font-medium text-gray-700 mb-2">Payment Amount</label>
                        <input wire:model="adminPaymentAmount" id="admin-payment-amount" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    @endif

                    <!-- Payment Proof Upload (if required) -->
                    @if($adminPaymentAction === 'require_proof')
                    <div>
                        <label for="payment-reference" class="block text-sm font-medium text-gray-700 mb-2">Payment Reference</label>
                        <input wire:model="paymentReference" id="payment-reference" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter payment reference">
                        @error('paymentReference')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Proof</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center w-full">
                                @if($paymentProof)
                                <div class="flex flex-col items-center space-y-2">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="text-sm text-gray-900">{{ $paymentProof->getClientOriginalName() }}</div>
                                    <button wire:click="removePaymentProof" class="text-red-600 hover:text-red-800 text-sm font-medium">Remove</button>
                                </div>
                                @else
                                <div class="flex text-sm text-gray-600">
                                    <label for="payment-proof-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                        <span>Upload payment proof</span>
                                        <input wire:model="paymentProof" id="payment-proof-upload" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png">
                                    </label>
                                </div>
                                @endif
                            </div>
                        </div>
                        @error('paymentProof')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    <!-- Payment Notes -->
                    <div>
                        <label for="admin-payment-notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea wire:model="adminPaymentNotes" id="admin-payment-notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Add any notes about this payment..."></textarea>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- Step 5: Payment (Regular Users) -->
        @if(!$isAdminFlow && $currentStep === 4 && $requiresPayment)
        <section aria-labelledby="step4-heading">
            <header class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 id="step4-heading" class="text-lg font-semibold text-gray-900">Step 4: Payment</h3>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">Upload payment proof for the application fee.</p>
            </header>

            <div class="px-4 sm:px-6 py-4">
                <div class="space-y-6">
                    <!-- Fee Information -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-medium text-blue-900 mb-2">Application Fee</h4>
                        <p class="text-2xl font-bold text-blue-900">${{ $election ? number_format($election->candidate_application_fee, 2) : '0.00' }}</p>
                        <p class="text-sm text-blue-700 mt-1">Payment is required to complete your application.</p>
                    </div>

                    <!-- Payment Reference -->
                    <div>
                        <label for="payment-reference" class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Reference <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model="paymentReference"
                            id="payment-reference"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter payment reference number"
                        >
                        @error('paymentReference')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Proof Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Proof <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center w-full">
                                @if($paymentProof)
                                <div class="flex flex-col items-center space-y-2">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="text-sm text-gray-900">{{ $paymentProof->getClientOriginalName() }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($paymentProof->getSize() / 1024, 1) }} KB</div>
                                    <button
                                        wire:click="removePaymentProof"
                                        class="mt-2 text-red-600 hover:text-red-800 text-sm font-medium"
                                    >
                                        Remove
                                    </button>
                                </div>
                                @else
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="payment-proof-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                        <span>Upload payment proof</span>
                                        <input
                                            wire:model="paymentProof"
                                            id="payment-proof-upload"
                                            type="file"
                                            class="sr-only"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                        >
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, JPG, PNG up to 10MB</p>
                                @endif
                            </div>
                        </div>
                        @error('paymentProof')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- Final Step: Review & Submit -->
        @if(($isAdminFlow && $currentStep === 7) || (!$isAdminFlow && (($currentStep === 5 && $requiresPayment) || ($currentStep === 4 && !$requiresPayment))))
        <section aria-labelledby="step4-heading">
            <header class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 id="step-final-heading" class="text-lg font-semibold text-gray-900">Step {{ $requiresPayment ? '5' : '4' }}: Review & Submit</h3>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">Review your application details before submitting.</p>
            </header>

            <div class="px-4 sm:px-6 py-4">
                <div class="space-y-6">
                    <!-- Position Summary -->
                    <div class="bg-gray-50 rounded-lg p-4" role="region" aria-labelledby="position-summary">
                        <h4 id="position-summary" class="font-medium text-gray-900 mb-2">Position Applied For</h4>
                        @if($this->currentPosition)
                        <div class="text-sm text-gray-600">
                            <p class="font-medium">{{ $this->currentPosition['title'] }}</p>
                            <p>{{ $this->currentPosition['description'] }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Manifesto Preview -->
                    <div class="bg-gray-50 rounded-lg p-4" role="region" aria-labelledby="manifesto-preview">
                        <h4 id="manifesto-preview" class="font-medium text-gray-900 mb-2">Your Manifesto</h4>
                        <div class="text-sm text-gray-600 bg-white p-3 rounded border max-h-32 overflow-y-auto" tabindex="0">
                            {{ $manifesto ?: 'No manifesto provided' }}
                        </div>
                    </div>

                    <!-- Documents Summary -->
                    <div class="bg-gray-50 rounded-lg p-4" role="region" aria-labelledby="documents-summary">
                        <h4 id="documents-summary" class="font-medium text-gray-900 mb-2">Uploaded Documents</h4>
                        <div class="space-y-2" role="list">
                            <div class="flex items-center justify-between text-sm" role="listitem">
                                <span>CV/Resume:</span>
                                <span class="{{ $uploadedDocuments['cv'] ? 'text-green-600' : 'text-red-600' }}" aria-label="{{ $uploadedDocuments['cv'] ? 'Uploaded' : 'Missing' }}">
                                    {{ $uploadedDocuments['cv'] ? '✓ Uploaded' : '✗ Missing' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm" role="listitem">
                                <span>Certificates:</span>
                                <span class="{{ $uploadedDocuments['certificates'] ? 'text-green-600' : 'text-gray-500' }}" aria-label="{{ $uploadedDocuments['certificates'] ? 'Uploaded' : 'Optional' }}">
                                    {{ $uploadedDocuments['certificates'] ? '✓ Uploaded' : '○ Optional' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm" role="listitem">
                                <span>Profile Photo:</span>
                                <span class="{{ $uploadedDocuments['photo'] ? 'text-green-600' : 'text-gray-500' }}" aria-label="{{ $uploadedDocuments['photo'] ? 'Uploaded' : 'Optional' }}">
                                    {{ $uploadedDocuments['photo'] ? '✓ Uploaded' : '○ Optional' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    @if($requiresPayment)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h4 class="font-medium text-green-900 mb-2">Payment Information</h4>
                        <div class="space-y-2 text-sm text-green-700">
                            <div class="flex justify-between">
                                <span>Application Fee:</span>
                                <span class="font-medium">${{ $election ? number_format($election->candidate_application_fee, 2) : '0.00' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Payment Reference:</span>
                                <span class="font-medium">{{ $paymentReference ?: 'Not provided' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Payment Proof:</span>
                                <span class="{{ $paymentProof ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $paymentProof ? '✓ Uploaded' : '✗ Missing' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Terms and Conditions -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <input
                                wire:model="acceptTerms"
                                id="accept-terms"
                                type="checkbox"
                                class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <div class="flex-1">
                                <label for="accept-terms" class="text-sm font-medium text-blue-900 cursor-pointer">
                                    I accept the terms and conditions
                                </label>
                                <ul class="text-xs text-blue-700 mt-2 space-y-1">
                                    <li>• All information provided must be accurate and truthful</li>
                                    <li>• Documents will be reviewed by election administrators</li>
                                    <li>• Application fees are non-refundable once approved</li>
                                    <li>• You may withdraw your application until the approval deadline</li>
                                    <li>• Approved candidates are bound by the election rules and code of conduct</li>
                                </ul>
                            </div>
                        </div>
                        @error('acceptTerms')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </section>
        @endif
    </div>

    <!-- Navigation Buttons -->
    <nav class="flex justify-between mt-6 space-x-4" aria-label="Application navigation">
        @if($currentStep > 1)
        <button
            wire:click="previousStep"
            class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition flex items-center"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back
        </button>
        @else
        <div></div>
        @endif

        @if($currentStep < $totalSteps)
        <button
            wire:click="nextStep"
            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex items-center"
        >
            Next
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        @else
        <button
            wire:click="submitApplication"
            class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition flex items-center"
            wire:loading.attr="disabled"
        >
            @if($isSubmitting)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Submitting...
            @else
            Submit
            @endif
        </button>
        @endif
    </nav>
</div>