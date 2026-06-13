@extends('layouts.admin')

@section('title', __('Contact page'))

@section('page_subtitle')
    {{ __('Edit contact details, hours, map link, and form text. Customer messages appear below.') }}
@endsection

@section('content')
    <div class="admin-form-hero card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="admin-form-hero__strip"></div>
        <div class="card-body p-4 p-lg-5">
            <form method="post" action="{{ route('admin.contact-page.update') }}" class="row g-3">
                @csrf

                <div class="col-12"><h2 class="h6 text-uppercase text-muted mb-0">{{ __('Page header') }}</h2></div>
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Title') }} *</label>
                    <input type="text" name="hero_title" class="form-control @error('hero_title') is-invalid @enderror" value="{{ old('hero_title', $page->hero_title) }}" required maxlength="255">
                    @error('hero_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Subtitle') }}</label>
                    <textarea name="hero_subtitle" class="form-control" rows="2" maxlength="2000">{{ old('hero_subtitle', $page->hero_subtitle) }}</textarea>
                </div>

                <div class="col-12 pt-3"><h2 class="h6 text-uppercase text-muted mb-0">{{ __('Contact details') }}</h2></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $page->email) }}" maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Phone') }}</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $page->phone) }}" maxlength="40">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('WhatsApp') }}</label>
                    <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $page->whatsapp) }}" maxlength="40">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Address') }}</label>
                    <textarea name="address" class="form-control" rows="3" maxlength="2000">{{ old('address', $page->address) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Map URL') }}</label>
                    <input type="url" name="map_url" class="form-control" value="{{ old('map_url', $page->map_url) }}" placeholder="https://maps.google.com/...">
                    <div class="form-text">{{ __('Paste a Google Maps link or embed URL to show a map on the contact page.') }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Weekday hours') }}</label>
                    <input type="text" name="hours_weekdays" class="form-control" value="{{ old('hours_weekdays', $page->hours_weekdays) }}" maxlength="120">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Weekend hours') }}</label>
                    <input type="text" name="hours_weekend" class="form-control" value="{{ old('hours_weekend', $page->hours_weekend) }}" maxlength="120">
                </div>

                <div class="col-12 pt-3"><h2 class="h6 text-uppercase text-muted mb-0">{{ __('Contact form') }}</h2></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Form heading') }}</label>
                    <input type="text" name="form_heading" class="form-control" value="{{ old('form_heading', $page->form_heading) }}" maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Form subtext') }}</label>
                    <input type="text" name="form_subtext" class="form-control" value="{{ old('form_subtext', $page->form_subtext) }}" maxlength="255">
                </div>

                @include('admin.partials.seo-fields', ['entity' => $page])

                <div class="col-12">
                    <div class="form-check">
                        <input type="hidden" name="is_published" value="0">
                        <input class="form-check-input" type="checkbox" name="is_published" id="contact_pub" value="1" {{ old('is_published', $page->is_published) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="contact_pub">{{ __('Published on site') }}</label>
                    </div>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">{{ __('Save contact page') }}</button>
                    <a href="{{ route('pages.contact') }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">{{ __('Preview') }}</a>
                </div>
            </form>
        </div>
    </div>

    @if($inquiries->isNotEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0">{{ __('Recent messages') }}</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Message') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inquiries as $inq)
                            <tr class="{{ $inq->read_at ? 'text-muted' : '' }}">
                                <td class="small text-nowrap">{{ $inq->created_at->format('d M Y') }}</td>
                                <td>{{ $inq->name }}</td>
                                <td><a href="mailto:{{ $inq->email }}">{{ $inq->email }}</a></td>
                                <td class="small">{{ Str::limit($inq->message, 80) }}</td>
                                <td class="text-end">
                                    @unless($inq->read_at)
                                        <form method="post" action="{{ route('admin.contact-inquiries.read', $inq) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('Mark read') }}</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
