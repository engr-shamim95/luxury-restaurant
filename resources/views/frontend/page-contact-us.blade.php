@extends('layouts.frontend')

@section('title', ($page->meta_title ?: $page->title) . ' — ' . $siteName)
@section('meta_description', $page->meta_description ?: 'Get in touch with ' . $siteName)

@section('content')
    <!-- Elegant Page Hero -->
    <section class="relative py-28 sm:py-36 bg-gray-950 overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center opacity-30" style="background-image: url('{{ asset('storage/settings/hero-bg.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-gray-950/60 to-gray-950"></div>
        <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
            <span class="text-amber-500 font-medium tracking-[0.3em] uppercase text-xs mb-6 block">We'd Love to Hear From You</span>
            <h1 class="text-5xl sm:text-6xl lg:text-7xl text-white mb-6" style="font-family: 'Playfair Display', serif;">
                {{ $page->title }}
            </h1>
            <div class="w-16 h-[2px] bg-amber-600 mx-auto"></div>
        </div>
    </section>

    <!-- Contact Info Cards + Map Section -->
    <section class="py-20 sm:py-28 bg-white">
        <div class="max-w-6xl mx-auto px-6 lg:px-8">
            <!-- Info Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
                <!-- Address Card -->
                <div class="text-center p-10 border border-gray-100 hover:border-amber-200 transition-colors duration-300 group">
                    <div class="w-14 h-14 rounded-full border border-amber-600/40 flex items-center justify-center mx-auto mb-6 group-hover:bg-amber-50 transition-colors duration-300">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"></path></svg>
                    </div>
                    <h3 class="text-sm font-semibold tracking-[0.15em] uppercase text-gray-900 mb-3">Visit Us</h3>
                    <p class="text-gray-500 text-sm font-light leading-relaxed">123 Culinary Boulevard<br>Foodville, FL 33012</p>
                </div>

                <!-- Phone Card -->
                <div class="text-center p-10 border border-gray-100 hover:border-amber-200 transition-colors duration-300 group">
                    <div class="w-14 h-14 rounded-full border border-amber-600/40 flex items-center justify-center mx-auto mb-6 group-hover:bg-amber-50 transition-colors duration-300">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"></path></svg>
                    </div>
                    <h3 class="text-sm font-semibold tracking-[0.15em] uppercase text-gray-900 mb-3">Call Us</h3>
                    <p class="text-gray-500 text-sm font-light leading-relaxed">(555) 123-4567</p>
                    <p class="text-gray-400 text-xs font-light mt-2">Mon – Sun, 10 AM – 10 PM</p>
                </div>

                <!-- Email Card -->
                <div class="text-center p-10 border border-gray-100 hover:border-amber-200 transition-colors duration-300 group">
                    <div class="w-14 h-14 rounded-full border border-amber-600/40 flex items-center justify-center mx-auto mb-6 group-hover:bg-amber-50 transition-colors duration-300">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path></svg>
                    </div>
                    <h3 class="text-sm font-semibold tracking-[0.15em] uppercase text-gray-900 mb-3">Email Us</h3>
                    <p class="text-gray-500 text-sm font-light leading-relaxed">info@bellavista.test</p>
                    <p class="text-gray-400 text-xs font-light mt-2">We reply within 24 hours</p>
                </div>
            </div>

            <!-- Contact Form + Map Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                <!-- Contact Form -->
                <div>
                    <span class="text-amber-600 font-semibold tracking-[0.2em] uppercase text-xs mb-4 block">Send a Message</span>
                    <h2 class="text-3xl sm:text-4xl text-gray-900 mb-10 leading-tight" style="font-family: 'Playfair Display', serif;">Get In Touch</h2>

                    <form class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold tracking-wider uppercase text-gray-500 mb-2">Your Name</label>
                                <input type="text" placeholder="John Doe" class="w-full px-0 py-3 bg-transparent border-0 border-b border-gray-200 focus:border-amber-600 focus:ring-0 text-gray-900 text-sm font-light placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold tracking-wider uppercase text-gray-500 mb-2">Email Address</label>
                                <input type="email" placeholder="john@example.com" class="w-full px-0 py-3 bg-transparent border-0 border-b border-gray-200 focus:border-amber-600 focus:ring-0 text-gray-900 text-sm font-light placeholder-gray-300 transition-colors">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold tracking-wider uppercase text-gray-500 mb-2">Subject</label>
                            <input type="text" placeholder="Reservation, Catering, Feedback..." class="w-full px-0 py-3 bg-transparent border-0 border-b border-gray-200 focus:border-amber-600 focus:ring-0 text-gray-900 text-sm font-light placeholder-gray-300 transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold tracking-wider uppercase text-gray-500 mb-2">Your Message</label>
                            <textarea rows="4" placeholder="Tell us how we can help you..." class="w-full px-0 py-3 bg-transparent border-0 border-b border-gray-200 focus:border-amber-600 focus:ring-0 text-gray-900 text-sm font-light placeholder-gray-300 transition-colors resize-none"></textarea>
                        </div>
                        <div class="pt-4">
                            <button type="submit" class="px-10 py-4 bg-gray-900 hover:bg-black text-white text-xs font-bold tracking-[0.2em] uppercase transition-all duration-300">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Map / Visual Column -->
                <div class="flex flex-col justify-center">
                    <div class="aspect-square bg-gray-100 overflow-hidden relative border border-gray-100">
                        <!-- Placeholder map visual -->
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3592.8697576041427!2d-80.18!3d25.77!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjXCsDQ2JzEyLjAiTiA4MMKwMTAnNDguMCJX!5e0!3m2!1sen!2sus!4v1234567890" 
                            width="100%" 
                            height="100%" 
                            style="border:0; filter: grayscale(80%) contrast(1.1);" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    <div class="mt-6 p-6 bg-gray-50 border border-gray-100">
                        <h4 class="text-sm font-semibold tracking-[0.1em] uppercase text-gray-900 mb-2">Opening Hours</h4>
                        <div class="text-sm text-gray-500 font-light space-y-1">
                            <div class="flex justify-between"><span>Monday – Friday</span><span>11:00 AM – 10:00 PM</span></div>
                            <div class="flex justify-between"><span>Saturday</span><span>10:00 AM – 11:00 PM</span></div>
                            <div class="flex justify-between"><span>Sunday</span><span>10:00 AM – 9:00 PM</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dark CTA Strip -->
    <section class="py-16 bg-gray-900">
        <div class="max-w-3xl mx-auto px-6 text-center">
            <p class="text-lg text-gray-300 font-light italic mb-6" style="font-family: 'Playfair Display', serif;">"Great food is the foundation of genuine happiness."</p>
            <span class="text-amber-500 text-xs tracking-[0.2em] uppercase font-semibold">— Auguste Escoffier</span>
        </div>
    </section>
@endsection
