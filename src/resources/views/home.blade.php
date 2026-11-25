<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>トップページ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-800">
<div class="max-w-md mx-auto min-h-screen pb-20 px-4">

    {{-- ヘッダー：ロゴとログアウト --}}
    <header class="flex justify-between items-center py-4">
        <a href="{{ route('home') }}" class="font-bold text-lg">ロゴ</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="border px-3 py-1 rounded">ログアウト</button>
        </form>
    </header>

    {{-- 検索：グッズ一覧の画面に keyword を送る --}}
    <form method="GET" action="{{ route('goods.index') }}" class="flex mb-6">
        <input type="text" name="keyword" placeholder="グッズ名で検索"
               class="flex-1 border rounded-l px-3 py-2">
        <button type="submit" class="bg-indigo-500 text-white px-4 rounded-r">検索</button>
    </form>

    {{-- グッズ一覧 --}}
    <section class="mb-8">
        <div class="flex justify-between items-center mb-2">
            <h2 class="font-bold">グッズ一覧</h2>
            <a href="{{ route('goods.index') }}" class="text-sm text-indigo-600">もっと見る</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
            @foreach ($goods as $item)
                <a href="{{ route('goods.show', $item) }}" class="text-center">
                    @if ($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}"
                             class="w-full aspect-square object-cover rounded border">
                    @else
                        <div class="w-full aspect-square bg-gray-200 rounded border"></div>
                    @endif
                    <p class="text-xs mt-1 line-clamp-2">{{ $item->name }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- 回収状況 --}}
    <section class="mb-8">
        <h2 class="font-bold mb-2">回収状況</h2>
        @if ($collecting->isEmpty())
            <p class="text-sm text-gray-500">「集める」に設定したグッズはまだありません</p>
        @else
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                @foreach ($collecting as $mine)
                    <a href="{{ route('goods.show', $mine->goods) }}" class="text-center">
                        @if ($mine->goods->image_path)
                            <img src="{{ asset('storage/' . $mine->goods->image_path) }}" alt="{{ $mine->goods->name }}"
                                 class="w-full aspect-square object-cover rounded border">
                        @else
                            <div class="w-full aspect-square bg-gray-200 rounded border"></div>
                        @endif
                        <p class="text-xs mt-1 line-clamp-2">{{ $mine->goods->name }}</p>
                        <p class="text-xs font-bold">{{ $mine->quantity }}個</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>

{{-- 下のメニュー --}}
<nav class="fixed bottom-0 inset-x-0 bg-white border-t">
    <div class="max-w-md mx-auto grid grid-cols-4 text-center text-sm">
        <a href="{{ route('home') }}" class="py-3 font-bold">トップ</a>
        <a href="{{ route('goods.index') }}" class="py-3">一覧</a>
        <a href="{{ Route::has('collections.index') ? route('collections.index') : '#' }}" class="py-3">回収</a>
        <a href="{{ route('profile.edit') }}" class="py-3">アカウント</a>
    </div>
</nav>
</body>
</html>