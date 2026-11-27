<x-main-layout title="トップページ" active="home">

    {{-- 検索：グッズ一覧の画面に keyword を送る --}}
    <form method="GET" action="{{ route('goods.index') }}" class="flex mb-6">
        <input type="text" name="keyword" placeholder="グッズ名で検索"
               class="flex-1 border rounded-l px-3 py-2">
        <button type="submit" class="bg-indigo-500 text-white px-4 rounded-r">検索</button>
    </form>

    {{-- メンバーから選ぶ --}}
    <section style="margin-bottom: 32px;">
        <h2 style="font-weight: bold; margin-bottom: 8px;">メンバーから選ぶ</h2>
        <div class="app-grid">
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

            {{-- 全員集合（集合柄のグッズ） --}}
            <a href="{{ route('goods.index', ['member' => \App\Models\Goods::MEMBER_GROUP]) }}" style="text-align: center;">
                <img src="{{ asset('storage/' . \App\Models\Goods::MEMBER_GROUP_IMAGE) }}" alt="全員集合"
                     style="width: 100%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                <p style="font-size: 12px; margin-top: 4px;">全員集合</p>
            </a>
        </div>
    </section>

    {{-- グッズの種類から選ぶ --}}
    <section style="margin-bottom: 32px;">
        <h2 style="font-weight: bold; margin-bottom: 8px;">グッズの種類から選ぶ</h2>
        <div class="app-grid">
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

    {{-- シリーズから選ぶ（文字だけ。1行に1つずつ。押すとそのシリーズのグッズ一覧へ） --}}
    <section style="margin-bottom: 32px;">
        <h2 style="font-weight: bold; margin-bottom: 8px;">シリーズから選ぶ</h2>
        @if ($series->isEmpty())
            <p style="font-size: 14px; color: #888;">シリーズはまだありません</p>
        @else
            <ul style="border-top: 1px solid #e5e7eb;">
                @foreach ($series as $item)
                    <li style="border-bottom: 1px solid #e5e7eb;">
                        <a href="{{ route('goods.index', ['series' => $item->id]) }}"
                           style="display: flex; align-items: center; justify-content: space-between; min-height: 48px; padding: 12px 4px; font-size: 15px;">
                            <span>{{ $item->name }}</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" style="flex-shrink: 0;">
                                <path d="M9 6l6 6-6 6" stroke="#9ca3af" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- 回収状況 --}}
    <section class="mb-8">
        <h2 class="font-bold mb-2">回収状況</h2>
        @if ($collecting->isEmpty())
            <p class="text-sm text-gray-500">「回収」に設定したグッズはまだありません</p>
        @else
            <div class="app-grid">
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
</x-main-layout>
