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
        <a href="/">
            Quasi
        </a>
    </h1>
    <article class="my-6 grid grid-cols-1 place-items-baseline">
        <section class="flex flex-col">
            <h2 class="text-2xl font-bold">{{ $word->name }}</h2>
            <p class="text-gray-500">Frequency: {{ $word->frequency }}</p>
        </section>
        <section class="grid">
                <div class="my-6">
                    <ul class="list-none pl-4 text-gray-700">
                        @foreach ($parsedDefinitions as $parsed)
                            <li class="my-2">
                                @if ($parsed['small'] && $parsed['pos_string'])
                                    <small class="text-gray-600 italic block">
                                        {{ $parsed['pos_string'] }} {{ $parsed['small'] }}
                                    </small>
                                @elseif ($parsed['small'])
                                    <small class="text-gray-600 italic block">
                                        {{ $parsed['small'] }}
                                    </small>
                                @elseif ($parsed['pos_string'])
                                    <small class="text-gray-600 italic block">
                                        {{ $parsed['pos_string'] }}
                                    </small>
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
