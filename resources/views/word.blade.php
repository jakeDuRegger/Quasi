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
    <h1 class="text-4xl font-bold italic my-16 border-b-2 border-b-amber-400">
        <a href="/">
            quasi
        </a>
    </h1>
    <article class="my-6 grid grid-cols-1 md:grid-cols-2 place-items-baseline">
        <section class="flex flex-col">
            <h2 class="text-2xl font-bold">{{ $word->name }}</h2>
            <p class="text-gray-500">Frequency: {{ $word->frequency }}</p>
        </section>
        <ul class="grid gap-y-4 my-6 justify-self-end list-decimal list-inside">
            @foreach ($parsedDefinitions as $parsed)
                <li class="shadow-sm shadow-gray-500 max-w-prose font-semibold p-6 rounded grid">
                    <span class="text-gray-700">{{ $parsed['definition'] }}</span>
                    @if ($parsed['small'] && $parsed['pos_string'])
                        <small class="text-gray-600 italic block justify-self-end">
                            {{ $parsed['pos_string'] }} {{ $parsed['small'] }}
                        </small>
                    @elseif ($parsed['small'])
                        <small class="text-gray-600 italic block justify-self-end">
                            {{ $parsed['small'] }}
                        </small>
                    @elseif ($parsed['pos_string'])
                        <small class="text-gray-600 italic block justify-self-end">
                            {{ $parsed['pos_string'] }}
                        </small>
                    @endif
                </li>
            @endforeach
        </ul>
    </article>
</main>
</body>
</html>
