@props(['election'])

@if($election->voter_register_ends && 
    $election->voter_register_ends <= now() && 
    !$election->voter_register_published)
    
    <div class="flex gap-2">
        <form method="POST" action="{{ route('admin.elections.voter-register.publish', $election) }}" 
              class="inline-block">
            @csrf
            <button type="submit" 
                    onclick="return confirm('This will finalize the voter register and transition to verification phase. Continue?')"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                📋 Publish Voter Register
            </button>
        </form>
        
        <form method="POST" action="{{ route('admin.elections.voter-register.extend', $election) }}" 
              class="inline-block">
            @csrf
            <button type="submit" 
                    onclick="return confirm('Extend voter registration by 7 days?')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                ⏰ Extend Registration
            </button>
        </form>
    </div>
    
@elseif($election->voter_register_published)
    
    <div class="flex items-center gap-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            ✅ Voter Register Published
        </span>
        
        <form method="POST" action="{{ route('admin.elections.voter-register.restart', $election) }}" 
              class="inline-block">
            @csrf
            <button type="submit" 
                    onclick="return confirm('This will reopen registration. New users can register and will be eligible if you republish the register. Continue?')"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded text-xs font-medium">
                🔄 Restart Registration
            </button>
        </form>
    </div>

@elseif($election->voter_register_published === null && $election->phase === 'voter_registration')
    
    <div class="flex gap-2">
        <form method="POST" action="{{ route('admin.elections.voter-register.publish', $election) }}" 
              class="inline-block">
            @csrf
            <button type="submit" 
                    onclick="return confirm('Republish voter register to include new users registered during extension/restart period?')"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                📋 Republish Voter Register
            </button>
        </form>
        
        <form method="POST" action="{{ route('admin.elections.voter-register.extend', $election) }}" 
              class="inline-block">
            @csrf
            <button type="submit" 
                    onclick="return confirm('Extend voter registration by 7 days?')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                ⏰ Extend Registration
            </button>
        </form>
    </div>
    
@elseif($election->voter_register_ends && $election->voter_register_ends > now())
    
    <div class="flex items-center gap-2">
        <span class="text-sm text-gray-500">
            📅 Registration ends: {{ $election->voter_register_ends->format('M d, Y H:i') }}
        </span>
        
        <form method="POST" action="{{ route('admin.elections.voter-register.extend', $election) }}" 
              class="inline-block">
            @csrf
            <button type="submit" 
                    onclick="return confirm('Extend voter registration by 7 days?')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs font-medium">
                ⏰ Extend
            </button>
        </form>
    </div>
    
@endif