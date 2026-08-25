@use('App\Models\Thesis')
@use('App\Support\Locale')
@extends('admin.layout')

@section('title', __('admin.theses.title'))

@section('content')
    <div class="card">
        <div class="card-head">
            <h2>{{ __('admin.theses.title') }}</h2>
            <a href="{{ route('admin.theses.create') }}" class="btn btn-primary">{{ __('admin.theses.new') }}</a>
        </div>

        {{-- What is waiting to be looked at comes first: that is the only
             thing on this page that anybody is blocked on. --}}
        <nav class="filters" aria-label="{{ __('admin.theses.by_status') }}">
            <a href="{{ route('admin.theses') }}" class="chip{{ $status === null ? ' is-active' : '' }}">
                {{ __('admin.theses.all') }}
            </a>
            @foreach (Thesis::STATUSES as $option)
                <a href="{{ route('admin.theses', ['status' => $option]) }}"
                   class="chip{{ $status === $option ? ' is-active' : '' }}">
                    {{ __('admin.theses.statuses.'.$option) }}
                    <span class="chip-count">{{ $counts[$option] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>

        @if ($theses->isEmpty())
            <p class="empty">{{ __('admin.theses.empty') }}</p>
        @else
            <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('admin.theses.table.title') }}</th>
                        <th>{{ __('admin.theses.table.author') }}</th>
                        <th>{{ __('admin.theses.table.degree') }}</th>
                        <th>{{ __('admin.theses.table.year') }}</th>
                        <th>{{ __('admin.theses.table.status') }}</th>
                        <th>{{ __('admin.actions.edit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($theses as $thesis)
                        <tr>
                            <td>
                                <span class="row-title" dir="auto">{{ $thesis->title }}</span>
                                @if ($thesis->isUnderEmbargo())
                                    <span class="tag tag-warn">{{ __('admin.theses.embargo_until', ['date' => $thesis->embargo_until->toDateString()]) }}</span>
                                @endif
                            </td>
                            <td dir="auto">{{ $thesis->author }}</td>
                            <td dir="auto">{{ __('theses.degrees.'.$thesis->degree) }}</td>
                            <td dir="ltr">{{ $thesis->year }}</td>
                            <td>
                                <span class="tag tag-{{ $thesis->status }}">{{ __('admin.theses.statuses.'.$thesis->status) }}</span>
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.theses.edit', $thesis) }}" class="btn btn-secondary btn-sm">{{ __('admin.actions.edit') }}</a>
                                @if ($thesis->isPublished())
                                    <a href="{{ Locale::thesisUrl($thesis->id) }}" target="_blank" rel="noopener"
                                       class="btn btn-secondary btn-sm">{{ __('admin.actions.view') }}</a>
                                @endif
                                <form method="POST" action="{{ route('admin.theses.destroy', $thesis) }}" style="display:inline;"
                                      onsubmit="return confirm(@js(__('admin.theses.confirm_delete')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">{{ __('admin.actions.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            @if ($theses->hasPages())
                @include('admin.partials.pagination', ['paginator' => $theses])
            @endif
        @endif
    </div>
@endsection
