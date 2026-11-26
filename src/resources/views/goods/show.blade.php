<x-mobile-layout :title="$goods->name" :active="($userGoods->collect_type ?? 'one') === 'collect' ? 'collections' : 'goods'">

    {{-- 一覧に戻る --}}
    <a href="{{ url()->previous() === url()->current() ? route('goods.index') : url()->previous() }}"
       style="display: inline-block; font-size: 14px; color: #6366f1; margin-bottom: 12px;">← 戻る</a>

    {{-- グッズの画像（横幅いっぱいの正方形） --}}
    @if ($goods->image_path)
        <img src="{{ asset('storage/' . $goods->image_path) }}" alt="{{ $goods->name }}"
             style="width: 100%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 12px; border: 1px solid #ddd;">
    @else
        <div style="width: 100%; aspect-ratio: 1 / 1; background: #eee; border-radius: 12px;"></div>
    @endif

    {{-- グッズ名 --}}
    <h1 style="font-size: 20px; font-weight: bold; margin: 16px 0 12px;">{{ $goods->name }}</h1>

    {{-- グッズの情報 --}}
    <dl style="display: grid; grid-template-columns: 6em 1fr; row-gap: 8px; font-size: 14px; padding: 12px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
        <dt style="color: #888;">メンバー</dt><dd>{{ $goods->member->name ?? '集合' }}</dd>
        <dt style="color: #888;">種類</dt><dd>{{ $goods->category->name }}</dd>
        <dt style="color: #888;">シリーズ</dt><dd>{{ $goods->series->name ?? 'なし' }}</dd>
        <dt style="color: #888;">発売日</dt><dd>{{ $goods->release_date ?? '不明' }}</dd>
    </dl>

    {{-- 所持数（＋／−で更新） --}}
    <section style="margin-top: 20px;">
        <h2 style="font-weight: bold; margin-bottom: 8px;">所持数</h2>
        <div id="quantity-box" data-url="{{ route('user-goods.quantity', $goods) }}"
             style="display: flex; align-items: center; justify-content: center; gap: 24px;">
            <button type="button" data-delta="-1" aria-label="1つ減らす"
                    style="width: 48px; height: 48px; border: 1px solid #ccc; border-radius: 50%; font-size: 22px;">−</button>
            <span id="quantity" style="font-size: 28px; font-weight: bold; min-width: 2em; text-align: center;">{{ $userGoods->quantity ?? 0 }}</span>
            <button type="button" data-delta="1" aria-label="1つ増やす"
                    style="width: 48px; height: 48px; border: 1px solid #ccc; border-radius: 50%; font-size: 22px;">＋</button>
        </div>
    </section>

    {{-- 管理方法 --}}
    <section style="margin-top: 20px;">
        <label for="collect-type" style="display: block; font-weight: bold; margin-bottom: 8px;">管理方法</label>
        <select id="collect-type" data-url="{{ route('user-goods.collect-type', $goods) }}"
                style="width: 100%; border: 1px solid #ccc; border-radius: 8px; padding: 8px 12px;">
            <option value="one" @selected(($userGoods->collect_type ?? 'one') === 'one')>1つでいい</option>
            <option value="collect" @selected(($userGoods->collect_type ?? 'one') === 'collect')>集める</option>
        </select>
    </section>

    <p id="error" style="color: #dc2626; font-size: 14px; margin-top: 12px;"></p>

    {{-- 欲しい！ボタン（縦長の画面の右下、下のメニューの上に固定） --}}
    <button type="button" id="wish-button"
            data-url="{{ route('wishes.toggle', $goods) }}"
            aria-label="{{ $wished ? '欲しい！から外す' : '欲しい！に追加' }}"
            aria-pressed="{{ $wished ? 'true' : 'false' }}"
            style="position: fixed; right: max(16px, calc(50% - 14rem + 16px)); bottom: 72px; width: 60px; height: 60px;
                   border-radius: 50%; border: none; background: #fff;
                   box-shadow: 0 2px 8px rgba(0,0,0,0.25);
                   display: flex; align-items: center; justify-content: center;
                   cursor: pointer; z-index: 50;">
        <svg width="30" height="30" viewBox="0 0 24 24" aria-hidden="true">
            <path id="wish-heart"
                  d="M12 21s-7.5-4.6-9.5-9.2C1.2 8.6 3.2 5 6.6 5c2 0 3.4 1.1 4.4 2.5C12 6.1 13.4 5 15.4 5c3.4 0 5.4 3.6 4.1 6.8C19.5 16.4 12 21 12 21z"
                  stroke="#e0457b" stroke-width="2"
                  fill="{{ $wished ? '#e0457b' : 'none' }}"/>
        </svg>
    </button>

    <x-slot name="scripts">
<script>
    // レイアウトの <meta> に入っている CSRF トークンを読む
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
        // 欲しい！ボタン
    const wishButton = document.getElementById('wish-button');
    const wishHeart = document.getElementById('wish-heart');

    wishButton.addEventListener('click', async () => {
        try {
            const res = await fetch(wishButton.dataset.url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
            });
            if (!res.ok) throw new Error('欲しい！に追加できませんでした');

            const data = await res.json();

            // 返ってきた状態に合わせて、ハートを塗る・塗らない
            wishHeart.setAttribute('fill', data.wished ? '#e0457b' : 'none');
            wishButton.setAttribute('aria-pressed', data.wished ? 'true' : 'false');
            wishButton.setAttribute('aria-label', data.wished ? '欲しい！から外す' : '欲しい！に追加');
            errorText.textContent = '';
        } catch (e) {
            errorText.textContent = e.message;
        }
    });
</script>
    </x-slot>
</x-mobile-layout>
