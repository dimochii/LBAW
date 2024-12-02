// Tailwind Configuration
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'pastelGreen ': '#A6B37D',
                'whatsup-red': '#C96868',
                'whatsup-blue': '#7EACB5'
            }
        }
    }
};

// Search functionality module
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const searchElements = {
        input: document.getElementById('search-input'),
        results: document.getElementById('search-results'),
        communities: document.querySelector('#communities-results .space-y-2'),
        posts: document.querySelector('#posts-results .space-y-2'),
        users: document.querySelector('#users-results .space-y-2')
    };
    
    let debounceTimer;

    // Event Listeners
    function initializeSearchListeners() {
        // Show results on focus if there's a value
        searchElements.input.addEventListener('focus', () => {
            if (searchElements.input.value.trim().length > 0) {
                searchElements.results.classList.remove('hidden');
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchElements.input.contains(e.target) && 
                !searchElements.results.contains(e.target)) {
                searchElements.results.classList.add('hidden');
            }
        });

        // Handle search input with debounce
        searchElements.input.addEventListener('input', function(e) {
            clearTimeout(debounceTimer);
            
            if (e.target.value.trim().length === 0) {
                searchElements.results.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => {
                performSearch(e.target.value);
            }, 300);
        });
    }

    // Search API call
    async function performSearch(query) {
        try {
            const response = await fetch(`/search?search=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            updateSearchResults(data);
            
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    // Update DOM with search results
    function updateSearchResults(data) {
        // Show results container
        searchElements.results.classList.remove('hidden');
        
        // Update Communities
        searchElements.communities.innerHTML = data.communities.length ? 
            data.communities.map(community => `
                <a href="${community.route}" class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded">
                    <div class="w-8 h-8 bg-pastelGreen  rounded-full flex-shrink-0"></div>
                    <div>
                        <div class="font-medium text-sm">${community.name}</div>
                        <div class="text-xs text-gray-500 truncate">${community.description}</div>
                    </div>
                </a>
            `).join('') : 
            '<div class="text-sm text-gray-500 p-2">No communities found</div>';

        // Update Posts
        searchElements.posts.innerHTML = data.posts.length ?
            data.posts.map(post => `
                <a href="${post.community_route}" class="block p-2 hover:bg-gray-100 rounded">
                    <div class="font-medium text-sm">${post.name}</div>
                    <div class="text-xs text-gray-500">in ${post.community}</div>
                    <div class="text-xs text-gray-500 truncate">${post.content}</div>
                </a>
            `).join('') :
            '<div class="text-sm text-gray-500 p-2">No posts found</div>';

        // Update Users
        searchElements.users.innerHTML = data.users.length ?
            data.users.map(user => `
                <a href="${user.route}" class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded">
                    <div class="w-8 h-8 bg-whatsup-blue rounded-full flex-shrink-0"></div>
                    <div class="font-medium text-sm">${user.name}</div>
                </a>
            `).join('') :
            '<div class="text-sm text-gray-500 p-2">No users found</div>';
    }

    // Initialize the search functionality
    initializeSearchListeners();
});
