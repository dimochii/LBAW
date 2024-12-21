@extends('layouts.app')

@section('content')
<div class="min-h-screen font-grotesk">
    <div class="max-w-4xl mx-auto px-8 py-12">
        <div class="mb-16 group">
            <h1 class="text-6xl font-medium tracking-tight mb-4 transition-all duration-500 group-hover:tracking-widest">
                Edit Post
            </h1>
            <div class="h-1 w-24 bg-black transition-all duration-500 ease-out group-hover:w-full"></div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-8" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('news.update', $newsItem->post->id) }}" data-post-id="{{ $newsItem->post->id }}" class="space-y-12">
            @csrf
            @method('PUT')

            <fieldset>
                <legend class="sr-only">Post Details</legend>

                <div class="relative mt-4">
                    <label for="title" class="absolute left-0 -top-6 text-2xl font-medium transition-all duration-300 peer-placeholder-shown:text-3xl peer-placeholder-shown:top-2 peer-focus:-top-6 peer-focus:text-2xl">Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $newsItem->post->title) }}" class="peer w-full text-4xl font-medium bg-transparent border-b-2 border-black/10 focus:border-black focus:outline-none pb-2 pt-2 placeholder-transparent transition-all duration-300" placeholder="Enter title" required>
                </div>

                <div id="newsUrlContainer" class="space-y-4 mt-4 transform transition-all duration-500 origin-top">
                    <label for="news_url" class="block text-2xl font-medium">News URL</label>
                    <input type="url" id="news_url" name="news_url" value="{{ old('news_url', $newsItem->news_url) }}" class="w-full text-xl border-b-2 border-black/10 focus:border-black focus:outline-none pb-2 transition-colors duration-300" placeholder="https://" required>
                </div>
            </fieldset>

            <fieldset class="space-y-12">
                <legend class="sr-only">Authors</legend>
                <label class="block text-2xl font-medium ">Authors</label>
                <div class="border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30">
                    <ul class="authors-list divide-y divide-gray-200">
                        @foreach($newsItem->post->authors as $author)
                            <li class="flex items-center justify-between px-6 py-4 hover:bg-black/5 transition-colors duration-300">
                                <span class="text-lg font-medium text-black/70">
                                    {{ $author->name }}
                                </span>
                                @if($newsItem->post->authors->count() > 1)
                                    <button 
                                        type="button" 
                                        data-author-id="{{ $author->id }}" 
                                        class="remove-author-btn px-2 py-1 rounded-md bg-red-500/[.80] hover:bg-red-500 text-white font-bold"
                                    >
                                        remove
                                    </button>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="sr-only">Content</legend>

                <div class="flex items-center mb-2">
                    <label class="block text-2xl font-medium">Content</label>
                    <div class="px-2 relative inline-block">
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
                                Markdown formatting is supported. Use preview mode to see how your content will look.
                                <ul class="mt-1 list-disc list-inside">
                                    <li># for headers</li>
                                    <li>** for bold</li>
                                    <li>* for italic</li>
                                </ul>
                                <div class="absolute h-2 w-2 bg-gray-900 rotate-45 -bottom-1 left-1/2 transform -translate-x-1/2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30">
                    <div class="flex gap-4 px-6 py-3 border-b border-black/10">
                        <input type="radio" name="editor-toggle" id="write-toggle" class="hidden peer/write" checked>
                        <label for="write-toggle" class="underline-effect cursor-pointer peer-checked/write:font-bold">write</label>

                        <input type="radio" name="editor-toggle" id="preview-toggle" class="hidden peer/preview">
                        <label for="preview-toggle" class="underline-effect cursor-pointer peer-checked/preview:font-bold">preview</label>
                    </div>

                    <div class="relative">
                        <textarea id="editor-content" name="content" class="w-full h-64 p-6 text-lg font-vollkorn focus:outline-none resize-none peer-checked/preview:hidden" placeholder="Write your post content here...">{{ old('content', $newsItem->post->content) }}</textarea>
                        <div id="preview-content" 
                             class="hidden w-full h-64 p-6 text-lg font-vollkorn overflow-y-auto
                                    prose prose-a:text-[#4793AF]/[.80] hover:prose-a:text-[#4793AF]/[1] 
                                    prose-blockquote:border-l-4 prose-blockquote:border-[#4793AF]/[.50] 
                                    prose-code:bg-white/[.50] prose-code:p-1 prose-code:rounded 
                                    prose-code:text-[#4793AF]">
                        </div>

                    </div>
                </div>
            </fieldset>

            <div class="flex justify-end">
                <button type="submit" class="group relative overflow-hidden inline-flex items-center gap-4 px-8 py-4 bg-black text-white text-xl font-medium transition-transform duration-300 hover:-translate-y-1">
                    <span class="relative z-10">Save Changes</span>
                </button>
            </div>
        </form>
        
        <div class="flex justify-end mt-4">
            <form action="{{ route('post.delete', ['id' => $newsItem->post_id]) }}" method="POST" style="display: inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="group inline-flex items-center rounded gap-4 px-8 py-4 bg-rose-400 text-xl font-medium transition-all duration-300 hover:bg-rose-600 hover:text-white delete-button" data-post-id="{{ $newsItem->post_id }}">
                    Delete Post
                </button>
            </form>
        </div>

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
        )
        .replace(/^>\s*(.+)$/gm, 
            `<blockquote class="prose-blockquote border-l-4 border-[#4793AF]/[.50] pl-4 italic text-gray-700">$1</blockquote>`
        )
        .replace(/-{3,}/g, '<hr class="my-4 border-[#4793AF]/[.50]"/>')
        .replace(/\[([^\[]+)\]\(([^\)]+)\)/g, '<a href=\'$2\'>$1</a>')
        .replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold">$1</strong>')
        .replace(/\*(.+?)\*/g, '<em class="italic">$1</em>')
        .replace(/`(.*?)`/g, '<code class="bg-gray-200 p-1 rounded text-[#4793AF]">$1</code>')
        .replace(/^(?!<(h[1-6]|blockquote|hr)[^>]*>).+/gm, '$&<br>')
        .replace(/(<br>\s*){2,}/g, '<br>')
}

document.addEventListener('DOMContentLoaded', function() {
    const writeToggle = document.getElementById('write-toggle');
    const previewToggle = document.getElementById('preview-toggle');
    const editorContent = document.getElementById('editor-content');
    const previewContent = document.getElementById('preview-content');

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

    editorContent.addEventListener('input', (e) => {
        const markdownText = e.target.value;
        previewContent.innerHTML = markdownToHTML(markdownText);
    });

    writeToggle.addEventListener('change', () => {
        editorContent.classList.remove('hidden');
        previewContent.classList.add('hidden');
    });

    previewToggle.addEventListener('change', () => {
        editorContent.classList.add('hidden');
        previewContent.classList.remove('hidden');
        previewContent.innerHTML = markdownToHTML(editorContent.value);
    });

    // Initialize preview content
    previewContent.innerHTML = markdownToHTML(editorContent.value);
});

document.addEventListener('DOMContentLoaded', function() {
    const postId = document.querySelector('form').getAttribute('data-post-id');
    const authorsContainer = document.querySelector('ul.authors-list');

    authorsContainer.addEventListener('click', function(event) {
        const removeButton = event.target.closest('.remove-author-btn');
        if (!removeButton) return;

        const authorId = removeButton.getAttribute('data-author-id');
        const authorItem = removeButton.closest('li');

        fetch(`/news/${postId}/remove-authors`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                authors_to_remove: [authorId]
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Not possible to remove author');
            }
            return response.json();
        })
        .then(data => {
            // Remove o item da lista
            authorItem.remove();
        })
        .catch(error => {
            alert(error.message);
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-button');
    
    deleteButtons.forEach(button => {
        button.closest('form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to delete this post?')) {
                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            _method: 'DELETE'
                        })
                    });
                    
                    const result = await response.json();
                    
                    const notification = document.createElement('div');
                    notification.className = 'fixed left-1/2 top-16 -translate-x-1/2 w-96 p-4 rounded shadow-lg';
                    
                    if (response.ok) {
                        notification.style.backgroundColor = '#c5e6a6';  
                        notification.style.border = '2px solid #34a853';
                    } else {
                        notification.style.backgroundColor = '#ed6a5a';  
                        notification.style.border = '2px solid #a30000';
                    }
                    
                    const icon = document.createElement('span');
                    icon.className = 'inline-block mr-2';
                    icon.innerHTML = response.ok 
                        ? '✓'  
                        : '✕'; 
                    icon.style.color = response.ok ? '#34a853' : '#a30000';
                    
                    const messageText = document.createElement('span');
                    messageText.textContent = response.ok 
                        ? 'Post successfully deleted!'
                        : result.message || 'Failed to delete post.';
                    messageText.style.color = '#333333';
                    
                    notification.appendChild(icon);
                    notification.appendChild(messageText);
                    
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                        if (response.ok) {
                            window.location.href = '/global';  // Redirect after success
                        }
                    }, 3000);
                    
                } catch (error) {
                    console.error('Error:', error);

                    const errorNotification = document.createElement('div');
                    errorNotification.className = 'fixed left-1/2 top-4 -translate-x-1/2 w-96 p-4 rounded shadow-lg';
                    errorNotification.style.backgroundColor = '#ffebee';
                    errorNotification.style.border = '1px solid #a30000';
                    
                    const errorIcon = document.createElement('span');
                    errorIcon.className = 'inline-block mr-2';
                    errorIcon.innerHTML = '✕';
                    errorIcon.style.color = '#a30000';
                    
                    const errorText = document.createElement('span');
                    errorText.textContent = 'An error occurred while processing your request.';
                    errorText.style.color = '#333333';
                    
                    errorNotification.appendChild(errorIcon);
                    errorNotification.appendChild(errorText);
                    document.body.appendChild(errorNotification);
                    
                    setTimeout(() => {
                        errorNotification.remove();
                    }, 3000);
                }
            }
        });
    });
});




</script>
@endsection