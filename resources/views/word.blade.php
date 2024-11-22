<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $word->name }}</title>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="container mx-auto flex flex-col max-w-screen-lg px-16 lg:px-1 max-h-dvh">
<header class="flex flex-row items-baseline justify-between border-b-2 border-b-amber-400 mt-8">
    <h1 class="text-4xl font-bold italic">
        <a href="/" class="outline-amber-400">
            quasi
        </a>
    </h1>
    <a href="/" class="outline-amber-400">
        <svg xmlns="http://www.w3.org/2000/svg"
             width="24"
             height="24"
             fill="currentColor"
             viewBox="0 0 256 256"
             class="text-gray-500">
            <path
                d="M88,104H40a8,8,0,0,1-8-8V48a8,8,0,0,1,16,0V76.69L62.63,62.06A95.43,95.43,0,0,1,130,33.94h.53a95.36,95.36,0,0,1,67.07,27.33,8,8,0,0,1-11.18,11.44,79.52,79.52,0,0,0-55.89-22.77h-.45A79.56,79.56,0,0,0,73.94,73.37L59.31,88H88a8,8,0,0,1,0,16Zm128,48H168a8,8,0,0,0,0,16h28.69l-14.63,14.63a79.56,79.56,0,0,1-56.13,23.43h-.45a79.52,79.52,0,0,1-55.89-22.77,8,8,0,1,0-11.18,11.44,95.36,95.36,0,0,0,67.07,27.33H126a95.43,95.43,0,0,0,67.36-28.12L208,179.31V208a8,8,0,0,0,16,0V160A8,8,0,0,0,216,152Z"></path>
        </svg>
    </a>
</header>
<main class="mb-8">
    <article class="my-8 grid grid-cols-1 place-items-baseline justify-items-center">
        <!-- Word -->

        <section class="flex flex-col justify-center text-center sticky top-0 bg-white w-full z-10 p-4">
            <h2 class="text-3xl font-bold">{{ $word->name }}
            </h2>
            <small class="text-lg text-gray-500">{{$word->ipa_pronunciation}}</small>
            <p class="text-sm text-gray-500">Frequency: {{ $word->frequency }}</p>
        </section>
        <!-- Definitions -->
        <ul class="flex flex-col gap-y-4 mt-6 mb-8 list-decimal list-inside">
            @foreach ($parsedDefinitions as $parsed)
                <li class="shadow-sm shadow-gray-500 max-w-prose font-semibold p-6 rounded grid">
                    <p class="relative">
                        <span class="text-gray-700">{{ lcfirst($parsed['definition']) }}</span>
                        @isset($parsed['referencedWord'])
                            <span class="group">
                                <span class="text-amber-400 font-bold cursor-pointer">
                                    {{ $parsed['referencedWord'] }}
                                </span>
                                {{-- Tooltip Content --}}
                                <span
                                    class="absolute z-10 opacity-0 px-3 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg shadow-lg group-hover:block group-hover:opacity-100 top-full left-1/2 transform -translate-x-1/2 mt-1 transition-opacity">
                                    {{ $parsed['referencedDefinition'] ?? 'No additional info' }}
                                </span>
                            </span>
                        @endisset
                    </p>
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
        <!-- More Info -->
        <section class="flex flex-col justify-self-start my-8">
            <dl class="max-w-prose grid gap-6 text-wrap">
                <!-- Reusable logic for each section -->
                @php
                    $categories = [
                        'Sounds similar to' => [
                            'data' => json_decode($word->sounds_like, true),
                            'example' => '"bardolatry", "bartoletti"',
                        ],
                        'Synonyms' => [
                            'data' => json_decode($word->synonyms, true),
                            'example' => '"worship", "devotion"',
                        ],
                        'Antonyms' => [
                            'data' => json_decode($word->antonyms, true),
                            'example' => '"disregard", "criticism"',
                        ],
                        'Homophones' => [
                            'data' => json_decode($word->homophones, true),
                            'example' => '"bard", "barred"',
                        ],
                        'Kind of like' => [
                            'data' => json_decode($word->kind_of, true),
                            'example' => '"artistic devotion", "theater practices"',
                        ],
                        'Part of' => [
                            'data' => json_decode($word->part_of, true),
                            'example' => '"bardolatry as part of Shakespearean studies"',
                        ],
                        'Associated words' => [
                            'data' => json_decode($word->triggers, true),
                            'example' => '"Shakespeare", "drama"',
                        ],
                        'Spelled similar to' => [
                            'data' => json_decode($word->spelled_like, true),
                            'example' => '"bardolatry", "bardology"',
                        ],
                        'More general' => [
                            'data' => json_decode($word->more_general, true),
                            'example' => '"literature", "performance arts"',
                        ],
                    ];
                @endphp

                @foreach ($categories as $title => $info)
                    @if (!empty($info['data'])) <!-- Show section only if data is present -->
                    <dt class="pb-1 border-b border-b-amber-400">
                        {{ $title }} (e.g., {!! $info['example'] !!})
                    </dt>
                    <dd class="p-6 text-gray-600 shadow-sm shadow-gray-500 rounded">
                        {{ implode(', ', $info['data']) }}
                    </dd>
                    @endif
                @endforeach
            </dl>
        </section>
    </article>
</main>
</body>
</html>
