<x-main-layout title="アカウント" active="account">

    @php
        // 保存したあと・入力エラーのときは、設定を開いたまま表示する
        $settingsOpen = $errors->any()
            || $errors->updatePassword->isNotEmpty()
            || $errors->userDeletion->isNotEmpty()
            || in_array(session('status'), ['profile-updated', 'password-updated', 'verification-link-sent'], true);
    @endphp

    {{-- アイコンと名前（押すと、メールアドレス・パスワード・アカウント削除の設定が開く） --}}
    <details id="account-settings" @if ($settingsOpen) open @endif
             style="margin-bottom: 24px; border: 1px solid #e5e7eb; border-radius: 12px;">
        <summary style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; min-height: 72px; cursor: pointer; list-style: none;">
            {{-- アイコン（名前の1文字目） --}}
            <span aria-hidden="true"
                  style="width: 48px; height: 48px; flex-shrink: 0; border-radius: 50%; background: #e0e7ff; color: #4f46e5;
                         display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
                {{ mb_substr($user->name, 0, 1) }}
            </span>
            <span style="flex: 1; min-width: 0;">
                <span style="display: block; font-weight: bold; font-size: 16px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $user->name }}</span>
                <span style="display: block; font-size: 12px; color: #888;">メールアドレス・パスワード・アカウント削除</span>
            </span>
            {{-- 開いているかどうかの矢印 --}}
            <svg class="account-chevron" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" style="flex-shrink: 0; transition: transform 0.2s;">
                <path d="M6 9l6 6 6-6" stroke="#6b7280" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </summary>

        {{-- 設定（Breeze のフォームをそのまま使う） --}}
        <div style="display: flex; flex-direction: column; gap: 16px; padding: 0 16px 16px;">
            <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;">
                @include('profile.partials.update-password-form')
            </div>

            <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </details>

    {{-- summary の標準の三角を消し、開いているときは矢印を上向きにする --}}
    <style>
        #account-settings > summary::-webkit-details-marker { display: none; }
        #account-settings[open] .account-chevron { transform: rotate(180deg); }
    </style>

    {{-- 欲しい！に追加したもの（トップページから移動） --}}
    <section style="margin-bottom: 32px;">
        <h2 style="font-weight: bold; margin-bottom: 8px;">欲しい！に追加したもの（{{ $wishes->count() }}件）</h2>
        @if ($wishes->isEmpty())
            <p style="font-size: 14px; color: #888;">グッズ詳細のハートを押すと、ここに表示されます</p>
        @else
            <div class="app-grid">
                @foreach ($wishes as $wish)
                    <a href="{{ route('goods.show', $wish->goods) }}" style="text-align: center;">
                        @if ($wish->goods->image_path)
                            <img src="{{ asset('storage/' . $wish->goods->image_path) }}" alt="{{ $wish->goods->name }}"
                                 style="width: 100%; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                        @else
                            <div style="width: 100%; aspect-ratio: 1 / 1; background: #eee; border-radius: 8px;"></div>
                        @endif
                        <p style="font-size: 12px; margin-top: 4px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $wish->goods->name }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

</x-main-layout>
