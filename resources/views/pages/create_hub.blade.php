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

            <fieldset class="space-y-6">
                <legend class="text-3xl font-semibold pb-8 ">Hub Information</legend>

                <div class="relative">
                    <label for="name" class="absolute left-0 -top-6 text-2xl font-medium 
                              transition-all duration-300 peer-placeholder-shown:text-3xl 
                              peer-placeholder-shown:top-2 peer-focus:-top-6 peer-focus:text-2xl">
                        Hub Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" class="peer w-full text-4xl font-medium bg-transparent border-b-2 border-black/10 
                              focus:border-black focus:outline-none pb-2 pt-2 placeholder-transparent
                              transition-all duration-300" placeholder="Enter hub name" required>
                </div>

                <div class="relative">
                    <label for="description" class="absolute left-0 -top-3 text-2xl font-medium  
                              transition-all duration-300 peer-placeholder-shown:text-3xl 
                              peer-placeholder-shown:top-2 peer-focus:-top-6 peer-focus:text-2xl">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" name="description" class="peer w-full text-xl font-medium bg-transparent border-b-2 border-black/10 
                                 focus:border-black focus:outline-none pb-4 pt-4 placeholder-transparent
                                 transition-all duration-300" placeholder="Enter description" required></textarea>
                </div>
            </fieldset>

            <fieldset class="space-y-6 border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30 p-6">
                <legend class="sr-only">Privacy</legend>
                <label class="block text-2xl font-medium">Privacy</label>
                <div class="flex gap-8">
                    <label class="relative group flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="privacy" value="public" class="peer hidden" checked>
                        <div class="w-6 h-6 border-2 border-black rounded-full relative group-hover:border-green-400  transition-colors duration-300">
                            <div class="absolute inset-1 rounded-full bg-black transform scale-0 
                                      peer-checked:scale-100 transition-transform duration-300"></div>
                        </div>
                        <span class="text-xl group-hover:text-green-400 transition-colors duration-300">Public</span>
                    </label>

                    <label class="relative group flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="privacy" value="private" class="peer hidden">
                        <div class="w-6 h-6 border-2 border-black rounded-full relative group-hover:border-red-500 transition-colors duration-300">
                            <div class="absolute inset-1 rounded-full bg-black transform scale-0 
                                      peer-checked:scale-100 transition-transform duration-300"></div>
                        </div>
                        <span class="text-xl group-hover:text-red-500 transition-colors duration-300">Private</span>
                    </label>
                </div>
            </fieldset>

            <fieldset class="space-y-6">
                <legend class="sr-only">Moderators</legend>

                <div>
                <div class="flex items-center mb-2">
                    <label for="moderators" class="block text-2xl font-medium mr-2">Additional Moderators</label>
                    <div class="relative inline-block">
                        <svg class="help-trigger w-5 h-5 text-gray-500 hover:text-gray-700 cursor-help transition-colors duration-200" 
                            xmlns="http://www.w3.org/2000/svg" 
                            viewBox="0 0 24 24" 
                            fill="none" 
                            stroke="currentColor" 
                            stroke-width="2" 
                            stroke-linecap="round" 
                            stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <div class="help-tooltip absolute z-50 w-64 p-2 bg-gray-900 text-white text-sm rounded-lg -left-24 bottom-full mb-2">
                            <div class="relative">
                                Hold Shift key to select multiple consecutive moderators, or Ctrl key (Cmd on Mac) to select individual moderators
                                <div class="absolute h-2 w-2 bg-gray-900 rotate-45 -bottom-1 left-1/2 transform -translate-x-1/2"></div>
                            </div>
                        </div>
                    </div>
                </div>
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
            </fieldset>

            <fieldset class="space-y-6">
                <legend class="sr-only">Hub Image</legend>

                <div class="space-y-6">
                    <label for="image" class="block text-2xl font-medium">Hub Image</label>
                    <input type="file" id="image" name="image" class="w-full text-xl border-b-2 border-black/10 focus:border-black focus:outline-none pb-2 transition-colors duration-300">
                </div>
            </fieldset>

            <div class="preview-section mt-8">
                <h2 class="text-2xl font-medium mb-4">Preview</h2>
                <div class="preview-container border border-black/10 p-6">
                    <div class="flex items-center mb-4">
                        <div class="relative w-10 h-10 rounded-full overflow-hidden mr-4">
                            <img src="../images/groupdefault.jpg" alt="Community Image" class="w-full h-full object-cover rounded-full" id="preview-image">
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
                <button type="submit" class="group relative overflow-hidden inline-flex items-center gap-4 px-8 py-4 bg-black text-white text-xl font-medium transition-transform duration-300 hover:-translate-y-1">
                    <span class="relative z-10">Create Hub</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="relative z-10 h-6 w-6 transform transition-transform duration-300 group-hover:translate-x-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    <div class="absolute inset-0 bg-wpastelGreen transform translate-y-full transition-transform duration-300 group-hover:translate-y-0"></div>
                </button>
            </div>
        </form>
    </div>
</div>


<style>
    .help-tooltip {
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s;
}

.help-trigger:hover + .help-tooltip,
.help-tooltip:hover {
    visibility: visible;
    opacity: 1;
}
</style>

@endsection