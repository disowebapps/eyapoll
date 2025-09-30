# Candidate Application Deadline Implementation (Policy-Based)

## Overview
Clean implementation using Laravel's authorization system to control candidate application access based on deadlines.

## Components Modified

### 1. ElectionPolicy (`app/Policies/ElectionPolicy.php`)
- Enhanced `apply()` method with deadline logic
- Added `getApplicationMessage()` for consistent messaging
- Single source of truth for application authorization

### 2. Views (Policy-Based Authorization)
- **Dashboard & Elections**: Use `@can('apply', $election)` and `@cannot('apply', $election)`
- Greyed out buttons when policy denies access
- Modal shows policy-generated messages

### 3. Middleware (`app/Http/Middleware/CheckCandidateApplicationDeadline.php`)
- Simplified to use policy: `$request->user()?->can('apply', $election)`
- Route-level enforcement using Laravel's authorization

### 4. Livewire Components
- **ApplicationForm**: Uses `auth()->user()->can('apply', $election)`
- Clean policy-based checks in mount and submit methods

## Technical Implementation

### Policy Logic
```php
public function apply(?User $user, Election $election): bool
{
    if (!$user || !$user->isApproved()) return false;
    
    $now = now();
    if ($election->candidate_register_starts && $now->lt($election->candidate_register_starts)) return false;
    if ($election->candidate_register_ends && $now->gt($election->candidate_register_ends)) return false;
    
    return $election->canAcceptCandidateApplications();
}
```

### Frontend Logic
```php
@can('apply', $election)
    <a href="{{ route('candidate.apply', $election->id) }}" class="bg-green-600...">
        Apply as Candidate
    </a>
@else
    <button @click="showModal = true" class="bg-gray-400...">
        Apply as Candidate
    </button>
@endcan
```

## Benefits of Policy-Based Approach
- ✅ Single source of truth for authorization logic
- ✅ Laravel's built-in authorization system
- ✅ Cleaner, more testable code
- ✅ Consistent across all entry points
- ✅ Follows framework conventions