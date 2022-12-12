<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>پرداخت</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body dir="rtl">
<div class="bg-gray-50">
    @if($message = Session::get('message'))
        <div class="mx-auto max-w-7xl py-12 px-4 sm:px-6 lg:flex lg:items-center lg:justify-between lg:py-16 lg:px-8">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                <span class="block text-green-600">{{ $message }}</span>
            </h2>
        </div>
    @endif
    <div class="mx-auto max-w-7xl py-12 px-4 sm:px-6 lg:flex lg:items-center lg:justify-between lg:py-16 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
            <span class="block">محصول: {{ $product->name }}</span>
            <span class="block">مبلغ قابل پرداخت: {{ $product->price }} ریال</span>
            <span class="block text-indigo-600">شماره کارت: {{ $user->card_number }}</span>
        </h2>
        <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
            <div class="inline-flex rounded-md shadow">
                <a href="{{ route('orders.pay') }}"
                   class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-5 py-3 text-base font-medium text-white hover:bg-indigo-700">{{ isset($message) ? 'تست پرداخت مجدد' : 'تست پرداخت' }}</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
