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

            <div class="relative">
                <label for="title" class="absolute left-0 -top-6 text-2xl font-medium text-black/60 transition-all duration-300 peer-placeholder-shown:text-3xl peer-placeholder-shown:top-2 peer-focus:-top-6 peer-focus:text-2xl">Title</label>
                <input type="text" id="title" name="title" value="{{ old('title', $newsItem->post->title) }}" class="peer w-full text-4xl font-medium bg-transparent border-b-2 border-black/10 focus:border-black focus:outline-none pb-2 pt-2 placeholder-transparent transition-all duration-300" placeholder="Enter title" required>
            </div>

            <div id="newsUrlContainer" class="space-y-4 transform transition-all duration-500 origin-top">
                <label for="news_url" class="block text-2xl font-medium">News URL</label>
                <input type="url" id="news_url" name="news_url" value="{{ old('news_url', $newsItem->news_url) }}" class="w-full text-xl border-b-2 border-black/10 focus:border-black focus:outline-none pb-2 transition-colors duration-300" placeholder="https://" required>
            </div>

            <div class="space-y-12">
                <label class="block text-2xl font-medium text-black/60">Authors</label>
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
            </div>

            <div class="space-y-4">
                <label class="block text-2xl font-medium">Content</label>
                <div class="border-2 border-black/10 rounded-lg overflow-hidden transition-all duration-300 hover:border-black/30">
                    <div class="flex gap-4 px-6 py-3 border-b border-black/10">
                        <input type="radio" name="editor-toggle" id="write-toggle" class="hidden peer/write" checked>
                        <label for="write-toggle" class="underline-effect cursor-pointer peer-checked/write:font-bold">write</label>

                        <input type="radio" name="editor-toggle" id="preview-toggle" class="hidden peer/preview">
                        <label for="preview-toggle" class="underline-effect cursor-pointer peer-checked/preview:font-bold">preview</label>
                    </div>

                    <div class="relative">
                        <textarea id="editor-content" name="content" class="w-full h-64 p-6 text-lg font-vollkorn focus:outline-none resize-none peer-checked/preview:hidden" placeholder="Write your post content here...">{{ old('content', $newsItem->post->content) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-8">
                <form action="{{ route('post.delete', ['id' => $newsItem->post_id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="group inline-flex items-center rounded gap-4 px-8 py-4 bg-rose-400 text-xl font-medium transition-all duration-300 hover:bg-rose-600 hover:text-white">
                        <span class="relative z-10">Delete Post</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('news.update', $newsItem->post->id) }}">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="group relative overflow-hidden inline-flex items-center gap-4 px-8 py-4 bg-black text-white text-xl font-medium transition-transform duration-300 hover:-translate-y-1">
                        <span class="relative z-10">Save Changes</span>
                    </button>
                </form>
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

</script>
@endsection