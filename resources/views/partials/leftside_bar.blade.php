<div class="sidebar">
    
    <h3> recent </h3>
    <ul>
        @foreach ($recentHubs as $hub)
            <li>{{ $hub['name'] }} (ID: {{ $hub['id'] }})</li>
        @endforeach
    </ul>

    <h3> hubs </h3>
    <ul>
        @foreach ($userHubs as $hub)
            <li>{{ $hub['name'] }} (ID: {{ $hub['id'] }})</li>
        @endforeach
    </ul>

</div>
