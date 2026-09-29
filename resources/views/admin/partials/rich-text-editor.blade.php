@php
    $editorId = $editorId ?? 'richTextEditor';
    $editorName = $editorName ?? 'body';
    $editorValue = $editorValue ?? '';
    $editorHeight = (int) ($editorHeight ?? 480);
    $editorRequired = ! empty($editorRequired);
@endphp
<div class="admin-rich-editor-wrap">
    <textarea
        id="{{ $editorId }}"
        name="{{ $editorName }}"
        class="form-control admin-rich-editor-source @error($editorName) is-invalid @enderror"
        rows="14"
        @if($editorRequired) required @endif
    >{!! old($editorName, $editorValue) !!}</textarea>
    @error($editorName)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

@once
@push('styles')
<style>
.admin-rich-editor-wrap .tox-tinymce {
    border-radius: 0.5rem !important;
    border-color: var(--admin-border, #dee2e6) !important;
    overflow: hidden;
}
.admin-rich-editor-wrap .tox .tox-edit-area__iframe {
    background: #fff;
}
</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
@endpush
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById(@json($editorId));
    if (!el || typeof tinymce === 'undefined') return;

    tinymce.init({
        selector: '#' + @json($editorId),
        height: {{ $editorHeight }},
        menubar: false,
        statusbar: true,
        plugins: 'lists link autolink wordcount code table image',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | removeformat code',
        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
        content_style: 'body { font-family: "Plus Jakarta Sans", system-ui, sans-serif; font-size: 15px; line-height: 1.65; color: #334155; } h2,h3,h4 { color: #0f172a; margin-top: 1.25em; margin-bottom: 0.5em; } p { margin: 0 0 1em; } ul,ol { margin: 0 0 1em 1.25em; } a { color: #0d9488; } img { max-width: 100%; height: auto; border-radius: 8px; } blockquote { border-left: 4px solid #0d9488; margin: 1em 0; padding: 0.5em 1em; color: #64748b; }',
        link_default_target: '_blank',
        link_assume_external_targets: true,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        branding: false,
        promotion: false,
        paste_as_text: false,
        image_title: true,
        automatic_uploads: false,
        file_picker_types: 'image',
        setup: function (editor) {
            editor.on('change keyup', function () {
                editor.save();
            });
        }
    });

    var form = el.closest('form');
    if (form) {
        form.addEventListener('submit', function () {
            if (tinymce.get(@json($editorId))) {
                tinymce.get(@json($editorId)).save();
            }
        });
    }
});
</script>
@endpush
