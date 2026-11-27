{{--
    各1ページの「まとめて各1に登録」
    見出し横の二本線ボタン（#bulk-one-open）を押すと、画面の下からシートで出てくる
    1. メンバーを選ぶ → 2. 種類を選ぶ → 3. 対象のグッズにチェックして登録
    受け取る値：$bulkOne['members'], $bulkOne['categories']
--}}
{{-- シートの後ろの暗い部分（押すと閉じる） --}}
<div id="bulk-one-backdrop" hidden
     style="position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 70;"></div>

<section id="bulk-one-sheet" role="dialog" aria-modal="true" aria-labelledby="bulk-one-title" hidden
         data-candidates-url="{{ route('user-goods.bulk-one.candidates') }}"
         data-store-url="{{ route('user-goods.bulk-one.store') }}"
         data-undo-url="{{ route('user-goods.bulk-one.undo') }}"
         class="bulk-one-sheet"
         style="position: fixed; left: 0; right: 0; bottom: 0; z-index: 71; margin: 0 auto; max-width: 28rem; max-height: 85vh;
                overflow-y: auto; overscroll-behavior: contain; padding: 8px 16px 24px; background: #fff;
                border-radius: 16px 16px 0 0; box-shadow: 0 -2px 12px rgba(0,0,0,0.2);">

    {{-- つまみと、タイトル・閉じるボタン（スクロールしても上に残す） --}}
    <div style="position: sticky; top: -8px; background: #fff; padding-top: 8px; margin-bottom: 8px; z-index: 1;">
        <div class="bulk-one-handle" aria-hidden="true" style="width: 40px; height: 4px; background: #d1d5db; border-radius: 2px; margin: 0 auto 8px;"></div>
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <h2 id="bulk-one-title" style="font-weight: bold; font-size: 18px;">まとめて各1に登録</h2>
            <button type="button" id="bulk-one-close" aria-label="閉じる"
                    style="width: 44px; height: 44px; border: none; background: none; font-size: 24px; line-height: 1; cursor: pointer;">×</button>
        </div>
    </div>

    {{-- 1. メンバーを選ぶ（1人だけ） --}}
    <h3 style="font-weight: bold; font-size: 14px; margin-bottom: 8px;">1. メンバーを選ぶ</h3>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 16px;">
        @foreach ($bulkOne['members'] as $member)
            <button type="button" class="bulk-one-choice" data-kind="member" data-id="{{ $member->id }}" aria-pressed="false"
                    style="text-align: center; background: none; border: none; padding: 0; cursor: pointer;">
                @if ($member->image_path)
                    <img src="{{ asset('storage/' . $member->image_path) }}" alt=""
                         class="bulk-one-icon"
                         style="width: 100%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 8px; border: 3px solid #ddd;">
                @else
                    <div class="bulk-one-icon" style="width: 100%; aspect-ratio: 1 / 1; background: #eee; border-radius: 8px; border: 3px solid #ddd;"></div>
                @endif
                <span style="display: block; font-size: 12px; margin-top: 4px;">{{ $member->name }}</span>
            </button>
        @endforeach
    </div>

    {{-- 2. 種類を選ぶ（1つだけ） --}}
    <h3 style="font-weight: bold; font-size: 14px; margin-bottom: 8px;">2. 種類を選ぶ</h3>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 16px;">
        @foreach ($bulkOne['categories'] as $category)
            <button type="button" class="bulk-one-choice" data-kind="category" data-id="{{ $category->id }}" aria-pressed="false"
                    style="text-align: center; background: none; border: none; padding: 0; cursor: pointer;">
                @if ($category->image_path)
                    <img src="{{ asset('storage/' . $category->image_path) }}" alt=""
                         class="bulk-one-icon"
                         style="width: 100%; aspect-ratio: 1 / 1; object-fit: contain; padding: 8px; background: #fff; border-radius: 8px; border: 3px solid #ddd;">
                @else
                    <div class="bulk-one-icon" style="width: 100%; aspect-ratio: 1 / 1; background: #eee; border-radius: 8px; border: 3px solid #ddd;"></div>
                @endif
                <span style="display: block; font-size: 12px; margin-top: 4px;">{{ $category->name }}</span>
            </button>
        @endforeach
    </div>

    {{-- 3. 対象のグッズ（メンバーと種類を両方選ぶと表示する） --}}
    <h3 style="font-weight: bold; font-size: 14px; margin-bottom: 8px;">3. 対象のグッズ</h3>
    <p id="bulk-one-message" style="font-size: 14px; color: #888; margin-bottom: 8px;">メンバーと種類を選ぶと、ここに表示されます</p>
    <ul id="bulk-one-list" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;"></ul>

    <button type="button" id="bulk-one-submit" disabled
            style="width: 100%; min-height: 52px; border: none; border-radius: 12px; font-size: 16px; font-weight: bold; color: #fff; background: #c7c9d9;">
        0件を各1に登録する
    </button>
    <p id="bulk-one-error" style="color: #dc2626; font-size: 14px; margin-top: 8px;"></p>
</section>

{{-- パソコンでは、シートを画面の真ん中に出す（スマホでは画面の下から） --}}
<style>
    html[data-view="pc"] .bulk-one-sheet {
        top: 50% !important; bottom: auto !important; transform: translateY(-50%);
        max-width: 40rem !important; border-radius: 16px !important; padding-bottom: 16px !important;
    }
    html[data-view="pc"] .bulk-one-handle { display: none; }   /* 引っぱるつまみはスマホ用 */
</style>

{{-- 登録したあとに画面の下に出すお知らせ（スマホでは下のメニューの上） --}}
<div id="bulk-one-toast" class="app-toast" role="status" aria-live="polite" hidden>
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;
                background: #1f2937; color: #fff; border-radius: 12px; padding: 12px 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.25);">
        <span id="bulk-one-toast-text" style="font-size: 14px;"></span>
        <button type="button" id="bulk-one-undo"
                style="min-height: 44px; padding: 0 12px; border: none; background: none; color: #a5b4fc; font-weight: bold; font-size: 15px;">
            元に戻す
        </button>
    </div>
</div>

<script>
(() => {
    const root = document.getElementById('bulk-one-sheet');
    const backdrop = document.getElementById('bulk-one-backdrop');
    const openButton = document.getElementById('bulk-one-open');
    const closeButton = document.getElementById('bulk-one-close');
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const message = document.getElementById('bulk-one-message');
    const list = document.getElementById('bulk-one-list');
    const submit = document.getElementById('bulk-one-submit');
    const errorText = document.getElementById('bulk-one-error');
    const toast = document.getElementById('bulk-one-toast');
    const toastText = document.getElementById('bulk-one-toast-text');
    const undoButton = document.getElementById('bulk-one-undo');

    // 再読み込みしても、選んだメンバー・種類とお知らせを覚えておくためのキー
    const STORAGE_KEY = 'bulkOne';

    // いま選んでいるメンバーと種類
    const selected = { member: null, category: null };

    // sessionStorage は使えないこともあるので、失敗しても動くようにする
    const storage = {
        get() { try { return JSON.parse(sessionStorage.getItem(STORAGE_KEY)) ?? {}; } catch (e) { return {}; } },
        set(value) { try { sessionStorage.setItem(STORAGE_KEY, JSON.stringify(value)); } catch (e) {} },
    };

    // JSON を送って受け取る（エラーのときは message を投げる）
    async function request(url, method, body) {
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: body ? JSON.stringify(body) : undefined,
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message ?? 'うまくいきませんでした。もう一度お試しください');
        return data;
    }

    // ---- シートを開く・閉じる ----

    function openSheet() {
        root.hidden = false;
        backdrop.hidden = false;
        openButton.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';   // 後ろの一覧がスクロールしないようにする
        closeButton.focus();
    }
    function closeSheet() {
        root.hidden = true;
        backdrop.hidden = true;
        openButton.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        openButton.focus();
    }
    openButton.addEventListener('click', openSheet);
    closeButton.addEventListener('click', closeSheet);
    backdrop.addEventListener('click', closeSheet);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !root.hidden) closeSheet();
    });

    // ---- 1・2. メンバーと種類を選ぶ ----

    // 選んだものは枠の色を変える
    function choose(kind, id) {
        selected[kind] = id;
        root.querySelectorAll(`.bulk-one-choice[data-kind="${kind}"]`).forEach((button) => {
            const on = button.dataset.id === id;
            button.setAttribute('aria-pressed', on ? 'true' : 'false');
            button.querySelector('.bulk-one-icon').style.borderColor = on ? '#6366f1' : '#ddd';
            button.querySelector('span').style.fontWeight = on ? 'bold' : 'normal';
        });
    }

    root.querySelectorAll('.bulk-one-choice').forEach((button) => {
        button.addEventListener('click', () => {
            choose(button.dataset.kind, button.dataset.id);
            loadCandidates();
        });
    });

    // ---- 3. 対象のグッズを表示する ----

    async function loadCandidates() {
        errorText.textContent = '';
        if (!selected.member || !selected.category) return;

        message.textContent = '読み込み中…';
        list.replaceChildren();
        updateButton();

        try {
            const url = `${root.dataset.candidatesUrl}?member=${encodeURIComponent(selected.member)}&category=${encodeURIComponent(selected.category)}`;
            const data = await request(url, 'GET');

            message.textContent = data.goods.length === 0 ? 'このメンバー × 種類のグッズはありません' : '';
            data.goods.forEach((item) => list.append(renderItem(item)));
        } catch (e) {
            message.textContent = '';
            errorText.textContent = e.message;
        }
        updateButton();
    }

    // グッズ1件分の行を作る（名前は textContent で入れるので、HTML として解釈されない）
    function renderItem(item) {
        const li = document.createElement('li');
        const isCollect = item.collect_type === 'collect';

        // 行全体を押せるように label で囲む（回収のものは押せない）
        const row = document.createElement(isCollect ? 'div' : 'label');
        row.style.cssText = 'display: flex; align-items: center; gap: 12px; padding: 8px; min-height: 64px; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;'
            + (isCollect ? ' opacity: 0.6;' : ' cursor: pointer;');

        let thumb;
        if (item.image_url) {
            thumb = document.createElement('img');
            thumb.src = item.image_url;
            thumb.alt = '';
            thumb.style.objectFit = 'cover';
        } else {
            thumb = document.createElement('div');
            thumb.style.background = '#eee';
        }
        thumb.style.width = '56px';
        thumb.style.height = '56px';
        thumb.style.flexShrink = '0';
        thumb.style.borderRadius = '8px';

        const text = document.createElement('div');
        text.style.flex = '1';
        const name = document.createElement('p');
        name.textContent = item.name;
        name.style.fontSize = '14px';
        text.append(name);

        if (isCollect) {
            // 「回収」のものは対象にしない（チェックボックスを出さない）
            const note = document.createElement('p');
            note.textContent = '回収に設定済みのため変更しません';
            note.style.cssText = 'font-size: 12px; color: #888;';
            text.append(note);
            row.append(thumb, text);
        } else {
            if (item.collect_type === 'one') {
                const note = document.createElement('p');
                note.textContent = '各1に設定済み';
                note.style.cssText = 'font-size: 12px; color: #6366f1;';
                text.append(note);
            }
            // 最初は全部チェックを入れておく（すでに各1のものもチェック済み）
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = true;
            checkbox.value = item.id;
            checkbox.className = 'bulk-one-check';
            checkbox.setAttribute('aria-label', `${item.name} を各1に登録する`);
            checkbox.style.cssText = 'width: 28px; height: 28px; flex-shrink: 0; accent-color: #6366f1;';
            checkbox.addEventListener('change', updateButton);
            row.append(thumb, text, checkbox);
        }

        li.append(row);
        return li;
    }

    // ボタンに件数を出す。0件なら押せない
    function checkedIds() {
        return [...list.querySelectorAll('.bulk-one-check:checked')].map((c) => Number(c.value));
    }
    function updateButton() {
        const count = checkedIds().length;
        submit.textContent = `${count}件を各1に登録する`;
        submit.disabled = count === 0;
        submit.style.background = count === 0 ? '#c7c9d9' : '#6366f1';
    }

    // ---- 登録する ----

    submit.addEventListener('click', async () => {
        const ids = checkedIds();
        if (ids.length === 0) return;

        submit.disabled = true;
        errorText.textContent = '';
        try {
            const data = await request(root.dataset.storeUrl, 'POST', { goods_ids: ids });
            const text = data.count > 0 ? `${data.count}件を各1に登録しました` : 'すべて各1に設定済みです';

            // 一覧にすぐ反映するため再読み込みする（シートは閉じた状態に戻る）
            // お知らせは再読み込みのあとに出す。選んだメンバー・種類も覚えておき、次に開いたときにそのまま使える
            storage.set({ member: selected.member, category: selected.category, toast: text, canUndo: data.count > 0 });
            location.reload();
        } catch (e) {
            errorText.textContent = e.message;
            updateButton();
        }
    });

    // ---- お知らせと「元に戻す」 ----

    let toastTimer;
    function showToast(text, canUndo) {
        toastText.textContent = text;
        undoButton.hidden = !canUndo;
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.hidden = true; }, 5000);   // 5秒で消す
    }

    undoButton.addEventListener('click', async () => {
        undoButton.disabled = true;
        try {
            await request(root.dataset.undoUrl, 'POST');
            storage.set({ member: selected.member, category: selected.category, toast: '元に戻しました', canUndo: false });
            location.reload();
        } catch (e) {
            showToast(e.message, false);
        } finally {
            undoButton.disabled = false;
        }
    });

    // ---- ページを開いたとき：再読み込み前の状態を戻す ----

    const saved = storage.get();
    storage.set({});
    if (saved.member && saved.category) {
        choose('member', saved.member);
        choose('category', saved.category);
        loadCandidates();
    }
    if (saved.toast) {
        showToast(saved.toast, saved.canUndo);
    }
})();
</script>
