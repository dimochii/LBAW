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

        <form method="POST" action="{{ route('communities.store') }}" class="space-y-12">
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

            <div class="space-y-6">
                <label class="block text-2xl font-medium">Privacy</label>
                <div class="flex gap-8">
                    <label class="relative group flex items-center gap-3 cursor-pointer">
                        <input type="radio" 
                               name="privacy" 
                               value="public" 
                               class="peer hidden" 
                               checked>
                        <div class="w-6 h-6 border-2 border-black rounded-full relative
                                  group-hover:border-pastelGreen  transition-colors duration-300">
                            <div class="absolute inset-1 rounded-full bg-black transform scale-0 
                                      peer-checked:scale-100 transition-transform duration-300"></div>
                        </div>
                        <span class="text-xl group-hover:text-pastelGreen  transition-colors duration-300">Public</span>
                    </label>

                    <label class="relative group flex items-center gap-3 cursor-pointer">
                        <input type="radio" 
                               name="privacy" 
                               value="private" 
                               class="peer hidden">
                        <div class="w-6 h-6 border-2 border-black rounded-full relative
                                  group-hover:border-pastelGreen transition-colors duration-300">
                            <div class="absolute inset-1 rounded-full bg-black transform scale-0 
                                      peer-checked:scale-100 transition-transform duration-300"></div>
                        </div>
                        <span class="text-xl group-hover:text-pastelGreen  transition-colors duration-300">Private</span>
                    </label>
                </div>
            </div>

            <div class="space-y-6">
                <label for="image_id" class="block text-2xl font-medium">Hub Image (Optional)</label>
                <input type="number" 
                       id="image_id" 
                       name="image_id" 
                       class="w-full text-xl border-b-2 border-black/10 focus:border-black 
                              focus:outline-none pb-2 transition-colors duration-300"
                       placeholder="Enter image ID">
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

</script>
@endsection
