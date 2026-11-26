{{--
    スマホ向け（縦長）の共通レイアウト
    使い方： <x-mobile-layout title="グッズ一覧" active="goods"> 中身 </x-mobile-layout>
    active … 下のメニューで太字にする項目（home / goods / collections / account）
--}}
@props(['title' => '', 'active' => ''])

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
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

    {{ $slot }}
</div>

{{-- 下のメニュー --}}
@php
    $menu = [
        'home'        => ['トップ', route('home')],
        'goods'       => ['各１', route('goods.index', ['collect_type' => 'one'])],   // 管理方法「1つでいい」だけ
        'collections' => ['回収', route('collections.index')],                         // 管理方法「集める」だけ
        'account'     => ['アカウント', route('profile.edit')],
    ];
@endphp
<nav style="position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #ddd;">
    <div style="max-width: 28rem; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); text-align: center; font-size: 14px;">
        @foreach ($menu as $key => [$label, $url])
            <a href="{{ $url }}" style="padding: 12px 0; {{ $active === $key ? 'font-weight: bold;' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
</nav>

{{ $scripts ?? '' }}
</body>
</html>
