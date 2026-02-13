@php
    $slides = [
        ['clicks' => '76k', 'imp' => '865k', 'ctr' => '8.8%', 'pos' => '8.5'],
        ['clicks' => '125k', 'imp' => '1.45m', 'ctr' => '8.6%', 'pos' => '9.2'],
        ['clicks' => '21k', 'imp' => '245k', 'ctr' => '8.5%', 'pos' => '11.2'],
        ['clicks' => '92k', 'imp' => '1.1m', 'ctr' => '8.3%', 'pos' => '10.1'],
        ['clicks' => '67k', 'imp' => '745k', 'ctr' => '8.9%', 'pos' => '9.5'],
    ];
@endphp
<section class="max-w-[1440px] mx-auto md:px-8 px-2" id="features">
    <div class="max-w-[720px] mx-auto text-center space-y-4">
        <h2 class="transition-all duration-1000 ease-out opacity-100 translate-y-0 text-3xl md:text-5xl font-bold tracking-tight text-gray-900"
            data-aos="fade-up">They get clicks. You?</h2>
        <p class="subtitle text-[#777] text-lg md:text-xl transition-all duration-1000 ease-out delay-100 opacity-100 translate-y-0"
            data-aos="fade-up" data-aos-delay="100">Get visitors online from high-quality articles tailored for your
            business. No skills needed.</p>
    </div>
    <div class="relative transition-all duration-1000 ease-out delay-200 opacity-100 translate-y-0" data-aos="fade-up"
        data-aos-delay="200">
        <div class="overflow-x-hidden overflow-y-visible py-8">
            <style>
                @keyframes marquee {
                    0% {
                        transform: translateX(0);
                    }

                    100% {
                        transform: translateX(-50%);
                    }
                }

                .animate-marquee {
                    animation: marquee 40s linear infinite;
                    width: max-content;
                }

                .animate-marquee:hover {
                    animation-play-state: paused;
                }

                @keyframes fadeInScale {
                    0% {
                        opacity: 0;
                        transform: translateX(-50%) scale(0.8);
                    }

                    100% {
                        opacity: 1;
                        transform: translateX(-50%) scale(1);
                    }
                }
            </style>

            <div class="overflow-hidden">
                <div class="flex animate-marquee gap-8 pl-4">
                    <!-- Loop twice for infinite scroll effect (2 sets of 5 slides) -->
                    @for ($i = 0; $i < 2; $i++)
                        @foreach($slides as $slide)
                            <div class="min-w-[340px] md:min-w-[900px] flex justify-center">
                                <div class="w-full">
                                    <div class="border border-[#E5E7EB] bg-white rounded-xl w-full shadow-lg mx-auto">
                                        <div class="overflow-x-hidden overflow-y-visible rounded-t-xl">
                                            <div class="grid grid-cols-2 md:grid-cols-4 relative" style="height:auto; min-height:110px; width:100%">
                                                <div class="absolute z-10" style="right:16px;top:16px">
                                                    <button
                                                        class="border border-[#747775] rounded-lg px-3 py-1.5 flex items-center gap-2 bg-white hover:bg-gray-50 transition-colors"
                                                        style="height:32px">
                                                        <span class="text-[#444746] text-[14px] font-medium leading-none"
                                                            style="font-family:Roboto, sans-serif">Daily</span>
                                                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M7 9L11 13L15 9" stroke="#444746" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <!-- Brand Red Box -->
                                                <div class="bg-[#FF2D46] relative w-full h-[110px]">
                                                    <div class="absolute flex items-center gap-[6px]"
                                                        style="left:24px;top:24px">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <rect x="0.5" y="0.5" width="12.51" height="12.5" rx="1.5"
                                                                stroke="white"></rect>
                                                            <path
                                                                d="M5.37928 8.15898L10.2392 3.2991L10.8843 3.94422L5.37928 9.44922L2.82031 6.89025L3.46543 6.24514L5.37928 8.15898Z"
                                                                fill="white"></path>
                                                        </svg>
                                                        <span class="text-white text-[13px] leading-[16px] font-normal"
                                                            style="font-family:Roboto, sans-serif">Total clicks</span>
                                                    </div>
                                                    <div class="absolute text-white text-xl md:text-[32px] leading-none font-normal"
                                                        style="left:24px;top:48px;font-family:Roboto, sans-serif">
                                                        {{ $slide['clicks'] }}</div>
                                                </div>
                                                <!-- Brand Dark Box -->
                                                <div class="bg-[#111111] relative w-full h-[110px]">
                                                    <div class="absolute flex items-center gap-[6px]"
                                                        style="left:24px;top:24px">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <rect x="0.5" y="0.5" width="12.51" height="12.5" rx="1.5"
                                                                stroke="white"></rect>
                                                            <path
                                                                d="M5.37928 8.15898L10.2392 3.2991L10.8843 3.94422L5.37928 9.44922L2.82031 6.89025L3.46543 6.24514L5.37928 8.15898Z"
                                                                fill="white"></path>
                                                        </svg>
                                                        <span class="text-white text-[13px] leading-[16px] font-normal"
                                                            style="font-family:Roboto, sans-serif">Total impressions</span>
                                                    </div>
                                                    <div class="absolute text-white text-xl md:text-[32px] leading-none font-normal"
                                                        style="left:24px;top:48px;font-family:Roboto, sans-serif">
                                                        {{ $slide['imp'] }}</div>
                                                </div>
                                                <!-- White Box CTR -->
                                                <div class="bg-white border-r border-b border-[rgba(0,0,0,0.12)] relative w-full h-[110px]">
                                                    <div class="absolute flex items-center gap-[6px]"
                                                        style="left:24px;top:24px">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <rect x="0.5" y="0.5" width="12.51" height="12.5" rx="1.5"
                                                                stroke="black" stroke-opacity="0.54"></rect>
                                                        </svg>
                                                        <span
                                                            class="text-[rgba(0,0,0,0.54)] text-[13px] leading-[16px] font-normal"
                                                            style="font-family:Roboto, sans-serif">Average CTR</span>
                                                    </div>
                                                    <div class="absolute text-[rgba(0,0,0,0.54)] text-xl md:text-[32px] leading-none font-normal"
                                                        style="left:24px;top:48px;font-family:Roboto, sans-serif">
                                                        {{ $slide['ctr'] }}</div>
                                                </div>
                                                <!-- White Box Position -->
                                                <div class="bg-white border-r border-b border-[rgba(0,0,0,0.12)] relative w-full h-[110px]">
                                                    <div class="absolute flex items-center gap-[6px]"
                                                        style="left:24px;top:24px">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <rect x="0.5" y="0.5" width="12.51" height="12.5" rx="1.5"
                                                                stroke="black" stroke-opacity="0.54"></rect>
                                                        </svg>
                                                        <span
                                                            class="text-[rgba(0,0,0,0.54)] text-[13px] leading-[16px] font-normal"
                                                            style="font-family:Roboto, sans-serif">Average position</span>
                                                    </div>
                                                    <div class="absolute text-[rgba(0,0,0,0.54)] text-xl md:text-[32px] leading-none font-normal"
                                                        style="left:24px;top:48px;font-family:Roboto, sans-serif">
                                                        {{ $slide['pos'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="relative w-full" style="height:300px;padding:0 16px 20px 16px">
                                            <div class="absolute" style="left:16px;top:16px"><span
                                                    class="text-[#616161] text-[12px] font-normal"
                                                    style="font-family:Roboto, sans-serif">Clicks</span></div>
                                            <div class="absolute" style="right:16px;top:16px"><span
                                                    class="text-[#616161] text-[12px] font-normal"
                                                    style="font-family:Roboto, sans-serif">Impressions</span></div>
                                            <!-- Chart Area -->
                                            <div class="absolute" style="left:16px;right:16px;top:48px;height:230px">
                                                <!-- Y Axis Labels -->
                                                <div class="absolute"
                                                    style="left:0;top:0;width:100%;height:100%;pointer-events:none">
                                                    <div class="absolute text-[#757575] text-[12px] font-normal whitespace-nowrap"
                                                        style="left:0;top:4.3%;transform:translateY(-50%)">4.4k</div>
                                                    <div class="absolute text-[#757575] text-[12px] font-normal whitespace-nowrap"
                                                        style="left:0;top:31%;transform:translateY(-50%)">2.9k</div>
                                                    <div class="absolute text-[#757575] text-[12px] font-normal whitespace-nowrap"
                                                        style="left:0;top:57.6%;transform:translateY(-50%)">1.5k</div>
                                                    <div class="absolute text-[#757575] text-[12px] font-normal whitespace-nowrap"
                                                        style="left:0;top:84.3%;transform:translateY(-50%)">0</div>
                                                </div>
                                                <!-- Tooltip / Badge -->
                                                <div class="absolute"
                                                    style="left:42.04%;top:73.16%;transform:translate(-50%, -50%);pointer-events:auto">
                                                    <div class="absolute mb-2 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-sm rounded-lg whitespace-nowrap pointer-events-none"
                                                        style="font-family:Inter, sans-serif;font-size:11px;box-shadow:0 2px 8px rgba(0, 0, 0, 0.15);bottom:100px;z-index:50">
                                                        Started using BrandThirty
                                                        <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-px"
                                                            style="width:0;height:0;border-left:6px solid transparent;border-right:6px solid transparent;border-top:6px solid #111827">
                                                        </div>
                                                    </div>
                                                    <!-- Icon -->
                                                    <div class="absolute"
                                                        style="left:50%;bottom:48px;transform:translateX(-50%);animation:fadeInScale 0.6s ease-out forwards;z-index:10">
                                                        <div class="relative flex items-center justify-center bg-white rounded-full"
                                                            style="width:48px;height:48px;box-shadow:0 4px 20px rgba(255, 45, 70, 0.25), 0 2px 8px rgba(0, 0, 0, 0.1);border:2px solid #FF2D46">
                                                            <img src="{{ asset('Images/Logo.png') }}" alt="B30 Logo"
                                                                class="w-8 h-8 rounded-full shadow-[0_0_15px_rgba(255,45,70,0.6)] object-contain">
                                                        </div>
                                                        <div class="absolute"
                                                            style="width:2px;height:36px;background-color:#FF2D46;opacity:0.3;left:50%;top:100%;transform:translateX(-50%)">
                                                        </div>
                                                    </div>
                                                    <div class="absolute rounded-full"
                                                        style="width:24px;height:24px;background-color:#FF2D46;opacity:0.2;left:50%;top:50%;transform:translate(-50%, -50%)">
                                                    </div>
                                                    <div class="absolute rounded-full"
                                                        style="width:12px;height:12px;background-color:#FF2D46;left:50%;top:50%;transform:translate(-50%, -50%)">
                                                    </div>
                                                </div>
                                                <!-- Graph Lines -->
                                                <svg width="100%" height="100%" viewBox="0 0 1006 230"
                                                    preserveAspectRatio="none" class="w-full h-full"
                                                    style="padding-left:40px;padding-right:40px">
                                                    <line x1="33" y1="10" x2="971" y2="10" stroke="#E0E0E0" stroke-width="1">
                                                    </line>
                                                    <line x1="33" y1="71.33" x2="971" y2="71.33" stroke="#E0E0E0"
                                                        stroke-width="1"></line>
                                                    <line x1="33" y1="132.67" x2="971" y2="132.67" stroke="#E0E0E0"
                                                        stroke-width="1"></line>
                                                    <line x1="33" y1="194" x2="971" y2="194" stroke="#E0E0E0" stroke-width="1">
                                                    </line>
                                                    <path d="M33,180L100,183L200,181L400,175L600,157L800,88L971,49" fill="none"
                                                        stroke="#FF2D46" stroke-width="2"></path>
                                                    <path d="M33,181L100,182L300,177L500,165L700,131L900,76L971,10" fill="none"
                                                        stroke="#111111" stroke-width="2"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endfor
                </div>
            </div>

            <div class="flex items-center justify-between max-w-[1080px] mx-auto px-3 mt-4">
                <p class="comment text-[#777] !font-bold bg-[#F5F5F5] px-3 py-0.5 rounded-full inline-block">
                    ScammerBlock</p>
                <div class="flex items-center gap-2">
                    <button class="bg-neutral-100 rounded-full p-2.5 transition-all duration-200"
                        aria-label="Previous example">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </button>
                    <button class="bg-neutral-100 rounded-full p-2.5 transition-all duration-200"
                        aria-label="Next example">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>