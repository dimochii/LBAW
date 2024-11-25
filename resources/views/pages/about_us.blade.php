@extends('layouts.app')
@section('content')
<div class="font-grotesk">
    {{-- Hero Section --}}
    <div class="bg-pastelBlue px-8 py-16 text-center">
        <h1 class="text-6xl font-bold mb-4">Welcome to Hubs</h1>
        <p class="text-2xl max-w-3xl mx-auto">A collaborative news platform where communities share, discuss, and explore stories that matter</p>
    </div>
Copy{{-- Platform Overview --}}
<div class="container mx-auto px-8 py-16">
    <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <svg class="h-12 w-12 mb-4 fill-[#4793AF]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
            </svg>
            <h2 class="text-2xl font-semibold mb-3">Community-Driven</h2>
            <p>Join diverse hubs, contribute stories, and engage in meaningful discussions with like-minded individuals.</p>
        </div>
        <div class="bg-white shadow-lg rounded-lg p-6">
            <svg class="h-12 w-12 mb-4 fill-[#4793AF]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
            </svg>
            <h2 class="text-2xl font-semibold mb-3">Open Collaboration</h2>
            <p>Anyone can create hubs, share content, and participate in discussions, fostering an inclusive environment.</p>
        </div>
        <div class="bg-white shadow-lg rounded-lg p-6">
            <svg class="h-12 w-12 mb-4 fill-[#4793AF]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
            <h2 class="text-2xl font-semibold mb-3">Transparent Interaction</h2>
            <p>Upvote, comment, and engage with content while maintaining a respectful and constructive dialogue.</p>
        </div>
    </div>
</div>

{{-- Markdown Guide --}}
<div class="bg-pastelYellow py-16 px-8">
    <div class="container mx-auto">
        <h2 class="text-4xl font-bold mb-8 text-center">Markdown: Your Commenting Companion</h2>
        
        <div class="grid md:grid-cols-2 gap-8">
            {{-- Markdown Syntax Guide --}}
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-2xl font-semibold mb-4">Quick Markdown Syntax</h3>
                <table class="w-full">
                    <tr class="border-b">
                        <td class="py-2 font-mono">`*italic*`</td>
                        <td class="py-2"><em>italic</em></td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 font-mono">`**bold**`</td>
                        <td class="py-2"><strong>bold</strong></td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 font-mono">`# Heading 1`</td>
                        <td class="py-2 text-3xl font-bold">Heading 1</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 font-mono">`[Link](https://example.com)`</td>
                        <td class="py-2 text-[#4793AF]">Link</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-mono">`> Blockquote`</td>
                        <td class="py-2 italic border-l-4 border-[#4793AF] pl-4">Blockquote</td>
                    </tr>
                </table>
            </div>

            {{-- Live Preview Explanation --}}
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-2xl font-semibold mb-4">Live Preview Magic</h3>
                <p class="mb-4">As you type your comment, watch the magic happen in real-time:</p>
                
                <div class="bg-gray-100 p-4 rounded-lg mb-4">
                    <div class="flex mb-4">
                        <div class="w-1/2 pr-2">
                            <h4 class="font-semibold mb-2">Markdown Input</h4>
                            <textarea class="w-full h-40 p-2 border rounded font-mono text-sm" placeholder="Type your markdown here..."> Learn More</textarea>
                        </div>
                        <div class="w-1/2 pl-2">
                            <h4 class="font-semibold mb-2">Live Preview</h4>
                            <div id="markdown-preview" class="h-40 p-2 border rounded overflow-auto prose">
                                <h2>Welcome to Hubs!</h2>
                                <p>This is a <strong>collaborative</strong> platform where we share <em>interesting</em> stories.</p>
                                <blockquote>Community drives innovation.</blockquote>
                                <p><a href="#">Learn More</a></p>
                            </div>
                        </div>
                </div>
                <p class="text-sm text-gray-600">Your formatting appears instantly, making commenting a breeze!</p>
            </div>
        </div>
    </div>
</div>
</div>
Copy{{-- Join Community Section --}}
<div class="container mx-auto px-8 py-16 text-center">
    <h2 class="text-4xl font-bold mb-6">Ready to Join a Hub?</h2>
    <p class="text-xl mb-8 max-w-2xl mx-auto">Discover communities that spark your curiosity, share your insights, and connect with passionate individuals across various topics.</p>
    <a href="#"class="bg-[#4793AF] text-white px-8 py-3 rounded-full text-xl hover:bg-[#3A7A8C] transition-colors">
        Explore Hubs
    </a>
</div>
</div>
@endsection
<script>
    // Optional: Add live markdown preview functionality
    const markdownInput = document.querySelector('textarea');
    const markdownPreview = document.getElementById('markdown-preview');

    function markdownToHTML(markdown) {
        return markdown
            .replace(/^(#{1,6})\s*(.+)$/gm, (_, hashes, content) =>
                `<h${hashes.length}>${content}</h${hashes.length}>`
            )
            .replace(/^>\s*(.+)$/gm, '<blockquote>$1</blockquote>')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/\[([^\[]+)\]\(([^\)]+)\)/g, '<a href="$2">$1</a>');
    }

    if (markdownInput && markdownPreview) {
        markdownInput.addEventListener('input', (e) => {
            markdownPreview.innerHTML = markdownToHTML(e.target.value);
        });
    }
</script>