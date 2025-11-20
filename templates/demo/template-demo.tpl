{{-- This is a comment --}}
<div style="padding: 20px; background: #f9f9f9; border-radius: 8px;">
    <h2>Template Engine Demo</h2>
    
    <p>Welcome, <strong>{{ $username ?? 'Guest' }}</strong>!</p>
    
    @if(isset($isAdmin) && $isAdmin)
        <div style="background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0;">
            <strong>Admin Access</strong>: You have administrative privileges.
        </div>
    @else
        <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0;">
            <strong>Regular User</strong>: You have standard access.
        </div>
    @endif
    
    <h3>Sample List</h3>
    @if(isset($items) && count($items) > 0)
        <ul>
            @foreach($items as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @else
        <p>No items to display.</p>
    @endif
    
    <h3>Raw HTML Example</h3>
    <p>Escaped: {{ '<script>alert("XSS")</script>' }}</p>
    <p>Raw: {!! '<strong>This is bold</strong>' !!}</p>
</div>
