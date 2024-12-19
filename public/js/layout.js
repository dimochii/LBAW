

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    let debounceTimer;

    const toggleSearchResults = (show) => {
        searchResults.style.opacity = show ? '1' : '0';
        searchResults.style.transform = show ? 'scale(1)' : 'scale(0.95)';
        searchResults.style.pointerEvents = show ? 'auto' : 'none';
    };

    const createCommunityItem = (community) => `
        <div class="p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 cursor-pointer" 
             onclick="window.location.href='${community.route}'">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full object-cover bg-green-500"></div>
                <span class="text-sm text-gray-700">${community.name}</span>
            </div>
            ${community.description ? `
                <p class="text-xs text-gray-500 mt-1 ml-4">${community.description}</p>
            ` : ''}
        </div>
    `;

    const createPostItem = (post) => `
        <div class="p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 cursor-pointer"
             onclick="window.location.href='${post.community_route}'">
            <span class="text-sm text-gray-700">${post.name}</span>
            <div class="text-xs text-gray-500 mt-1">in ${post.community}</div>
            ${post.content ? `
                <p class="text-xs text-gray-500 mt-1 line-clamp-2">${post.content}</p>
            ` : ''}
        </div>
    `;

    const createUserItem = (user) => `
    <div class="p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200 cursor-pointer"
         onclick="window.location.href='${user.route}'">
        <div class="flex items-center gap-3">
            ${user.image ? `
                <img src="${user.image}" class="w-6 h-6 rounded-full object-cover">
            ` : `
                <div class="w-6 h-6 rounded-full bg-blue-500"></div>
            `}
            <span class="text-sm text-gray-700">@${user.name}</span>
        </div>
    </div>
`;


    const updateSearchResults = (results) => {
        const communitiesContainer = searchResults.querySelector('.from-red-50.to-blue-50 .space-y-2');
        const postsContainer = searchResults.querySelector('.from-blue-50.to-green-50 .space-y-2');
        const usersContainer = searchResults.querySelector('.from-green-50.to-red-50 .space-y-2');

        // Update communities section
        communitiesContainer.innerHTML = results.communities.length 
            ? results.communities.map(createCommunityItem).join('')
            : '<div class="p-3 text-sm text-gray-500">No communities found</div>';

        // Update posts section
        postsContainer.innerHTML = results.posts.length
            ? results.posts.map(createPostItem).join('')
            : '<div class="p-3 text-sm text-gray-500">No posts found</div>';

        // Update users section
        usersContainer.innerHTML = results.users.length
            ? results.users.map(createUserItem).join('')
            : '<div class="p-3 text-sm text-gray-500">No users found</div>';

        toggleSearchResults(true);
    };

    const performSearch = async (searchTerm) => {
        if (!searchTerm.trim()) {
            toggleSearchResults(false);
            return;
        }

        try {
            const response = await fetch(`/search?search=${encodeURIComponent(searchTerm)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Search request failed');

            const results = await response.json();
            updateSearchResults(results);
        } catch (error) {
            console.error('Search error:', error);
            // Show error state in dropdown
            searchResults.innerHTML = `
                <div class="p-4 text-sm text-red-500">
                    An error occurred while searching. Please try again.
                </div>
            `;
            toggleSearchResults(true);
        }
    };

    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => performSearch(e.target.value), 300);
    });

    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim()) {
            toggleSearchResults(true);
        }
    });

    document.addEventListener('click', (e) => {
        if (!searchResults.contains(e.target) && e.target !== searchInput) {
            toggleSearchResults(false);
        }
    });
});

// ---------- Responsive design ----------

function toggleLeftSidebar() {
    const leftSidebar = document.getElementById('left-sidebar');
    const overlay = document.getElementById('mobile-menu-overlay');
    if (leftSidebar.classList.contains('-translate-x-full')) {
        leftSidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    } else {
        leftSidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    }
}

function toggleMobileMenu() {
    const leftSidebar = document.getElementById('left-sidebar');
    const overlay = document.getElementById('mobile-menu-overlay');
    
    leftSidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

document.addEventListener('click', function(event) {
    const leftSidebar = document.getElementById('left-sidebar');
    const rightSidebar = document.getElementById('right-sidebar');
    const overlay = document.getElementById('mobile-menu-overlay');
    const mobileMenuButton = event.target.closest('button');
    
    if (!leftSidebar.contains(event.target) && 
        (!rightSidebar || !rightSidebar.contains(event.target)) && 
        !mobileMenuButton?.hasAttribute('onclick')) {
        
        leftSidebar.classList.add('-translate-x-full');
        if (rightSidebar) {
            rightSidebar.classList.add('translate-x-full');
        }
        overlay.classList.add('hidden');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const profileIcon = document.getElementById('profileIcon');
    const dropdownMenu = document.getElementById('dropdownMenu');

    // Mostrar o dropdown ao clicar no ícone
    profileIcon.addEventListener('click', function (event) {
      event.preventDefault();
      dropdownMenu.classList.toggle('hidden');
    });

    // Fechar o dropdown ao clicar fora dele
    document.addEventListener('click', function (event) {
      if (!dropdownMenu.contains(event.target) && !profileIcon.contains(event.target)) {
        dropdownMenu.classList.add('hidden');
      }
    });
  });

// ---------- Favorite button ----------

function toggleFavorite(postId) {
    const isChecked = document.getElementById(`favorite-${postId}`).checked;
    const url = isChecked ? `/favorite/${postId}/add` : `/favorite/${postId}/remove`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            console.log(data.message);
        } else {
            console.log('An error occurred');
        }
    })
    .catch(error => console.error('Error:', error));
}