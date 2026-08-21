@extends('admin.layout')

@section('title', 'بەشەکان')

@section('content')
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
            <h2 style="font-size:1.15rem;">بەشە زانستییەکان ({{ $departments->total() }})</h2>
            <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">+ زیادکردنی بەشی نوێ</a>
        </div>
        @if ($departments->isEmpty())
            <p style="color:#6b6b80; padding:1.5rem 0; text-align:center;">هیچ بەشێک نەنووسراوە.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ڕیز</th>
                        <th>ئایکۆن</th>
                        <th>ناو (سۆرانی)</th>
                        <th>ناو (ئینگلیزی)</th>
                        <th>بەستەر</th>
                        <th>کردارەکان</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($departments as $department)
                        <tr>
                            <td>{{ $department->sort_order }}</td>
                            <td><span class="icon-preview">{{ $department->icon }}</span></td>
                            <td>{{ $department->translation('ku-sorani', 'title') }}</td>
                            <td>{{ $department->translation('en', 'title') }}</td>
                            <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <a href="{{ $department->drive_url }}" target="_blank" rel="noopener" style="color:#667eea;">{{ $department->drive_url }}</a>
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-secondary">دەستکاری</a>
                                <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" style="display:inline;"
                                      onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم بەشە؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">سڕینەوە</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">
                @if ($departments->onFirstPage())
                    <span>«</span>
                @else
                    <a href="{{ $departments->previousPageUrl() }}">«</a>
                @endif
                @foreach ($departments->getUrlRange(1, $departments->lastPage()) as $page => $url)
                    @if ($page === $departments->currentPage())
                        <span class="current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
                @if ($departments->hasMorePages())
                    <a href="{{ $departments->nextPageUrl() }}">»</a>
                @else
                    <span>»</span>
                @endif
            </div>
        @endif
    </div>
@endsection
