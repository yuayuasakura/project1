<x-main-layout :title="$title" :active="$active">

    {{-- 検索：いま見ている一覧（グッズ一覧／各１／回収）の中で探す。ほかの絞り込み条件も引き継ぐ --}}
    <form method="GET" action="{{ $action }}" class="flex mb-6">
        @foreach (request()->only(['collect_type', 'member', 'category', 'series', 'owned', 'filter']) as $name => $value)
            @if ($value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach
        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="グッズ名で検索"
               class="flex-1 border rounded-l px-3 py-2">
        <button type="submit" class="bg-indigo-500 text-white px-4 rounded-r">検索</button>
    </form>

    {{-- 検索・絞り込みの条件が正しくなかったとき（その条件は外して表示している） --}}
    @if (! empty($filterErrors))
        <div role="alert" style="margin-bottom: 16px; padding: 12px; border-radius: 8px; background: #fef2f2; color: #b91c1c; font-size: 14px;">
            <p style="font-weight: bold; margin-bottom: 4px;">正しくない条件があったため、その条件を外して表示しています</p>
            <ul style="list-style: disc; padding-left: 20px;">
                @foreach ($filterErrors as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- グッズ一覧：画像とグッズ名を横に4つずつ --}}
    <section style="margin-bottom: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div style="display: flex; align-items: center; gap: 4px;">
                <h2 style="font-weight: bold;">{{ $title }}（{{ $goods->count() }}件）</h2>

                {{-- 各1のときだけ：二本線のボタンで「まとめて各1に登録」を開く --}}
                @if (! empty($bulkOne))
                    <button type="button" id="bulk-one-open" aria-label="まとめて各1に登録" aria-haspopup="dialog"
                            aria-controls="bulk-one-sheet" aria-expanded="false"
                            style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;
                                   border: none; background: none; border-radius: 8px; cursor: pointer;">
                        <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 9h16M4 15h16" stroke="#374151" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                @endif
            </div>

            {{-- 絞り込みのプルダウン（各１：一覧・所持済・未所持／グッズ一覧：一覧・回収・所持済・未所持）。選んだらすぐ切り替える --}}
            @if ($select)
                <form method="GET" action="{{ $action }}">
                    {{-- ほかの絞り込み条件は引き継ぐ（プルダウン自身の値は除く） --}}
                    @foreach (request()->only(array_diff(['collect_type', 'member', 'category', 'series', 'keyword', 'owned', 'filter'], [$select['name']])) as $name => $value)
                        @if ($value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label for="list-filter" class="sr-only">表示するグッズを絞り込む</label>
                    <select id="list-filter" name="{{ $select['name'] }}" onchange="this.form.submit()"
                            style="border: 1px solid #ccc; border-radius: 8px; padding: 4px 28px 4px 8px; font-size: 14px;">
                        @foreach ($select['options'] as $value => $label)
                            <option value="{{ $value }}" @selected((string) request($select['name']) === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit">表示</button></noscript>
                </form>
            @endif
        </div>

        @if ($goods->isEmpty())
            <p style="font-size: 14px; color: #888;">該当するグッズがありません</p>
        @else
            <div class="app-grid">
                @foreach ($goods as $item)
                    @php $mine = $item->userGoods->first(); @endphp
                    <a href="{{ route('goods.show', $item) }}" style="text-align: center;">
                        <div style="position: relative;">
                            @if ($item->image_path)
                                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}"
                                     style="width: 100%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                            @else
                                <div style="width: 100%; aspect-ratio: 1 / 1; background: #eee; border-radius: 8px;"></div>
                            @endif

                            {{-- 持っているグッズには印を付ける --}}
                            @if ($mine && $mine->quantity > 0)
                                <span style="position: absolute; top: 4px; right: 4px; background: #6366f1; color: #fff; font-size: 10px; padding: 1px 4px; border-radius: 4px;">
                                    {{ $mine->collect_type === 'collect' ? $mine->quantity . '個' : '所持' }}
                                </span>
                            @endif
                        </div>
                        <p style="font-size: 12px; margin-top: 4px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $item->name }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- 各1のときだけ「まとめて各1に登録」を用意する。見出し横のボタンより後ろに置く（ふだんは隠しておき、見出し横の二本線ボタンで下から出す） --}}
    @if (! empty($bulkOne))
        @include('goods.partials.bulk-one', ['bulkOne' => $bulkOne])
    @endif
</x-main-layout>
