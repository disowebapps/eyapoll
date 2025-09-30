@extends('layouts.voter')

@section('title', 'Submit Appeal')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Submit Election Appeal</h1>
            <p class="text-gray-600 mt-2">Please provide details about your appeal. All information will be reviewed confidentially.</p>
        </div>

        <form id="appealForm" enctype="multipart/form-data">
            @csrf

            <!-- Election Selection -->
            <div class="mb-6">
                <label for="election_id" class="block text-sm font-medium text-gray-700 mb-2">Election</label>
                <select id="election_id" name="election_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select an election</option>
                    @foreach($elections as $election)
                        <option value="{{ $election->id }}">{{ $election->title }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Appeal Type -->
            <div class="mb-6">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Appeal Type</label>
                <select id="type" name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select appeal type</option>
                    @foreach($appealTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Appeal Title</label>
                <input type="text" id="title" name="title" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Brief title for your appeal" required>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Detailed Description</label>
                <textarea id="description" name="description" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Please provide detailed information about your appeal, including what happened and why you believe it was incorrect." required></textarea>
            </div>

            <!-- Document Upload -->
            <div class="mb-6">
                <label for="documents" class="block text-sm font-medium text-gray-700 mb-2">Supporting Documents (Optional)</label>
                <p class="text-sm text-gray-500 mb-2">Upload up to 3 documents (PDF, JPG, PNG) - Max 5MB each</p>
                <input type="file" id="documents" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div id="fileList" class="mt-2"></div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="button" onclick="window.history.back()" class="mr-4 px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50" id="submitBtn">
                    Submit Appeal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('appealForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';

    const formData = new FormData(this);

    try {
        const response = await fetch('{{ route("voter.appeals.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (result.success) {
            window.location.href = result.redirect;
        } else {
            alert('Error: ' + (result.message || 'Failed to submit appeal'));
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Appeal';
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Appeal';
    }
});

// File validation
document.getElementById('documents').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';

    if (files.length > 3) {
        alert('Maximum 3 files allowed');
        e.target.value = '';
        return;
    }

    files.forEach(file => {
        if (file.size > 5 * 1024 * 1024) { // 5MB
            alert(`File ${file.name} is too large. Maximum size is 5MB.`);
            e.target.value = '';
            return;
        }

        const div = document.createElement('div');
        div.className = 'text-sm text-gray-600';
        div.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        fileList.appendChild(div);
    });
});
</script>
@endsection