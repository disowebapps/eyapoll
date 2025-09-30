@extends('layouts.guest')

@section('title', 'Verify Vote - Echara Youths')
@section('main-class', 'pt-16')

@push('styles')
@livewireStyles
@endpush

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @livewire('public.receipt-verification', ['hash' => $hash])
</div>
@endsection

@push('scripts')
@livewireScripts

<script>
// Auto-resize textareas
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea[data-auto-resize]');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
});

// Copy to clipboard functionality
window.addEventListener('copy-to-clipboard', event => {
    navigator.clipboard.writeText(event.detail.text).then(() => {
        window.EcharaHelpers.showToast(event.detail.message || 'Copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        window.EcharaHelpers.showToast('Failed to copy to clipboard', 'error');
    });
});

// Download receipt functionality
window.addEventListener('download-receipt', event => {
    const data = event.detail.data;
    const filename = event.detail.filename;

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    window.EcharaHelpers.showToast('Receipt downloaded successfully!', 'success');
});

// Share receipt functionality
window.addEventListener('share-receipt', event => {
    const text = event.detail.text;
    const url = event.detail.url;

    if (navigator.share) {
        navigator.share({
            title: 'Civic Participation Receipt',
            text: text,
            url: url
        });
    } else {
        navigator.clipboard.writeText(text + ' ' + url).then(() => {
            window.EcharaHelpers.showToast('Receipt link copied to clipboard!', 'success');
        });
    }
});

// Form validation helpers
window.EcharaHelpers = {
    validateIdNumber: function(idNumber) {
        const cleaned = idNumber.replace(/[^0-9]/g, '');
        return cleaned.length === 11 && /^\d+$/.test(cleaned);
    },

    formatPhoneNumber: function(phoneNumber) {
        const cleaned = phoneNumber.replace(/[^0-9+]/g, '');
        return cleaned;
    },

    showToast: function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
};
</script>

<style>
.form-input {
    @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500;
}

.form-select {
    @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500;
}

.btn-primary {
    @apply bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed;
}

.btn-secondary {
    @apply bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2;
}

.btn-success {
    @apply bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2;
}

.btn-danger {
    @apply bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2;
}

.error-message {
    @apply text-red-600 text-sm mt-1;
}

.success-message {
    @apply text-green-600 text-sm mt-1;
}

.info-message {
    @apply text-blue-600 text-sm mt-1;
}

.card {
    @apply bg-white overflow-hidden shadow rounded-lg;
}

.card-header {
    @apply px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200;
}

.card-body {
    @apply px-4 py-5 sm:p-6;
}

.election-card {
    @apply bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200;
}

.election-card.active {
    @apply ring-2 ring-blue-500;
}

.position-card {
    @apply bg-white border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors duration-200;
}

.candidate-card {
    @apply flex items-center p-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 cursor-pointer;
}

.candidate-card.selected {
    @apply border-blue-500 bg-blue-50 ring-2 ring-blue-200;
}

.progress-bar {
    @apply w-full bg-gray-200 rounded-full h-2;
}

.progress-fill {
    @apply bg-blue-600 h-2 rounded-full transition-all duration-300;
}
</style>
@endpush