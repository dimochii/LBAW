@extends('layouts.app')
@section('content')
    <div class="font-grotesk flex items-center bg-pastelGreen px-8 py-4 divide-y-2 divide-black border-b-2 border-black">
        <div class="text-center py-6 mr-8 animate-fadeIn">
            <h1 class="tracking-tighter font-medium text-6xl py-6 text-white mb-4">Welcome to <span class = "font-bold">whatsUp</span>!</h1>
            <p class= "text-zinc-50 font-light">This is a <strong>collaborative</strong> platform where we share <em>interesting</em> stories.</p>
            <p class= "text-zinc-50 font-light">Our platform is designed to bring you the most engaging and diverse stories from around the world. We believe in the power of community-driven content to connect people and foster meaningful discussions.</p>
        </div>
    </div>

    <section class="py-20 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-20 pointer-events-none">
            <div class="absolute top-0 right-0 w-96 h-96 bg-[#C4DBB6] rounded-full mix-blend-multiply filter blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-[#B0D0F0] rounded-full mix-blend-multiply filter blur-3xl"></div>
        </div>

        <div class="container mx-auto px-8 relative z-10">
            <div class="text-center mb-16">
                <h2 class="tracking-tighter font-medium text-6xl py-6  bg-clip-text text-transparent bg-pastelGreen">
                    Our Core Values
                </h2>
                <p class=" text-[#555555] font-light max-w-2xl mx-auto">
                    Creating a platform that nurtures creativity, collaboration, and meaningful connections.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-10">
                <div class=" backdrop-blur-lg shadow-2xl rounded-2xl p-8 border border-black transform hover:scale-105 hover:rotate-3 transition-all duration-300 group">
                    <div class="bg-gradient-to-br from-pastelGreen to-[#4CAF50] rounded-full w-20 h-20 flex items-center justify-center mb-6 mx-auto shadow-lg group-hover:animate-spin-slow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM9.763 9.51a2.25 2.25 0 013.828-1.351.75.75 0 001.06-1.06 3.75 3.75 0 00-6.38 2.25c0 1.626 1.067 3 2.572 3.572l.096.034a.75.75 0 00.496-1.415l-.096-.034a1.5 1.5 0 01-.572-1.207c0-.603.333-1.168.876-1.454zm4.474 2.736a.75.75 0 00-1.06-1.06 3.75 3.75 0 10.176 5.585.75.75 0 10-1.258-.813 2.25 2.25 0 11-.176-3.512.75.75 0 001.06 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="text-3xl tracking-tighter font-medium text-center text-pastelGreen mb-4">Community-Driven</h3>
                    <p class="text-center text-[#555555] font-light leading-relaxed">
                        Create connections through diverse hubs, share stories, and engage in transformative discussions that inspire and unite.
                    </p>
                </div>

                <div class=" backdrop-blur-lg shadow-2xl rounded-2xl p-8 border border-black transform hover:scale-105 hover:-rotate-3 transition-all duration-300 group">
                    <div class="bg-gradient-to-br from-pastelBlue to-[#1E88E5] rounded-full w-20 h-20 flex items-center justify-center mb-6 mx-auto shadow-lg group-hover:animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 12a.75.75 0 01-.75-.75v-7.5a.75.75 0 111.5 0v7.5A.75.75 0 016 12zM12 12a.75.75 0 01-.75-.75v-4.5a.75.75 0 011.5 0v4.5A.75.75 0 0112 12zM18 12a.75.75 0 01-.75-.75v-1.5a.75.75 0 111.5 0v1.5A.75.75 0 0118 12zM9 12a.75.75 0 01-.75-.75v-10.5a.75.75 0 011.5 0v10.5A.75.75 0 019 12zM15 12a.75.75 0 01-.75-.75V3.75a.75.75 0 111.5 0v7.5A.75.75 0 0115 12zM3 12a.75.75 0 01-.75-.75v-4.5a.75.75 0 011.5 0v4.5A.75.75 0 013 12zM21 12a.75.75 0 01-.75-.75v-7.5a.75.75 0 111.5 0v7.5A.75.75 0 0121 12z" />
                        </svg>
                    </div>
                    <h3 class="text-3xl tracking-tighter font-medium text-center text-pastelBlue mb-4">Open Collaboration</h3>
                    <p class="text-center text-[#555555] font-light leading-relaxed">
                        Break down barriers and empower everyone to create, share, and participate in an inclusive digital ecosystem.
                    </p>
                </div>

                <div class=" backdrop-blur-lg shadow-2xl rounded-2xl p-8 border border-black transform hover:scale-105 hover:rotate-3 transition-all duration-300 group">
                    <div class="bg-gradient-to-br from-pastelRed to-[#FF9800] rounded-full w-20 h-20 flex items-center justify-center mb-6 mx-auto shadow-lg group-hover:animate-bounce">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.804 21.644A6.707 6.707 0 006 21.75a6.721 6.721 0 003.583-1.029c.774.565 1.659.92 2.417 1.13V18c-1.682-.387-3-1.419-3-2.5a2.5 2.5 0 011.844-2.425l1.223-.463a6 6 0 00-1.181-2.107A4.98 4.98 0 0012 6.5a4.98 4.98 0 00-3.117 1.145A6 6 0 007.7 9.965l-1.222-.463A2.5 2.5 0 016 11.5c0 1.08-1.318 2.113-3 2.5v2.25c.767-.21 1.652-.565 2.417-1.13A6.721 6.721 0 009 21.75c.734 0 1.606-.211 2.25-.64V20.25a2.25 2.25 0 012.25-2.25h1.5a2.25 2.25 0 012.25 2.25v.86a6.707 6.707 0 002.25.64 6.721 6.721 0 003.583-1.029c.774.565 1.659.92 2.417 1.13V18c-1.682-.387-3-1.419-3-2.5a2.5 2.5 0 011.844-2.425l1.223-.463a6 6 0 00-1.181-2.107A4.98 4.98 0 0012 6.5a4.98 4.98 0 00-3.117 1.145A6 6 0 007.7 9.965l-1.222-.463A2.5 2.5 0 016 11.5c0 1.08-1.318 2.113-3 2.5v2.25c.767-.21 1.652-.565 2.417-1.13z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="text-3xl tracking-tighter font-medium text-center text-pastelRed mb-4">Transparent Interaction</h3>
                    <p class="text-center text-[#555555] font-light leading-relaxed">
                        Foster an environment of openness, respect, and constructive dialogue where every voice is heard and valued.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="border-b-2 border-t-2 border-black py-20 px-8">
    <div class="container mx-auto">
        <div class="text-center mb-16">
            <h2 class="font-medium text-6xl text-black mb-6">
                Discover WhatsUp Features
            </h2>
            <p class="text-[#555555] font-light max-w-2xl mx-auto text-xl">
                Explore the powerful tools that make WhatsUp your ultimate storytelling and community platform.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="border-2 border-black p-6">
                <h3 class="text-3xl font-medium text-black mb-4">
                    News Discovery
                </h3>
                <p class="text-[#555555] font-light leading-relaxed mb-6">
                    Instantly fetch trending news with rich preview thumbnails. Explore curated content from diverse sources at your fingertips.
                </p>
                <div class="max-w-screen-xl mx-auto p-5 sm:p-10 md:p-16 relative bg-white border-2 border-black">
                    <div class="grid grid-cols-1 sm:grid-cols-9 gap-10">

                        <div class="sm:col-span-6 lg:col-span-5">
                            <a href="#">
                                <div class="h-56 bg-cover text-center overflow-hidden"
                                    style="background-image: url('https://tailwindcss.com/img/card-left.jpg')"
                                    title="Woman holding a mug">
                                </div>
                            </a>
                            <div
                                class="mt-3 bg-white rounded-b lg:rounded-b-none lg:rounded-r flex flex-col justify-between leading-normal">
                                <div class="lg:pl-16">
                                    <a href="#"
                                        class="text-xs text-indigo-600 uppercase font-medium mb-3 flex items-center hover:text-gray-900 transition duration-500 ease-in-out">
                                        Fashion
                                    </a>
                                    <a href="#"
                                        class="text-gray-900 font-medium tracking-tight mb-2 hover:text-indigo-600 transition duration-500 ease-in-out">The
                                        perfect summer sweater! </a>
                                    <p class="text-gray-700 text-xs mt-2">
                                    Discover the latest trends and stories from around the globe. Stay informed and engaged with our curated content, 
                                    designed to bring you the most relevant and exciting news. Join our community and be part of the conversation.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-6 lg:col-span-4">
                            <div class="flex items-start mb-3 pb-3">
                                <a href="#" class="inline-block mr-3">
                                    <div class="w-20 h-20 bg-cover bg-center"
                                        style="background-image:url(https://media.gettyimages.com/photos/cristiano-ronaldo-of-juventus-fc-looks-dejected-during-the-uefa-of-picture-id1227967060?k=6&amp;m=1227967060&amp;s=612x612&amp;w=0&amp;h=cMSMlRyI6YAzcE_C2KgHGRLeVojHYoUhIvhwPBYv8f4=);">
                                    </div>
                                </a>
                                <div class="text-sm">
                                    <p class="text-gray-600 text-xs">Aug 18</p>
                                    <a href="#" class="text-gray-900 font-medium tracking-tight hover:text-indigo-600 leading-none">Cristiano Ronaldo
                                        of Juventus FC looks dejected during the...</a>
                                </div>
                            </div>
                            <div class="flex items-start mb-3 pb-3">
                                <a href="#" class="inline-block mr-3">
                                    <div class="w-20 h-20 bg-cover bg-center"
                                        style="background-image:url(https://media.gettyimages.com/photos/lionel-messi-and-marcandre-ter-stegen-of-fc-barcelona-waits-in-the-picture-id1266763488?k=6&amp;m=1266763488&amp;s=612x612&amp;w=0&amp;h=8vxz9HfQVfrff5N7d1lBVxtLamRQGK3J3lyHkUuuIiw=);">
                                    </div>
                                </a>
                                <div class="text-sm w-2/3">
                                    <p class="text-gray-600 text-xs">Jan 18</p>
                                    <a href="#" class="text-gray-900 font-medium tracking-tight hover:text-indigo-600 leading-none">Barcelona v Bayern
                                        Munich </a>
                                </div>
                            </div>
                            <div class="flex items-start mb-3 pb-3">
                                <a href="#" class="inline-block mr-3">
                                    <div class="w-20 h-20 bg-cover bg-center"
                                        style="background-image:url(https://media.gettyimages.com/photos/cristiano-ronaldo-of-juventus-fc-looks-dejected-during-the-uefa-of-picture-id1227967060?k=6&amp;m=1227967060&amp;s=612x612&amp;w=0&amp;h=cMSMlRyI6YAzcE_C2KgHGRLeVojHYoUhIvhwPBYv8f4=);">
                                    </div>
                                </a>
                                <div class="text-sm">
                                    <p class="text-gray-600 text-xs">Aug 18</p>
                                    <a href="#" class="text-gray-900 font-medium tracking-tight hover:text-indigo-600 leading-none">Cristiano Ronaldo
                                        of Juventus FC looks dejected during the...</a>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <a href="#" class="inline-block mr-3">
                                    <div class="w-20 h-20 bg-cover bg-center"
                                        style="background-image:url(https://media.gettyimages.com/photos/lionel-messi-of-fc-barcelona-looks-dejected-following-his-teams-in-picture-id1266341828?k=6&amp;m=1266341828&amp;s=612x612&amp;w=0&amp;h=FZi-bSrIlOEE01780h79GsbBYPqZo2l3aaCxoktWADY=);">
                                    </div>
                                </a>
                                <div class="text-sm w-2/3">
                                    <p class="text-gray-600 text-xs">July 23</p>
                                    <a href="#" class="text-gray-900 font-medium tracking-tight hover:text-indigo-600 leading-none">Barcelona v Bayern
                                        Munich - UEFA Champions League </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Community Connections -->
            <div class="border-2 border-black p-6">
                <h3 class="text-3xl font-medium text-black mb-4">
                    Community Connections
                </h3>
                <p class="text-[#555555] font-light leading-relaxed mb-6">
                    Create and join interest-based communities. Find your tribe, share passions, and expand your digital network.
                </p>
                <!--  Reddit-like Community Threads -->
                <div class="border-2 border-black p-4 mb-4 bg-gray-50">
                    <div class="flex items-center space-x-4 p-4 rounded-md mb-4">
                        <img src="https://www.redditstatic.com/avatars/defaults/v2/avatar_default_3.png"
                             alt="Community Image" class="rounded-full ring-2 ring-black h-36 w-36 sm:h-24 sm:w-24 md:h-32 md:w-32 lg:h-40 lg:w-40 object-cover">
                        <div>
                            <h4 class="text-xl font-semibold text-gray-800">h\Global Economy Discussions</h4>
                            <p class="text-sm text-gray-600">A space to talk about the world's financial situation and its future...</p>
                            <div class="flex items-center space-x-4 mt-4">
                                <button class="mt-2 px-4 py-1 text-sm rounded-full hover:opacity-80 transition-colors 
                                        bg-black text-[#F4F2ED]">follow +</button>
                                <span class="text-gray-600">1.2k followers</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Discussions  -->
            <div class="border-2 border-black md:col-span-2 p-6">
                <h3 class="text-3xl font-medium text-black mb-4">
                    Interactive Discussions
                </h3>
                <p class="text-[#555555] font-light leading-relaxed mb-6">
                    Engage deeply with rich commenting tools. Share insights, ask questions, and connect with like-minded individuals.
                </p>
                <section class="">
                    <div class="container px-0 mx-auto sm:px-5">

                        <div
                            class="flex-col w-full py-4 mx-auto bg-white border-b-2 border-r-2 border-gray-200 sm:px-4 sm:py-4 md:px-4 sm:rounded-lg sm:shadow-sm md:w-2/3">
                            <div class="flex flex-row">
                                <img class="object-cover w-12 h-12 border-2 border-gray-300 rounded-full" alt="Noob master's avatar"
                                    src="https://images.unsplash.com/photo-1517070208541-6ddc4d3efbcb?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&faces=1&faceindex=1&facepad=2.5&w=500&h=500&q=80">
                                <div class="flex-col mt-1">
                                    <div class="flex items-center flex-1 px-4 font-bold leading-tight">Noob master
                                        <span class="ml-2 text-xs font-normal text-gray-500">2 weeks ago</span>
                                    </div>
                                    <div class="flex-1 px-2 ml-2 text-sm font-medium leading-loose text-gray-600">Wow!!! how was created this website?
                                    </div>
                                    <button class="inline-flex items-center px-1 pt-2 ml-1 flex-column">
                                    <svg class="h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M21,21H3L12,3Z" />
                                            </svg>
                                    <p> 22</p>
                                    <svg class="h-6 rotate-180" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M21,21H3L12,3Z" />
                                            </svg>
                                    </button>
                                    <button class="inline-flex items-center px-1 -ml-1 flex-column">
                                    <svg
                                        class="cursor-pointer ml-4 h-5 min-w-5 transition-all ease-out  group-hover/wrapper:fill-[#F4F2ED] group-hover/wrapper:hover:fill-blue-400"
                                        viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                        <g id="Layer_2" data-name="Layer 2">
                                        <g id="invisible_box" data-name="invisible box">
                                            <rect width="48" height="48" fill="none" />
                                            <rect width="48" height="48" fill="none" />
                                        </g>
                                        <g id="icons_Q2" data-name="icons Q2">
                                            <path
                                            d="M42,4H6A2,2,0,0,0,4,6V42a2,2,0,0,0,2,2,2,2,0,0,0,1.4-.6L15.2,36H42a2,2,0,0,0,2-2V6a2,2,0,0,0-2-2Z" />
                                        </g>
                                        </g>
                                    </svg>
                                    </button>
                                </div>
                            </div>
                            <hr class="my-2 ml-16 border-gray-200">
                            <div class="flex flex-row pt-1 md-10 md:ml-16">
                                <img class="w-12 h-12 border-2 border-gray-300 rounded-full" alt="Emily's avatar"
                                    src="https://images.unsplash.com/photo-1581624657276-5807462d0a3a?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&fit=facearea&faces=1&faceindex=1&facepad=2.5&w=500&h=500&q=80">
                                <div class="flex-col mt-1">
                                    <div class="flex items-center flex-1 px-4 font-bold leading-tight">Emily
                                        <span class="ml-2 text-xs font-normal text-gray-500">5 days ago</span>
                                    </div>
                                    <div class="flex-1 px-2 ml-2 text-sm font-medium leading-loose text-gray-600">I love this! Can you
                                        share the code?
                                    </div>
                                    <button class="inline-flex items-center px-1 pt-2 ml-1 flex-column">
                                    <svg class="h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M21,21H3L12,3Z" />
                                            </svg>
                                    <p> 10</p>
                                    <svg class="h-6 rotate-180" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M21,21H3L12,3Z" />
                                            </svg>
                                    </button>
                                    <button class="inline-flex items-center px-1 -ml-1 flex-column">
                                    <svg
                                        class="cursor-pointer ml-4 h-5 min-w-5  transition-all ease-out  group-hover/wrapper:fill-[#F4F2ED] group-hover/wrapper:hover:fill-blue-400"
                                        viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                        <g id="Layer_2" data-name="Layer 2">
                                        <g id="invisible_box" data-name="invisible box">
                                            <rect width="48" height="48" fill="none" />
                                            <rect width="48" height="48" fill="none" />
                                        </g>
                                        <g id="icons_Q2" data-name="icons Q2">
                                            <path
                                            d="M42,4H6A2,2,0,0,0,4,6V42a2,2,0,0,0,2,2,2,2,0,0,0,1.4-.6L15.2,36H42a2,2,0,0,0,2-2V6a2,2,0,0,0-2-2Z" />
                                        </g>
                                        </g>
                                    </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex-col w-full py-4 mx-auto mt-3 bg-white border-b-2 border-r-2 border-gray-200 sm:px-4 sm:py-4 md:px-4 sm:rounded-lg sm:shadow-sm md:w-2/3">
                            <div class="flex flex-row md-10">
                                <img class="w-12 h-12 border-2 border-gray-300 rounded-full" alt="Anonymous's avatar"
                                    src="https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&faces=1&faceindex=1&facepad=2.5&w=500&h=500&q=80">
                                <div class="flex-col mt-1">
                                    <div class="flex items-center flex-1 px-4 font-bold leading-tight">Anonymous
                                        <span class="ml-2 text-xs font-normal text-gray-500">3 days ago</span>
                                    </div>
                                    <div class="flex-1 px-2 ml-2 text-sm font-medium leading-loose text-gray-600">This website is so cool!
                                    </div>
                                    <button class="inline-flex items-center px-1 pt-2 ml-1 flex-column">
                                    <svg class="h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M21,21H3L12,3Z" />
                                            </svg>
                                    <p> 9</p>
                                    <svg class="h-6 rotate-180" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M21,21H3L12,3Z" />
                                            </svg>
                                    </button>
                                    <button class="inline-flex ">
                                    <svg
                                        class="cursor-pointer ml-4 h-5 min-w-5  transition-all ease-out  group-hover/wrapper:fill-[#F4F2ED] group-hover/wrapper:hover:fill-blue-400"
                                        viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                                        <g id="Layer_2" data-name="Layer 2">
                                        <g id="invisible_box" data-name="invisible box">
                                            <rect width="48" height="48" fill="none" />
                                            <rect width="48" height="48" fill="none" />
                                        </g>
                                        <g id="icons_Q2" data-name="icons Q2">
                                            <path
                                            d="M42,4H6A2,2,0,0,0,4,6V42a2,2,0,0,0,2,2,2,2,0,0,0,1.4-.6L15.2,36H42a2,2,0,0,0,2-2V6a2,2,0,0,0-2-2Z" />
                                        </g>
                                        </g>
                                    </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
        </div>
        </div>
    </div>
</section>
    <div class="bg-pastelBlue py-16 px-8 animate-fadeInUp divide-y-2 divide-black border-b-2 border-black">
        <div class="container mx-auto py-4">
            <h2 class=" py-4 mb-8 text-center tracking-tighter font-medium text-6xl">Markdown: Your Commenting Companion</h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition-transform">
                    <h3 class="text-2xl tracking-tighter font-medium mb-4">Quick Markdown Syntax</h3>
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

                <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition-transform">
                    <h3 class="text-2xl tracking-tighter font-medium mb-4">Live Preview Magic</h3>
                    <p class="mb-4 font-light">As you type your comment, watch the magic happen in real-time:</p>
                    
                    <div class="bg-gray-100 p-4 rounded-lg mb-4">
                        <div class="flex mb-4">
                            <div class="w-1/2 pr-2">
                                <h4 class="font-semibold mb-2">Markdown Input</h4>
                                <textarea class="w-full h-40 p-2 border rounded font-mono text-sm" placeholder="Type your markdown here..."> 
                                    # Welcome to Hubs!
                                    This is a **collaborative** platform where we share *interesting* stories.
                                    > Community drives innovation.
                                    Learn More](#)
                                </textarea>
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
                    <p class="text-sm text-gray-600 font-light">Your formatting appears instantly, making commenting a breeze!</p>
                </div>
            </div>
        </div>
    </div>
    </div>
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="tracking-tighter font-medium text-6xl mb-12 text-center text-gray-800 animate-fadeIn py-8">
                Meet the Team
            </h2>
            <div class=" font-light grid gap-8 sm:grid-cols-2 lg:grid-cols-4 animate-fadeInUp">
                <div class="text-center hover:shadow-lg p-4 rounded-lg bg-white transition-shadow flex flex-col items-center min-h-[280px]">
                    <img src="https://preview.redd.it/iaqjfd8km8a71.png?width=587&format=png&auto=webp&s=9b1a5bb2609f2741fd5b8a0db192c9584167dd8a" alt="Member 1" class="rounded-full h-32 w-32 mb-4 transform hover:scale-110 transition-transform shadow-lg">
                    <h3 class="text-xl tracking-tighter font-medium text-gray-800">Diana Nunes</h3>
                    <p class="text-gray-600">up20228247</p>
                </div>
                <div class="text-center hover:shadow-lg p-4 rounded-lg bg-white transition-shadow flex flex-col items-center min-h-[280px]">
                    <img src="https://preview.redd.it/w7rm34sdm8a71.png?width=587&format=png&auto=webp&s=986bd66673b1ae29e7f4532424396fcd46f3b72d" alt="Member 2" class="rounded-full h-32 w-32 mb-4 transform hover:scale-110 transition-transform shadow-lg">
                    <h3 class="text-xl tracking-tighter font-medium text-gray-800">Teresa Mascarenhas</h3>
                    <p class="text-gray-600">up202206828</p>
                </div>
                <div class="text-center hover:shadow-lg p-4 rounded-lg bg-white transition-shadow flex flex-col items-center min-h-[280px]">
                    <img src="https://preview.redd.it/oicm80xnm8a71.png?width=587&format=png&auto=webp&s=93af0e66b9c6aa62b1201921f2d555ee510d4074" alt="Member 3" class="rounded-full h-32 w-32 mb-4 transform hover:scale-110 transition-transform shadow-lg">
                    <h3 class="text-xl tracking-tighter font-medium text-gray-800">Tiago Monteiro</h3>
                    <p class="text-gray-600">up202108391</p>
                </div>
                <div class="text-center hover:shadow-lg p-4 rounded-lg bg-white transition-shadow flex flex-col items-center min-h-[280px]">
                    <img src="https://preview.redd.it/hpyfttbpn8a71.png?width=587&format=png&auto=webp&s=fcc57f3d1063223b4755a9568883a70979e9613a" alt="Member 4" class="rounded-full h-32 w-32 mb-4 transform hover:scale-110 transition-transform shadow-lg">
                    <h3 class="text-xl tracking-tighter font-medium text-gray-800">Vasco Costa</h3>
                    <p class="text-gray-600">up202109923</p>
                </div>
            </div>
        </div>
    </section>


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
