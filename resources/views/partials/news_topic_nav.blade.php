<nav class="max-w-7xl mx-auto px-6 flex flex-wrap gap-4 md:gap-8 font-light text-xl tracking-tighter">
    <a href="{{ url($url . '/?tab=News') }}"
       class="py-4 relative group {{ $activeTab === 'News' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
      news
    </a>
    <a href="{{ url($url . '/?tab=Topics') }}"
       class="py-4 relative group {{ $activeTab === 'Topics' ? 'text-gray-900 border-b-2 border-black' : 'text-gray-500 hover:text-gray-700' }}">
      topics
    </a>
</nav>
