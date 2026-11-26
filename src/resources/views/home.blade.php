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
<div style="max-width: 28rem; margin: 0 auto; min-height: 100vh; padding: 0 16px 80px;">

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

    {{-- メンバーから選ぶ --}}
    <section style="margin-bottom: 32px;">
        <h2 style="font-weight: bold; margin-bottom: 8px;">メンバーから選ぶ</h2>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
            @foreach ($members as $member)
                <a href="{{ route('goods.index', ['member' => $member->id]) }}" style="text-align: center;">
                    @if ($member->image_path)
                        <img src="{{ asset('storage/' . $member->image_path) }}" alt="{{ $member->name }}"
                            style="width: 100%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                    @else
                        <div style="width: 100%; aspect-ratio: 1 / 1; background: #eee; border-radius: 8px;"></div>
                    @endif
                    <p style="font-size: 12px; margin-top: 4px;">{{ $member->name }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- グッズの種類から選ぶ --}}
    <section style="margin-bottom: 32px;">
        <h2 style="font-weight: bold; margin-bottom: 8px;">グッズの種類から選ぶ</h2>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
            @foreach ($categories as $category)
                <a href="{{ route('goods.index', ['category' => $category->id]) }}" style="text-align: center;">
                    @if ($category->image_path)
                        <img src="{{ asset('storage/' . $category->image_path) }}" alt=""
                             style="width: 100%; aspect-ratio: 1 / 1; object-fit: contain; padding: 8px; border-radius: 8px; border: 1px solid #ddd;">
                    @else
                        <div style="width: 100%; aspect-ratio: 1 / 1; background: #eee; border-radius: 8px;"></div>
                    @endif
                    <p style="font-size: 12px; margin-top: 4px;">{{ $category->name }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- 欲しい！に追加したもの --}}
    <section style="margin-bottom: 32px;">
        <h2 style="font-weight: bold; margin-bottom: 8px;">欲しい！に追加したもの</h2>
        @if ($wishes->isEmpty())
            <p style="font-size: 14px; color: #888;">グッズ詳細のハートを押すと、ここに表示されます</p>
        @else
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                @foreach ($wishes as $wish)
                    <a href="{{ route('goods.show', $wish->goods) }}" style="text-align: center;">
                        @if ($wish->goods->image_path)
                            <img src="{{ asset('storage/' . $wish->goods->image_path) }}" alt="{{ $wish->goods->name }}"
                                 style="width: 100%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                        @else
                            <div style="width: 100%; aspect-ratio: 1 / 1; background: #eee; border-radius: 8px;"></div>
                        @endif
                        <p style="font-size: 12px; margin-top: 4px;">{{ $wish->goods->name }}</p>
                    </a>
                @endforeach
            </div>
        @endif
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
    <nav style="position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #ddd;">
        <div style="max-width: 28rem; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); text-align: center; font-size: 14px;">
            <a href="{{ route('home') }}" style="padding: 12px 0; font-weight: bold;">トップ</a>
            <a href="{{ route('goods.index', ['collect_type' => 'one']) }}" style="padding: 12px 0;">各１</a>
            <a href="{{ route('collections.index') }}" style="padding: 12px 0;">回収</a>
            <a href="{{ route('profile.edit') }}" style="padding: 12px 0;">アカウント</a>
        </div>
    </nav>
</body>
</html>