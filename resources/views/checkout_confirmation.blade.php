<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review & Payment - BrandThirty</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-black': '#050505',
                        'brand-dark': '#111111',
                        'brand-red': '#FF2D46',
                        'brand-red-hover': '#d91b32',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .accordion-content {
            transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }

        .accordion-item.active .accordion-content {
            max-height: 500px;
            opacity: 1;
        }

        .accordion-item.active .chevron {
            transform: rotate(180deg);
        }
    </style>
</head>

<body class="bg-brand-black text-white antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="w-full border-b border-white/5 bg-brand-black/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-24">
                <div class="flex-shrink-0 flex items-center">
                    <img class="h-10 w-auto object-contain brightness-100 md:h-12"
                        src="{{ asset('Images/B30_logo-04.png') }}" alt="BrandThirty">
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto">

            <div class="text-center mb-16">
                <!-- Changed Icon to Info/Check -->
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-500/10 mb-6">
                    <i class="fas fa-clipboard-check text-blue-500 text-3xl"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold mb-4">Review & Complete Order</h1>
                <p class="text-gray-400 text-lg">Hello {{ $orderData['name'] }}, please review your order details and
                    confirm payment.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

                <!-- Left Column: Receipt / Order Summary -->
                <div class="bg-brand-dark border border-white/10 rounded-2xl p-8 shadow-2xl h-fit">
                    <h2 class="text-xl font-bold border-b border-white/10 pb-4 mb-6">Order Receipt</h2>

                    <div class="space-y-4 text-sm mb-8">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Ref ID</span>
                            <span class="font-mono text-white">{{ $orderId }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Date</span>
                            <span class="text-white">{{ date('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Client Info</span>
                            <span class="text-white text-right">{{ $orderData['company'] ?: $orderData['name'] }}</span>
                        </div>
                    </div>

                    <div class="border-t border-b border-white/10 py-6 space-y-3 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Selected Plan</span>
                            <span class="text-white font-bold">{{ ucfirst($orderData['plan']) }} Package</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Content Strategy</span>
                            <span class="text-white">{{ strip_tags($addonText) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Distribution Reach</span>
                            <span class="text-white">{{ $orderData['distribution'] }} Articles</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-lg font-bold">
                        <span>Total Amount</span>
                        <span class="text-3xl font-extrabold text-brand-red">RM
                            {{ number_format($grandTotal, 2) }}</span>
                    </div>
                </div>

                <!-- Right Column: Payment Options (Accordion) -->
                <div class="space-y-4">
                    <h3 class="text-xl font-bold mb-4">Select Payment Method</h3>

                    <!-- Accordion Item 1: DuitNow -->
                    <div
                        class="accordion-item bg-white/5 border border-white/10 rounded-xl overflow-hidden transition-all hover:bg-white/10">
                        <div class="p-4 flex items-center justify-between cursor-pointer"
                            onclick="toggleAccordion(this)">
                            <div class="flex items-center gap-4">
                                <div class="text-2xl">📱</div>
                                <div>
                                    <h4 class="font-bold text-sm">DuitNow / TnG</h4>
                                    <p class="text-xs text-gray-500">Scan QR to Pay</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300 chevron"></i>
                        </div>
                        <div class="accordion-content bg-brand-black/20">
                            <div class="p-6 text-center">
                                <div class="bg-white p-3 rounded-xl inline-block mb-4 shadow-lg">
                                    {{-- Real QR image if it exists, otherwise inline SVG placeholder --}}
                                    @if(file_exists(public_path('images/duitnow_qr.jpg')))
                                        <img src="{{ asset('images/duitnow_qr.jpg') }}" alt="DuitNow QR Code" class="w-40 h-40 object-contain">
                                    @else
                                        <svg width="160" height="160" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="160" height="160" fill="#fff"/>
                                            {{-- Simulated QR pattern --}}
                                            <rect x="10" y="10" width="40" height="40" rx="2" fill="#111"/>
                                            <rect x="16" y="16" width="28" height="28" rx="1" fill="#fff"/>
                                            <rect x="22" y="22" width="16" height="16" fill="#111"/>
                                            <rect x="110" y="10" width="40" height="40" rx="2" fill="#111"/>
                                            <rect x="116" y="16" width="28" height="28" rx="1" fill="#fff"/>
                                            <rect x="122" y="22" width="16" height="16" fill="#111"/>
                                            <rect x="10" y="110" width="40" height="40" rx="2" fill="#111"/>
                                            <rect x="16" y="116" width="28" height="28" rx="1" fill="#fff"/>
                                            <rect x="22" y="122" width="16" height="16" fill="#111"/>
                                            {{-- Center data dots --}}
                                            <g fill="#111">
                                                <rect x="60" y="10" width="8" height="8"/><rect x="76" y="10" width="8" height="8"/><rect x="92" y="10" width="8" height="8"/>
                                                <rect x="60" y="26" width="8" height="8"/><rect x="76" y="26" width="8" height="8"/>
                                                <rect x="60" y="42" width="8" height="8"/><rect x="92" y="42" width="8" height="8"/>
                                                <rect x="10" y="60" width="8" height="8"/><rect x="26" y="60" width="8" height="8"/><rect x="42" y="60" width="8" height="8"/><rect x="60" y="60" width="8" height="8"/><rect x="76" y="60" width="8" height="8"/><rect x="92" y="60" width="8" height="8"/><rect x="110" y="60" width="8" height="8"/><rect x="126" y="60" width="8" height="8"/><rect x="142" y="60" width="8" height="8"/>
                                                <rect x="10" y="76" width="8" height="8"/><rect x="42" y="76" width="8" height="8"/><rect x="76" y="76" width="8" height="8"/><rect x="110" y="76" width="8" height="8"/><rect x="142" y="76" width="8" height="8"/>
                                                <rect x="10" y="92" width="8" height="8"/><rect x="26" y="92" width="8" height="8"/><rect x="42" y="92" width="8" height="8"/><rect x="60" y="92" width="8" height="8"/><rect x="76" y="92" width="8" height="8"/><rect x="92" y="92" width="8" height="8"/><rect x="110" y="92" width="8" height="8"/><rect x="126" y="92" width="8" height="8"/><rect x="142" y="92" width="8" height="8"/>
                                                <rect x="60" y="110" width="8" height="8"/><rect x="92" y="110" width="8" height="8"/><rect x="110" y="110" width="8" height="8"/><rect x="142" y="110" width="8" height="8"/>
                                                <rect x="60" y="126" width="8" height="8"/><rect x="76" y="126" width="8" height="8"/><rect x="126" y="126" width="8" height="8"/>
                                                <rect x="60" y="142" width="8" height="8"/><rect x="92" y="142" width="8" height="8"/><rect x="110" y="142" width="8" height="8"/><rect x="142" y="142" width="8" height="8"/>
                                            </g>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex items-center justify-center gap-2 mb-3">
                                    <span class="text-xs font-bold text-purple-400 uppercase tracking-wide">DuitNow QR</span>
                                    <span class="text-gray-600">|</span>
                                    <span class="text-xs text-gray-500">Touch 'n Go / Banking Apps</span>
                                </div>
                                <div class="bg-gray-800/80 rounded-lg px-4 py-2 text-xs font-mono inline-block text-gray-300 border border-white/5">
                                    REF: {{ $orderId }}
                                </div>
                                <p class="text-[10px] text-gray-500 mt-3">Scan with any Banking App or E-Wallet. Include Ref ID as payment reference.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 2: Manual Transfer -->
                    <div
                        class="accordion-item bg-white/5 border border-white/10 rounded-xl overflow-hidden transition-all hover:bg-white/10">
                        <div class="p-4 flex items-center justify-between cursor-pointer"
                            onclick="toggleAccordion(this)">
                            <div class="flex items-center gap-4">
                                <div class="text-2xl">🏦</div>
                                <div>
                                    <h4 class="font-bold text-sm">Manual Transfer</h4>
                                    <p class="text-xs text-gray-500">Bank Transfer / FPX</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300 chevron"></i>
                        </div>
                        <div class="accordion-content bg-brand-black/20">
                            <div class="p-6 space-y-4">
                                <div
                                    class="bg-brand-black p-3 rounded-lg border border-white/5 flex justify-between items-center">
                                    <span class="text-xs text-gray-500 uppercase">Bank</span>
                                    <span class="text-sm font-bold">Maybank</span>
                                </div>
                                <div class="bg-brand-black p-3 rounded-lg border border-white/5">
                                    <span class="block text-[10px] text-gray-500 uppercase mb-1">Account Number</span>
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-mono text-brand-red font-bold tracking-wider">5123
                                            4567 8901</span>
                                        <button class="text-gray-400 hover:text-white transition"
                                            onclick="copyToClipboard('512345678901')"><i
                                                class="fas fa-copy"></i></button>
                                    </div>
                                </div>
                                <div
                                    class="bg-brand-black p-3 rounded-lg border border-white/5 flex justify-between items-center">
                                    <span class="text-xs text-gray-500 uppercase">Holder</span>
                                    <span class="text-sm font-bold">BrandThirty Sdn Bhd</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Item 3: Credit Card -->
                    <div
                        class="accordion-item bg-white/5 border border-white/10 rounded-xl overflow-hidden transition-all hover:bg-white/10">
                        <div class="p-4 flex items-center justify-between cursor-pointer"
                            onclick="toggleAccordion(this)">
                            <div class="flex items-center gap-4">
                                <div class="text-2xl">💳</div>
                                <div>
                                    <h4 class="font-bold text-sm">Credit Card</h4>
                                    <p class="text-xs text-gray-500">Visa / Mastercard</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300 chevron"></i>
                        </div>
                        <div class="accordion-content bg-brand-black/20">
                            <div class="p-6 text-center">
                                <p class="text-sm text-gray-400 mb-4">Secure payment via Stripe/Payment Gateway.</p>
                                <a href="#"
                                    class="block w-full py-3 bg-brand-red hover:bg-brand-red-hover text-white font-bold rounded-lg transition-all text-sm mb-3 shadow-lg shadow-red-900/40">
                                    Pay with Card
                                </a>
                                <a href="{{ $ccWaUrl }}" target="_blank"
                                    class="text-xs text-blue-400 hover:text-blue-300 flex items-center justify-center gap-1">
                                    <i class="fab fa-whatsapp"></i> Request Payment Link
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- CONFIRMATION FORM (Instead of Direct WA Link) -->
                    <div class="pt-6">
                        <form action="{{ url('checkout/confirm') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <!-- Hidden Inputs to Pass Data to Process State -->
                            <input type="hidden" name="name" value="{{ $orderData['name'] }}">
                            <input type="hidden" name="email" value="{{ $orderData['email'] }}">
                            <input type="hidden" name="phone" value="{{ $orderData['phone'] }}">
                            <input type="hidden" name="company" value="{{ $orderData['company'] }}">
                            <input type="hidden" name="website" value="{{ $orderData['website'] }}">
                            <input type="hidden" name="plan" value="{{ $orderData['plan'] }}">
                            <input type="hidden" name="strategy" value="{{ $orderData['strategy'] }}">
                            <input type="hidden" name="distribution" value="{{ $orderData['distribution'] }}">
                            <input type="hidden" name="total_amount" value="{{ $grandTotal }}">
                            <input type="hidden" name="order_id" value="{{ $orderId }}">

                            <!-- Payment proof upload -->
                            <div class="mb-6">
                                <label class="block text-sm font-bold text-gray-300 mb-2">
                                    <i class="fas fa-camera text-brand-red mr-1"></i> Upload payment proof (optional)
                                </label>
                                <p class="text-xs text-gray-500 mb-2">Screenshot or photo of your payment receipt (JPEG, PNG, max 5MB). Admin will verify before approving.</p>
                                <input type="file" name="receipt" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                    class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-white/10 file:text-white file:font-bold file:cursor-pointer hover:file:bg-white/20">
                                @error('receipt')
                                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" name="confirm_payment" value="1"
                                class="w-full py-4 bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold rounded-xl text-center transition-all shadow-lg hover:shadow-green-900/40 flex items-center justify-center gap-3">
                                <i class="fab fa-whatsapp text-2xl"></i>
                                <span>I Have Made Payment (Confirm Order)</span>
                            </button>
                            <p class="text-center text-xs text-gray-500 mt-2">Clicking this will generate your invoice
                                and notify our team.</p>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <footer class="text-center py-8 text-gray-600 text-sm border-t border-gray-900 mt-12">
        &copy; 2026 BrandThirty. All rights reserved.
    </footer>

    <script>
        function toggleAccordion(header) {
            const item = header.parentElement;
            const isActive = item.classList.contains('active');

            // Close all items
            document.querySelectorAll('.accordion-item').forEach(el => {
                el.classList.remove('active');
            });

            // Toggle clicked item
            if (!isActive) {
                item.classList.add('active');
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied to clipboard!');
            });
        }
    </script>

</body>

</html>