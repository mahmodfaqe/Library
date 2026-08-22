@extends('admin.layout')

@section('title', __('admin.categories.title'))

@section('content')
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:0.5rem;">
            <h2 style="font-size:1.15rem;">{{ __('admin.categories.heading', ['count' => $categories->count()]) }}</h2>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">{{ __('admin.categories.add') }}</a>
        </div>

        @if ($categories->isEmpty())
            <p style="color:#6b6b80; padding:1.5rem 0; text-align:center;">{{ __('admin.categories.empty') }}</p>
        @else
            <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('admin.categories.table.order') }}</th>
                        <th>{{ __('admin.categories.table.icon') }}</th>
                        <th>{{ __('admin.categories.table.name') }}</th>
                        <th>{{ __('admin.categories.table.translated') }}</th>
                        <th>{{ __('admin.categories.table.books') }}</th>
                        <th>{{ __('admin.categories.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>{{ $category->sort_order }}</td>
                            <td><span class="icon-preview">{{ $category->icon }}</span></td>
                            <td dir="auto">{{ $category->name }}</td>
                            <td style="font-size:0.85rem; color:#6b6b80;">
                                {{ count($category->translations ?? []) }} / {{ count(\App\Support\Locale::SUPPORTED) - 1 }}
                            </td>
                            <td>{{ $category->books_count }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-secondary">{{ __('admin.actions.edit') }}</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display:inline;"
                                      onsubmit="return confirm(@js(__('admin.categories.confirm_delete')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">{{ __('admin.actions.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
@endsection
