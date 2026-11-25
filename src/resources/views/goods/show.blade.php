<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ $goods->name }}</h2>
    </x-slot>

    <div class="p-4">
        @if ($goods->image_path)
            <img src="{{ asset('storage/' . $goods->image_path) }}" alt="{{ $goods->name }}" class="w-48">
        @endif

        <p>メンバー：{{ $goods->member->name ?? '集合' }}</p>
        <p>種類：{{ $goods->category->name }}</p>
        <p>シリーズ：{{ $goods->series->name ?? 'なし' }}</p>
        <p>発売日：{{ $goods->release_date ?? '不明' }}</p>
        <p>所持数：{{ $userGoods->quantity ?? 0 }}</p>
        <p>管理方法：{{ ($userGoods->collect_type ?? 'one') === 'one' ? '1つでいい' : '集める' }}</p>

        <a href="{{ route('goods.index') }}">一覧に戻る</a>
    </div>

    <div class="my-4">
        <p>所持数</p>
        <div id="quantity-box" data-url="{{ route('user-goods.quantity', $goods) }}" class="flex items-center gap-4">
            <button type="button" data-delta="-1" class="border px-4 py-2 text-xl">−</button>
            <span id="quantity" class="text-2xl">{{ $userGoods->quantity ?? 0 }}</span>
            <button type="button" data-delta="1" class="border px-4 py-2 text-xl">＋</button>
        </div>
    </div>

    <div class="my-4">
        <label for="collect-type">管理方法</label>
        <select id="collect-type" data-url="{{ route('user-goods.collect-type', $goods) }}">
            <option value="one" @selected(($userGoods->collect_type ?? 'one') === 'one')>1つでいい</option>
            <option value="collect" @selected(($userGoods->collect_type ?? 'one') === 'collect')>集める</option>
        </select>
    </div>

<p id="error" class="text-red-600"></p>
<script>
    // Breeze のレイアウトに入っている CSRF トークンを読む
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const errorText = document.getElementById('error');

    // サーバーに PATCH で送って、JSON を受け取る
    async function send(url, body) {
        const res = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',   // ← エラーも JSON で返してもらうため
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify(body),
        });

        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message ?? '保存できませんでした');
        }
        return data;
    }

    // ＋／− ボタン
    const box = document.getElementById('quantity-box');
    const quantityText = document.getElementById('quantity');

    box.querySelectorAll('button').forEach((button) => {
        button.addEventListener('click', async () => {
            const next = Number(quantityText.textContent) + Number(button.dataset.delta);
            if (next < 0) return;   // 0 未満にはしない

            try {
                const data = await send(box.dataset.url, { quantity: next });
                quantityText.textContent = data.quantity;   // 返ってきた数字で書き換える
                errorText.textContent = '';
            } catch (e) {
                errorText.textContent = e.message;
            }
        });
    });

    // 「1つでいい／集める」の切り替え
    const select = document.getElementById('collect-type');

    select.addEventListener('change', async () => {
        try {
            await send(select.dataset.url, { collect_type: select.value });
            errorText.textContent = '';
        } catch (e) {
            errorText.textContent = e.message;
        }
    });
</script>
</x-app-layout>