{{--
    共通レイアウト（スマホ用：縦長の1列／パソコン用：横に広い画面）
    使い方： <x-main-layout title="グッズ一覧" active="goods"> 中身 </x-main-layout>
    active … メニューで太字にする項目（home / goods / collections / account）

    表示の切り替え
      ・<html data-view="sp|pc"> でスマホ用・パソコン用の見た目を切り替える（下の <style> を参照）
      ・最初は画面の幅で決める（768px 以上ならパソコン用）
      ・ヘッダーの切り替えボタンで選んだ表示は、このブラウザに覚えておく（localStorage）
--}}
@props(['title' => '', 'active' => ''])

@php
    $menu = [
        'home'        => ['トップ', route('home')],
        'goods'       => ['各１', route('goods.index', ['collect_type' => 'one'])],   // 管理方法「1つでいい」だけ
        'collections' => ['回収', route('collections.index')],                         // 管理方法「回収」だけ
        'account'     => ['アカウント', route('profile.edit')],
    ];
@endphp

<!DOCTYPE html>
<html lang="ja" data-view="sp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>

    {{-- 画面を描く前に表示（スマホ用／パソコン用）を決めて、ちらつかないようにする --}}
    <script>
        (function () {
            var saved = null;
            try { saved = localStorage.getItem('viewMode'); } catch (e) {}
            var auto = window.matchMedia('(min-width: 768px)').matches ? 'pc' : 'sp';
            document.documentElement.dataset.view = (saved === 'pc' || saved === 'sp') ? saved : auto;
        })();
    </script>

    <style>
        /* ---- スマホ用（基本） ---- */
        :root {
            --app-max: 28rem;      /* 中身の最大の横幅 */
            --app-pad: 16px;       /* 左右の余白 */
            --grid-cols: 4;        /* グッズやアイコンを横に並べる数 */
            --grid-gap: 8px;
            --fixed-bottom: 64px;  /* 右下のボタンやお知らせを置く高さ（下のメニューの上） */
        }
        /* ---- パソコン用 ---- */
        html[data-view="pc"] {
            --app-max: 72rem;
            --app-pad: 32px;
            --grid-cols: 8;
            --grid-gap: 16px;
            --fixed-bottom: 24px;  /* 下のメニューがないので、画面の下の近くに置く */
        }

        .app-container { max-width: var(--app-max); margin: 0 auto; min-height: 100vh; padding: 0 var(--app-pad) 80px; }
        html[data-view="pc"] .app-container { padding-bottom: 48px; }

        /* グッズやアイコンの並び（スマホ4列／パソコン8列） */
        .app-grid { display: grid; grid-template-columns: repeat(var(--grid-cols), minmax(0, 1fr)); gap: var(--grid-gap); }

        /* メニュー：スマホは画面の下に固定、パソコンはヘッダーの中 */
        .app-bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #ddd; z-index: 40; }
        .app-bottom-nav > div { max-width: 28rem; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); text-align: center; font-size: 14px; }
        .app-bottom-nav a { padding: 12px 0; }
        .app-top-nav { display: none; }
        html[data-view="pc"] .app-bottom-nav { display: none; }
        html[data-view="pc"] .app-top-nav { display: flex; gap: 4px; margin-right: auto; margin-left: 32px; }
        .app-top-nav a { padding: 8px 16px; border-radius: 8px; }
        .app-top-nav a:hover { background: #f3f4f6; }
        .app-top-nav a[aria-current="page"] { font-weight: bold; background: #eef2ff; color: #4338ca; }

        /* 右下に固定するボタン（中身の幅の右端に合わせる） */
        .app-fab { position: fixed; z-index: 50; bottom: calc(var(--fixed-bottom) + 8px);
                   right: max(16px, calc((100vw - var(--app-max)) / 2 + var(--app-pad))); }

        /* 画面の下に出すお知らせ */
        .app-toast { position: fixed; left: 0; right: 0; bottom: var(--fixed-bottom); z-index: 60; padding: 0 16px; }
        .app-toast > div { max-width: calc(28rem - 32px); margin: 0 auto; }

        /* 表示の切り替えボタン：いまの表示と反対の名前を出す */
        .view-to-pc { display: inline-flex; }
        .view-to-sp { display: none; }
        html[data-view="pc"] .view-to-pc { display: none; }
        html[data-view="pc"] .view-to-sp { display: inline-flex; }

        /* グッズ詳細：パソコンでは画像と情報を左右に並べる */
        html[data-view="pc"] .detail-layout { display: grid; grid-template-columns: minmax(0, 420px) minmax(0, 1fr); gap: 40px; align-items: start; }
        html[data-view="pc"] .detail-layout h1 { margin-top: 0 !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-800">
<div class="app-container">

    {{-- ヘッダー：ロゴ（パソコンではメニュー）・表示の切り替え・ログアウト --}}
    <header class="flex justify-between items-center py-4 gap-2">
        <a href="{{ route('home') }}" class="font-bold text-lg">ロゴ</a>

        <nav class="app-top-nav" aria-label="メニュー">
            @foreach ($menu as $key => [$label, $url])
                <a href="{{ $url }}" @if ($active === $key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2" style="margin-left: auto;">
            {{-- スマホ用 ⇔ パソコン用 の切り替え --}}
            <button type="button" id="view-toggle" class="border px-3 py-1 rounded items-center gap-1" style="display: inline-flex;">
                <span class="view-to-pc" style="align-items: center; gap: 4px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="12" rx="1"/><path d="M8 20h8M12 16v4"/>
                    </svg>
                    PC表示
                </span>
                <span class="view-to-sp" style="align-items: center; gap: 4px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>
                    </svg>
                    スマホ表示
                </span>
            </button>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="border px-3 py-1 rounded">ログアウト</button>
            </form>
        </div>
    </header>

    {{ $slot }}
</div>

{{-- 下のメニュー（スマホ用のときだけ表示） --}}
<nav class="app-bottom-nav" aria-label="メニュー">
    <div>
        @foreach ($menu as $key => [$label, $url])
            <a href="{{ $url }}" @if ($active === $key) aria-current="page" style="font-weight: bold;" @endif>{{ $label }}</a>
        @endforeach
    </div>
</nav>

<script>
    // 表示の切り替えボタン：押すたびにスマホ用 ⇔ パソコン用（ページは読み込み直さない）
    (function () {
        var root = document.documentElement;
        var button = document.getElementById('view-toggle');

        function label() {
            button.setAttribute('aria-label', root.dataset.view === 'pc' ? 'スマホ用の表示に切り替える' : 'パソコン用の表示に切り替える');
        }
        label();

        button.addEventListener('click', function () {
            root.dataset.view = root.dataset.view === 'pc' ? 'sp' : 'pc';
            try { localStorage.setItem('viewMode', root.dataset.view); } catch (e) {}
            label();
        });

        // まだボタンで選んでいないときは、画面の幅が変わったら自動で合わせる
        window.matchMedia('(min-width: 768px)').addEventListener('change', function (e) {
            var saved = null;
            try { saved = localStorage.getItem('viewMode'); } catch (err) {}
            if (saved !== 'pc' && saved !== 'sp') {
                root.dataset.view = e.matches ? 'pc' : 'sp';
                label();
            }
        });
    })();
</script>

{{ $scripts ?? '' }}
</body>
</html>
