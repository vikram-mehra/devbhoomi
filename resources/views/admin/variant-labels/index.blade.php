@extends('layouts.admin')

@section('title', __('Variant Labels & Options'))

@section('page_subtitle')
    {{ __('Configure product variant categories (e.g. Weight, Size, Color) and option values.') }}
@endsection

@section('content')
    <div class="row g-4">
        <!-- Create Label Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title h6 fw-bold mb-0">{{ __('Create Variant Label') }}</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.variant-labels.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">{{ __('Label Name') }} *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required placeholder="e.g. Weight, Size, Color" value="{{ old('name') }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text small">{{ __('The dimension name displayed to customers on product listings and details.') }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">{{ __('Initial Options') }}</label>
                            <input type="text" name="options" class="form-control @error('options') is-invalid @enderror" placeholder="e.g. 0.5kg, 1kg, 2kg" value="{{ old('options') }}">
                            @error('options')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text small">{{ __('Comma separated values to load initially.') }}</div>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill w-100 fw-semibold">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('Create Label') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Labels and Options List -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title h6 fw-bold mb-0">{{ __('Manage Labels & Options') }}</h3>
                </div>
                <div class="card-body p-0">
                    @if($labels->isEmpty())
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-tags fs-1 d-block mb-3 opacity-50"></i>
                            <p class="mb-0">{{ __('No variant labels defined yet. Create one on the left.') }}</p>
                        </div>
                    @else
                        <div class="accordion accordion-flush" id="labelsAccordion">
                            @foreach($labels as $label)
                                <div class="accordion-item border-bottom">
                                    <h2 class="accordion-header" id="heading-{{ $label->id }}">
                                        <button class="accordion-button collapsed px-4 py-3 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $label->id }}" aria-expanded="false" aria-controls="collapse-{{ $label->id }}">
                                            <i class="bi bi-tag-fill text-secondary me-2"></i>
                                            {{ $label->name }}
                                            <span class="badge rounded-pill text-bg-light ms-2">{{ $label->options->count() }} {{ __('options') }}</span>
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $label->id }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $label->id }}" data-bs-parent="#labelsAccordion">
                                        <div class="accordion-body px-4 py-3 bg-light-subtle">
                                            <!-- Rename / Delete Label Form -->
                                            <div class="d-flex align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
                                                <form method="post" action="{{ route('admin.variant-labels.update', $label) }}" class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 400px;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="text" name="name" class="form-control form-control-sm" required value="{{ $label->name }}" aria-label="{{ __('Label Name') }}">
                                                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3">{{ __('Rename') }}</button>
                                                </form>

                                                <form method="post" action="{{ route('admin.variant-labels.destroy', $label) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this label and all its options?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
                                                        <i class="bi bi-trash me-1"></i>{{ __('Delete Label') }}
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Add Option Form -->
                                            <div class="mb-4">
                                                <h4 class="h6 fw-bold mb-2">{{ __('Add Option Value') }}</h4>
                                                <form method="post" action="{{ route('admin.variant-labels.options.store', $label) }}" class="d-flex align-items-center gap-2" style="max-width: 400px;">
                                                    @csrf
                                                    <input type="text" name="value" class="form-control form-control-sm" required placeholder="e.g. 500 ml or 1 L" aria-label="{{ __('Option Value') }}">
                                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">{{ __('Add') }}</button>
                                                </form>
                                            </div>

                                            <!-- Options List -->
                                            <div>
                                                <h4 class="h6 fw-bold mb-2">{{ __('Configured Options') }}</h4>
                                                @if($label->options->isEmpty())
                                                    <p class="small text-muted mb-0">{{ __('No options configured yet. Add values above.') }}</p>
                                                @else
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($label->options as $opt)
                                                            <div class="badge text-bg-light border d-flex align-items-center gap-2 px-3 py-2 rounded-pill fs-7">
                                                                <span class="fw-semibold text-dark">{{ $opt->value }}</span>
                                                                <form method="post" action="{{ route('admin.variant-options.destroy', $opt) }}" class="d-inline" onsubmit="return confirm('{{ __('Delete option :val?', ['val' => $opt->value]) }}')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn-close p-0 m-0" style="font-size:0.65rem;" aria-label="{{ __('Remove') }}"></button>
                                                                </form>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
