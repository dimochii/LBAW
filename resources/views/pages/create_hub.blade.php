@extends('layouts.app')

@section('content')
<div class="min-h-screen font-grotesk">
    <div class="max-w-4xl mx-auto px-8 py-12">
        <div class="mb-16 group">
            <h1 class="text-6xl font-medium tracking-tight mb-4 transition-all duration-500 group-hover:tracking-widest">
                Create Hub
            </h1>
            <div class="h-1 w-24 bg-black transition-all duration-500 ease-out group-hover:w-full"></div>
        </div>

        <form method="POST" action="{{ route('communities.store') }}" class="space-y-12" enctype="multipart/form-data">
            @csrf
            
            <div class="relative">
                <label for="name" 
                       class="absolute left-0 -top-6 text-2xl font-medium text-black/60 
                              transition-all duration-300 peer-placeholder-shown:text-3xl 
                              peer-placeholder-shown:top-2 peer-focus:-top-6 peer-focus:text-2xl">
                    Hub Name
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="peer w-full text-4xl font-medium bg-transparent border-b-2 border-black/10 
                              focus:border-black focus:outline-none pb-2 pt-2 placeholder-transparent
                              transition-all duration-300"
                       placeholder="Enter hub name"
                       required>
            </div>

            <div class="relative">
                <label for="description" 
                       class="absolute left-0 -top-6 text-2xl font-medium text-black/60 
                              transition-all duration-300 peer-placeholder-shown:text-3xl 
                              peer-placeholder-shown:top-2 peer-focus:-top-6 peer-focus:text-2xl">
                    Description
                </label>
                <textarea id="description" 
                          name="description" 
                          class="peer w-full text-xl font-medium bg-transparent border-b-2 border-black/10 
                                 focus:border-black focus:outline-none pb-2 pt-2 placeholder-transparent
                                 transition-all duration-300"
                          placeholder="Enter description"
                          required></textarea>
            </div>

            <div class="space-y-6 border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
                <label class="block text-2xl font-medium">Privacy</label>
                <div class="flex gap-8">
                    <label class="relative group flex items-center gap-3 cursor-pointer">
                        <input type="radio" 
                               name="privacy" 
                               value="public" 
                               class="peer hidden" 
                               checked>
                        <div class="w-6 h-6 border-2 border-black rounded-full relative
                                  group-hover:border-green-400  transition-colors duration-300">
                            <div class="absolute inset-1 rounded-full bg-black transform scale-0 
                                      peer-checked:scale-100 transition-transform duration-300"></div>
                        </div>
                        <span class="text-xl group-hover:text-green-400 transition-colors duration-300">Public</span>
                    </label>

                    <label class="relative group flex items-center gap-3 cursor-pointer">
                        <input type="radio" 
                               name="privacy" 
                               value="private" 
                               class="peer hidden">
                        <div class="w-6 h-6 border-2 border-black rounded-full relative
                                  group-hover:border-red-500 transition-colors duration-300">
                            <div class="absolute inset-1 rounded-full bg-black transform scale-0 
                                      peer-checked:scale-100 transition-transform duration-300"></div>
                        </div>
                        <span class="text-xl group-hover:text-red-500 transition-colors duration-300">Private</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="moderators" class="block text-2xl font-medium mb-2">Additional Moderators</label>
                <select 
                    name="moderators[]" 
                    id="moderators" 
                    multiple 
                    class="w-full rounded text-l border-b-2 border-black/10 focus:border-black focus:outline-none pb-2 transition-all duration-300"
                >
                @foreach(Auth::user()->follows->sortBy('name') as $potentialModerator)
                    <option 
                        class="hover:bg-sky-400"
                        value="{{ $potentialModerator->id }}" 
                        data-image="{{ $potentialModerator->profile_photo_url }}">
                        {{ $potentialModerator->name }} ({{ $potentialModerator->username }})
                    </option>
                @endforeach
                </select>
                <p class="text-sm text-gray-600 mt-1">Select users you follow to be additional moderators</p>
            </div>

            <div id="selected-moderators" class="flex flex-wrap gap-2 mt-4">
                <!-- Chips dynamically added here -->
            </div>


            <div class="space-y-6">
                <label for="image" class="block text-2xl font-medium">Hub Image</label>
                <input type="file" id="image" name="image" class="w-full text-xl border-b-2 border-black/10 focus:border-black focus:outline-none pb-2 transition-colors duration-300">
            </div>

            <div class="preview-section mt-8">
                <h2 class="text-2xl font-medium mb-4">Preview</h2>
                <div class="preview-container border border-black/10 p-6">
                    <div class="flex items-center mb-4">
                        <div class="relative w-10 h-10 rounded-full overflow-hidden mr-4">
                            <img src="/api/placeholder/80/80" alt="Community Image" class="w-full h-full object-cover rounded-full" id="preview-image">
                        </div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-3xl font-medium" id="preview-name">Community Name</h3>
                            <div id="privacy-indicator" class="flex items-center">
                                <!-- Privacy lock icon will be dynamically added here -->
                            </div>
                        </div>
                    </div>
                    <p class="text-xl" id="preview-description">Community Description</p>
                    <div class="mt-4 text-xl">
                        <span id="preview-members">1 member</span>
                        <span class="mx-2">•</span>
                        <span id="preview-online">1 online</span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" 
                        class="group relative overflow-hidden inline-flex items-center gap-4 px-8 py-4 bg-black text-white text-xl font-medium transition-transform duration-300 hover:-translate-y-1">
                    <span class="relative z-10">Create Hub</span>
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="relative z-10 h-6 w-6 transform transition-transform duration-300 group-hover:translate-x-2" 
                         viewBox="0 0 20 20" 
                         fill="currentColor">
                        <path fill-rule="evenodd" 
                              d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" 
                              clip-rule="evenodd" />
                    </svg>
                    <div class="absolute inset-0 bg-wpastelGreen transform translate-y-full transition-transform duration-300 group-hover:translate-y-0"></div>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');
    const previewImage = document.getElementById('preview-image');
    const previewMembers = document.getElementById('preview-members');
    const previewOnline = document.getElementById('preview-online');
    const privacyIndicator = document.getElementById('privacy-indicator');

    function createLockSVG(isPrivate) {
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("xmlns", "http://www.w3.org/2000/svg");
        svg.setAttribute("viewBox", "0 0 24 24");
        svg.setAttribute("width", "24");
        svg.setAttribute("height", "24");
        svg.setAttribute("fill", isPrivate ? "red" : "green");
        svg.setAttribute("class", "ml-2");

        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        
        if (isPrivate) {
            path.setAttribute("d", "M17 10V7a5 5 0 0 0-5-5h-2a5 5 0 0 0-5 5v3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2zM7 7a3 3 0 0 1 3-3h2a3 3 0 0 1 3 3v3H7V7z");
            path.style.fill = '#EF4444'; 
        } else {
            path.setAttribute("d", "M17 8V7a5 5 0 0 0-5-5h-2a5 5 0 0 0-5 5v1a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2zm-9-1a3 3 0 0 1 3-3h2a3 3 0 0 1 3 3v1H8V7z");
            path.style.fill = '#22C55E'; 
        }

        svg.appendChild(path);
        return svg;
    }

    function updatePrivacyIndicator() {
        privacyIndicator.innerHTML = '';

        const selectedPrivacy = document.querySelector('input[name="privacy"]:checked').value;
        const lockSVG = createLockSVG(selectedPrivacy === 'private');
        privacyIndicator.appendChild(lockSVG);
    }

    document.querySelectorAll('input[name="privacy"]').forEach(radio => {
        radio.addEventListener('change', updatePrivacyIndicator);
    });

    nameInput.addEventListener('input', () => {
        previewName.textContent = nameInput.value;
    });

    descriptionInput.addEventListener('input', () => {
        previewDescription.textContent = descriptionInput.value;
    });

    const imageInput = document.getElementById('image');
    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        previewImage.src = URL.createObjectURL(file);
    });

    previewMembers.textContent = '1 member';
    previewOnline.textContent = '1 online';

    updatePrivacyIndicator();

    const moderatorsSelect = document.getElementById('moderators');
    moderatorsSelect.multiple = true;

    
    document.addEventListener('DOMContentLoaded', () => {
        const moderatorsSelect = document.getElementById('moderators');
        const selectedModeratorsContainer = document.getElementById('selected-moderators');
        const previewMembers = document.getElementById('preview-members');

        // Color palette inspired by news and journalism themes
        const newsColors = [
        { bg: '#2C3E50', text: '#ECF0F1' },  // Dark blue-gray with light text
        { bg: '#34495E', text: '#ECF0F1' },  // Slightly lighter blue-gray
        { bg: '#2980B9', text: '#FFFFFF' },  // Bright blue
        { bg: '#3498DB', text: '#FFFFFF' },  // Lighter bright blue
        { bg: '#16A085', text: '#FFFFFF' },  // Teal green
        { bg: '#1ABC9C', text: '#FFFFFF' },  // Lighter teal
        { bg: '#8E44AD', text: '#FFFFFF' },  // Deep purple
        { bg: '#9B59B6', text: '#FFFFFF' }   // Lighter purple
        ];
        let colorIndex = 0;

        moderatorsSelect.addEventListener('change', () => {
        selectedModeratorsContainer.innerHTML = '';
        
        Array.from(moderatorsSelect.selectedOptions).forEach(option => {
            // Cycle through colors
            const currentColor = newsColors[colorIndex];
            colorIndex = (colorIndex + 1) % newsColors.length;

            // Create moderator chip with enhanced styling
            const moderatorChip = document.createElement('div');
            moderatorChip.className = 'moderator-chip';
            moderatorChip.style.backgroundColor = currentColor.bg;
            moderatorChip.style.color = currentColor.text;

            // User Icon (SVG)
            const iconSvg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            iconSvg.setAttribute('viewBox', '0 0 24 24');
            iconSvg.setAttribute('width', '24');
            iconSvg.setAttribute('height', '24');
            iconSvg.setAttribute('fill', 'none');
            iconSvg.setAttribute('stroke', currentColor.text);
            iconSvg.setAttribute('stroke-width', '2');
            iconSvg.setAttribute('stroke-linecap', 'round');
            iconSvg.setAttribute('stroke-linejoin', 'round');
            iconSvg.className = 'moderator-chip-icon';

            // User icon path
            const iconPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
            iconPath.setAttribute('d', 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2');
            const iconCircle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            iconCircle.setAttribute('cx', '12');
            iconCircle.setAttribute('cy', '7');
            iconCircle.setAttribute('r', '4');

            iconSvg.appendChild(iconPath);
            iconSvg.appendChild(iconCircle);
            
            // Moderator Name
            const text = document.createElement('span');
            text.textContent = option.text;
            text.className = 'moderator-chip-name';
            
            // Remove Button with icon
            const removeBtn = document.createElement('button');
            removeBtn.innerHTML = '&times;';
            removeBtn.className = 'moderator-chip-remove';

            // Hover and interaction effects
            moderatorChip.addEventListener('mouseover', () => {
            moderatorChip.style.transform = 'scale(1.05)';
            moderatorChip.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
            });
            
            moderatorChip.addEventListener('mouseout', () => {
            moderatorChip.style.transform = 'scale(1)';
            moderatorChip.style.boxShadow = 'none';
            });

            removeBtn.addEventListener('click', () => {
            option.selected = false;
            moderatorChip.remove();
            updateMembersCount();
            });

            // Append elements
            moderatorChip.appendChild(iconSvg);
            moderatorChip.appendChild(text);
            moderatorChip.appendChild(removeBtn);
            selectedModeratorsContainer.appendChild(moderatorChip);
        });
        
        updateMembersCount();
        });

        function updateMembersCount() {
        const count = moderatorsSelect.selectedOptions.length + 1; // Includes creator
        previewMembers.textContent = `${count} member${count > 1 ? 's' : ''}`;
        }

        // Add CSS for chip styling
        const style = document.createElement('style');
        style.textContent = `
        .moderator-chip {
            display: flex;
            align-items: center;
            border-radius: 9999px;
            overflow: hidden;
            margin: 4px;
            max-width: 250px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .moderator-chip-icon {
            width: 2rem;
            height: 2rem;
            margin-right: 0.5rem;
            padding: 0.25rem;
        }

        .moderator-chip-name {
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-right: 0.5rem;
        }

        .moderator-chip-remove {
            padding: 0.25rem 0.5rem;
            background: rgba(0,0,0,0.1);
            border: none;
            cursor: pointer;
            transition: background 0.2s ease;
            font-size: 1.2rem;
            line-height: 1;
        }

        .moderator-chip-remove:hover {
            background: rgba(0,0,0,0.2);
        }
        `;
        document.head.appendChild(style);
        });

</script>
@endsection