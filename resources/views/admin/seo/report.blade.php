@extends('layouts.admin')

@section('title', __('SEO Report'))

@section('page_subtitle')
    {{ __('Audit results for products, categories, blog posts, and technical SEO. Apply automatic fixes where available.') }}
@endsection

@section('content')
    @if(session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    @if(session('fixes'))
        <div class="alert alert-info py-2 small">
            <ul class="mb-0 ps-3">
                @foreach(session('fixes') as $fix)
                    <li>{{ $fix }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-4">
        <span class="badge bg-danger">{{ __('High') }}: {{ $counts['high'] }}</span>
        <span class="badge bg-warning text-dark">{{ __('Medium') }}: {{ $counts['medium'] }}</span>
        <span class="badge bg-secondary">{{ __('Low') }}: {{ $counts['low'] }}</span>
        <form method="post" action="{{ route('admin.seo.apply-fixes') }}" class="ms-auto">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm({{ json_encode(__('Apply auto-fixes for missing meta titles/descriptions?')) }})">
                {{ __('Apply auto-fixes') }}
            </button>
        </form>
        <a href="{{ route('admin.seo.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('SEO settings') }}</a>
    </div>

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Issue') }}</th>
                        <th>{{ __('Page / entity') }}</th>
                        <th>{{ __('Suggested fix') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issues as $issue)
                        <tr>
                            <td>
                                @if($issue['priority'] === 'high')
                                    <span class="badge bg-danger">{{ __('High') }}</span>
                                @elseif($issue['priority'] === 'medium')
                                    <span class="badge bg-warning text-dark">{{ __('Medium') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Low') }}</span>
                                @endif
                            </td>
                            <td class="small fw-semibold">{{ $issue['type'] }}</td>
                            <td class="small">{{ Str::limit($issue['entity'], 48) }}</td>
                            <td class="small text-muted">{{ $issue['fix'] }}</td>
                            <td class="text-end">
                                @if(Str::startsWith($issue['url'], ['http://', 'https://']))
                                    <a href="{{ $issue['url'] }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">{{ __('View') }}</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">{{ __('No SEO issues found — great job!') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
