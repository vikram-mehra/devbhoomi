@extends('layouts.admin')

@section('title', __('Blog posts'))

@section('page_subtitle')
    {{ __('Create and publish editorial articles for the storefront “From the blog” section.') }}
@endsection

@section('breadcrumbs')
    <a href="{{ route('admin.blog-posts.create') }}" class="btn btn-primary btn-sm">{{ __('New post') }}</a>
@endsection

@section('content')
    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="admin-data-card__title d-block">{{ __('All posts') }}</span>
                <span class="admin-data-card__meta">{{ __('Drafts and published articles.') }}</span>
            </div>
            <a href="{{ route('admin.blog-posts.create') }}" class="btn btn-sm btn-primary">{{ __('New post') }}</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Slug') }}</th>
                            <th>{{ __('Published') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $p)
                            <tr>
                                <td class="fw-semibold">{{ Str::limit($p->title, 56) }}</td>
                                <td class="small font-monospace text-break"><code>{{ Str::limit($p->slug, 40) }}</code></td>
                                <td>
                                    @if($p->is_published)
                                        <span class="admin-chip admin-chip--success">{{ __('Live') }}</span>
                                    @else
                                        <span class="admin-chip admin-chip--muted">{{ __('Draft') }}</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $p->published_at?->format('Y-m-d') ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('blog.show', $p) }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">{{ __('View') }}</a>
                                    <a href="{{ route('admin.blog-posts.edit', $p) }}" class="btn btn-sm btn-outline-primary">{{ __('Edit') }}</a>
                                    <form method="post" action="{{ route('admin.blog-posts.destroy', $p) }}" class="d-inline" onsubmit="return confirm(@json(__('Delete this post?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">{{ __('No posts yet.') }} <a href="{{ route('admin.blog-posts.create') }}">{{ __('Create one') }}</a></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="admin-pagination-wrap">{{ $posts->links() }}</div>
@endsection
