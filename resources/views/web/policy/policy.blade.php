@extends('web.layout.master')

@section('required-page-title', '서비스이용약관')
@section('required-page-header-css')
    <link rel="stylesheet" href="/web/styles/policy/terms.css">
@stop

@section('required-page-header-js')
@stop

@section('required-page-banner-blade')
@stop

@section('required-page-main-content')
    <main>
        <section class="container_list">
            <div class="container w-820">
                <h2>서비스이용약관</h2>
                <div class="list_type_container">
                    <select name="policy_version" id="policy_version_select" onchange="changePolicyVersion(this.value)">
                        <option value="">-- 버전 선택 --</option>
                        @foreach($policy_selects as $policy_item)
                            <option value="{{ $policy_item->version }}"
                                {{ request()->route('version') == $policy_item->version ? 'selected' : '' }}>
                                {{ date('Y.m.d', strtotime($policy_item->created_at)) }}
                                @if($policy_item->version ?? false)
                                    ({{ $policy_item->version }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="terms_content">
                    @if($policy_contents)
                        {!! $policy_contents->info !!}}
                    @else
                        <p class="txt-1">준비중입니다.</p>
                    @endif
                </div>
            </div>
        </section>
    </main>
@stop

@section('required-page-add-content')
    <script>
        function changePolicyVersion(selectedValue) {
            // 선택된 값이 없으면 기본 페이지로
            if (!selectedValue || selectedValue === '') {
                window.location.href = '{{ route("front_policy.index") }}';
                return;
            }

            // 선택된 버전으로 페이지 이동
            window.location.href = '{{ route("front_policy.index") }}/' + selectedValue;
        }

        // 페이지 로드 시 현재 선택된 버전 표시
        document.addEventListener('DOMContentLoaded', function() {
            const currentVersion = '{{ request()->route("version") ?? "" }}';
            const selectBox = document.getElementById('policy_version_select');

            if (currentVersion && selectBox) {
                selectBox.value = currentVersion;
            }
        });
    </script>
@stop
