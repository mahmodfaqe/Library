@extends('admin.layout')

@section('title', __('admin.activity.title'))

@section('content')
    <div class="card">
        <h2 style="font-size:1.15rem;">{{ __('admin.activity.heading', ['count' => $activities->total()]) }}</h2>
        <p style="color:#6b6b80; font-size:0.88rem; margin-top:0.4rem;">{{ __('admin.activity.blurb') }}</p>

        @if ($activities->isEmpty())
            <p style="color:#6b6b80; padding:1.5rem 0; text-align:center;">{{ __('admin.activity.empty') }}</p>
        @else
            <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('admin.activity.table.when') }}</th>
                        <th>{{ __('admin.activity.table.who') }}</th>
                        <th>{{ __('admin.activity.table.what') }}</th>
                        <th>{{ __('admin.activity.table.subject') }}</th>
                        <th>{{ __('admin.activity.table.ip') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($activities as $activity)
                        <tr>
                            <td style="white-space:nowrap; font-size:0.82rem; color:#6b6b80;">{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $activity->actor_name }}</td>
                            <td><code dir="ltr" style="font-size:0.82rem;">{{ $activity->action }}</code></td>
                            <td>{{ $activity->subject ?: '—' }}</td>
                            <td dir="ltr" style="font-size:0.8rem; color:#8a8aa0;">{{ $activity->ip ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $activities])
        @endif
    </div>
@endsection
