<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">グッズ一覧</h2>
    </x-slot>

    <div class="p-4">
        <form method="GET" action="{{ route('goods.index') }}" class="mb-4">
            <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="グッズ名で検索">
            <button type="submit">検索</button>
        </form>

        @forelse ($goods as $item)
            @php $mine = $item->userGoods->first(); @endphp
            <a href="{{ route('goods.show', $item) }}" class="block border p-2 mb-2">
                <p>{{ $item->name }}</p>
                <p>{{ $item->member->name ?? '集合' }} / {{ $item->category->name }}</p>
                <p>所持数：{{ $mine->quantity ?? 0 }}</p>
                @if ($mine && $mine->collect_type === 'one' && $mine->quantity > 0)
                    <span>持っています</span>
                @endif
            </a>
        @empty
            <p>持っていません</p>
        @endforelse
    </div>
</x-app-layout>
