@extends('admin.layout')

@section('title', 'ڕەخنە و پێشنیار')

@section('content')
    <div class="card">
        <h2 style="font-size:1.15rem;">ڕەخنە و پێشنیارەکان ({{ $feedback->total() }})</h2>
        @if ($feedback->isEmpty())
            <p style="color:#6b6b80; padding:1.5rem 0; text-align:center;">هێشتا هیچ ڕەخنە و پێشنیارێک نەنێردراوە.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ناو</th>
                        <th>ڕەخنە / پێشنیار</th>
                        <th>بەروار</th>
                        <th>کردارەکان</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($feedback as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name ?: '—' }}</td>
                            <td style="max-width:420px; white-space:pre-wrap; line-height:1.6;">{{ $item->message }}</td>
                            <td style="white-space:nowrap; font-size:0.82rem; color:#6b6b80;">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                            <td class="actions">
                                <form method="POST" action="{{ route('admin.feedback.destroy', $item) }}" style="display:inline;"
                                      onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم ڕەخنە/پێشنیارە؟');">
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
                @if ($feedback->onFirstPage())
                    <span>«</span>
                @else
                    <a href="{{ $feedback->previousPageUrl() }}">«</a>
                @endif
                @foreach ($feedback->getUrlRange(1, $feedback->lastPage()) as $page => $url)
                    @if ($page === $feedback->currentPage())
                        <span class="current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
                @if ($feedback->hasMorePages())
                    <a href="{{ $feedback->nextPageUrl() }}">»</a>
                @else
                    <span>»</span>
                @endif
            </div>
        @endif
    </div>
@endsection
