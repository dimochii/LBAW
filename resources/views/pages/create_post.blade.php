@extends('layouts.app')

@section('content')
<div class="min-h-screen font-grotesk">
    <div class="max-w-4xl mx-auto px-8 py-12">
        <!-- Header with animation -->
        <div class="mb-16 group">
            <h1 class="text-6xl font-medium tracking-tight mb-4 transition-all duration-500 group-hover:tracking-widest">
                Create Post
            </h1>
            <div class="h-1 w-24 bg-black transition-all duration-500 ease-out group-hover:w-full"></div>
        </div>

        <form method="POST" action="{{ route('post.store') }}" class="space-y-12">
            @csrf
            
            <!-- Title Input with floating animation -->
            <div class="relative">
                <label for="title" 
                       class="absolute left-0 -top-6 text-2xl font-medium text-black/60 
                              transition-all duration-300 peer-placeholder-shown:text-3xl 
                              peer-placeholder-shown:top-2 peer-focus:-top-6 peer-focus:text-2xl">
                    Title
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       class="peer w-full text-4xl font-medium bg-transparent border-b-2 border-black/10 
                              focus:border-black focus:outline-none pb-2 pt-2 placeholder-transparent
                              transition-all duration-300"
                       placeholder="Enter title"
                       required>
            </div>

            <!-- Post Type Selection with slide effect -->
            <div class="space-y-6">
                <label class="block text-2xl font-medium">Post Type</label>
                <div class="flex gap-8">
                    <!-- News Radio -->
                    <label class="relative group flex items-center gap-3 cursor-pointer">
                        <input type="radio" 
                               name="type" 
                               value="news"
                               class="peer hidden" 
                               checked>
                        <div class="w-6 h-6 border-2 border-black rounded-full relative
                                  group-hover:border-whatsup-green transition-colors duration-300">
                            <div class="absolute inset-1 rounded-full bg-black transform scale-0 
                                      peer-checked:scale-100 transition-transform duration-300"></div>
                        </div>
                        <span class="text-xl group-hover:text-whatsup-green transition-colors duration-300">News</span>
                    </label>

                    <!-- Topic Radio -->
                    <label class="relative group flex items-center gap-3 cursor-pointer">
                        <input type="radio" 
                               name="type" 
                               value="topic"
                               class="peer hidden">
                        <div class="w-6 h-6 border-2 border-black rounded-full relative
                                  group-hover:border-whatsup-green transition-colors duration-300">
                            <div class="absolute inset-1 rounded-full bg-black transform scale-0 
                                      peer-checked:scale-100 transition-transform duration-300"></div>
                        </div>
                        <span class="text-xl group-hover:text-whatsup-green transition-colors duration-300">Topic</span>
                    </label>
                </div>
            </div>

            <!-- News URL Input (conditionally shown) -->
            <div id="newsUrlContainer" 
                 class="space-y-4 transform transition-all duration-500 origin-top">
                <label for="news_url" class="block text-2xl font-medium">News URL</label>
                <input type="url" 
                       id="news_url" 
                       name="news_url" 
                       class="w-full text-xl border-b-2 border-black/10 focus:border-black 
                              focus:outline-none pb-2 transition-colors duration-300"
                       placeholder="https://">
            </div>

            <!-- Content Editor with tabs -->
            <div class="space-y-4">
                <label class="block text-2xl font-medium">Content</label>
                <div class="border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30">
                    <!-- Editor Tabs -->
                    <div class="flex gap-4 px-6 py-3 border-b border-black/10">
                        <input type="radio" name="editor-toggle" id="write-toggle" class="hidden peer/write" checked>
                        <label for="write-toggle" class="underline-effect cursor-pointer peer-checked/write:font-bold">
                            write
                        </label>

                        <input type="radio" name="editor-toggle" id="preview-toggle" class="hidden peer/preview">
                        <label for="preview-toggle" class="underline-effect cursor-pointer peer-checked/preview:font-bold">
                            preview
                        </label>
                    </div>

                    <!-- Editor Content -->
                    <div class="relative">
                        <textarea id="editor-content" 
                                name="content" 
                                class="w-full h-64 p-6 text-lg font-vollkorn focus:outline-none resize-none
                                       peer-checked/preview:hidden"
                                placeholder="Write your post content here..."></textarea>
                        <div id="preview-content" 
                             class="hidden w-full h-64 p-6 text-lg font-vollkorn overflow-y-auto
                                    prose prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] 
                                    prose-blockquote:border-l-4 prose-blockquote:border-[#4793AF]/[.50] 
                                    prose-code:bg-white/[.50] prose-code:p-1 prose-code:rounded 
                                    prose-code:text-[#4793AF]">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button with hover effect -->
            <div class="flex justify-end">
                <button type="submit" 
                        class="group relative overflow-hidden bg-black text-white px-8 py-4 text-xl 
                               font-medium transition-transform duration-300 hover:-translate-y-1">
                    <span class="relative z-10">Publish Post</span>
                    <div class="absolute inset-0 bg-whatsup-green transform translate-y-full 
                               transition-transform duration-300 group-hover:translate-y-0"></div>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.underline-effect {
    position: relative;
}

.underline-effect::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 2px;
    bottom: -4px;
    left: 0;
    background-color: currentColor;
    transform: scaleX(0);
    transform-origin: center;
    transition: transform 0.3s ease-out;
}

.underline-effect:hover::after,
input:checked + .underline-effect::after {
    transform: scaleX(1);
}
</style>

<script>
function markdownToHTML(markdown) {
    const headingClasses = {
        1: "m-0 text-4xl font-bold",
        2: "m-0 text-3xl font-bold",
        3: "m-0 text-2xl font-bold",
        4: "m-0 text-xl font-bold",
        5: "m-0 text-lg font-bold",
        6: "m-0 text-base font-bold",
    } 

    return markdown
        .replace(/^(#{1,6})\s*(.+)$/gm, (_, hashes, content) =>
            `<h${hashes.length} class="${headingClasses[hashes.length]}">${content}</h${hashes.length}>`
        ) // Headings
        .replace(/^>\s*(.+)$/gm, 
            `<blockquote class="prose-blockquote border-l-4 border-[#4793AF]/[.50] pl-4 italic text-gray-700">$1</blockquote>`
        ) // Blockquotes
        .replace(/-{3,}/g, '<hr class="my-4 border-[#4793AF]/[.50]"/>')
        .replace(/\[([^\[]+)\]\(([^\)]+)\)/g, '<a href=\'$2\'>$1</a>')
        .replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold">$1</strong>') // Bold
        .replace(/\*(.+?)\*/g, '<em class="italic">$1</em>') // Italics
        .replace(/`(.*?)`/g, '<code class="bg-gray-200 p-1 rounded text-[#4793AF]">$1</code>') // Inline Code
        .replace(/^(?!<(h[1-6]|blockquote|hr)[^>]*>).+/gm, '$&<br>')
        .replace(/(<br>\s*){2,}/g, '<br>') // Add <br> for plain text lines
}

document.addEventListener('DOMContentLoaded', function() {
    // Type selection handling
    const typeInputs = document.querySelectorAll('input[name="type"]');
    const newsUrlContainer = document.getElementById('newsUrlContainer');
    
    function toggleNewsUrl() {
        const isNews = document.querySelector('input[name="type"]:checked').value === 'news';
        newsUrlContainer.classList.toggle('scale-y-0', !isNews);
        newsUrlContainer.classList.toggle('opacity-0', !isNews);
        newsUrlContainer.classList.toggle('hidden', !isNews);
    }
    
    typeInputs.forEach(input => {
        input.addEventListener('change', toggleNewsUrl);
    });

    // Editor tabs and preview handling
    const writeToggle = document.getElementById('write-toggle');
    const previewToggle = document.getElementById('preview-toggle');
    const editorContent = document.getElementById('editor-content');
    const previewContent = document.getElementById('preview-content');

    // Tab key behavior for indentation
    editorContent.addEventListener("keydown", (e) => {
        if (e.key === "Tab") {
            e.preventDefault();
            const start = editorContent.selectionStart;
            const end = editorContent.selectionEnd;
            editorContent.value = editorContent.value.substring(0, start) + "  " + 
                                editorContent.value.substring(end);
            editorContent.selectionStart = editorContent.selectionEnd = start + 2;
        }
    });

    // Live preview updates
    editorContent.addEventListener('input', (e) => {
        const markdownText = e.target.value;
        previewContent.innerHTML = markdownToHTML(markdownText);
    });

    // Toggle preview visibility
    writeToggle.addEventListener('change', () => {
        editorContent.classList.remove('hidden');
        previewContent.classList.add('hidden');
    });

    previewToggle.addEventListener('change', () => {
        editorContent.classList.add('hidden');
        previewContent.classList.remove('hidden');
        // Update preview content
        previewContent.innerHTML = markdownToHTML(editorContent.value);
    });

    // Initialize state
    toggleNewsUrl();
});
</script>
@endsection