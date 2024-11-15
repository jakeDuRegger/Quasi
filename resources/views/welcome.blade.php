<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $word->name }}</title>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<main class="container mx-auto max-w-screen-lg px-16 lg:px-0">
    <h1 class="text-4xl font-bold my-16">
        Quasi
    </h1>
    <article class="my-6 grid grid-cols-2 place-items-baseline">
        <section class="flex flex-col">
            <h2 class="text-2xl font-bold">{{ $word->name }}</h2>
            <p class="text-gray-500">Frequency: {{ $word->frequency }}</p>
        </section>
        <section class="grid">
                <div class="my-6">
                    <ul class="list-disc pl-6 text-gray-700">
                        @foreach ($parsedDefinitions as $parsed)
                            <li class="mb-4">
                                @if ($parsed['small'])
                                    <small class="text-gray-600 italic block">{{ $parsed['small'] }}</small>
                                @endif
                                <span>{{ $parsed['definition'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
        </section>
    </article>
</main>
</body>
</html>
