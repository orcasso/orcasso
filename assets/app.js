import './styles/app.css';
import bsCustomFileInput from 'bs-custom-file-input'
import './jquery.collection.js'

import './summernote-0.9.1/summernote-bs4.min.css'
import './summernote-0.9.1/summernote-bs4.min.js'
import './summernote-0.9.1/lang/summernote-fr-FR.min.js'

window.initializeSelect2s = function () {
    $('.select2-ajax-loader').select2({
        ajax: {}
    });

    let select2Selects = $('.select2-select');
    if (select2Selects.length > 0) {
        select2Selects.each(function () {
            let initialized = $(this).data('select2');

            if (!initialized) {
                $(this).select2({
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });
            }
        });
    }

    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });
};

window.initializeCollections = function () {
    let jqCollections = $('.form-jq-collection');

    if (jqCollections.length > 0) {
        jqCollections.collection({
            add: '<a href="#" class="btn btn-success btn-sm btn-outline"><i class="fa fa-plus"></i></a>',
            remove: '<a href="#" class="btn btn-danger btn-sm btn-outline"><i class="fa fa-minus"></i></a>',
            min: 0,
            allow_up: false,
            allow_down: false,
            allow_add: true,
            allow_remove: true,
            allow_duplicate: false,

            after_add: function (collection, element) {
                if (element.hasClass('collection-select2-form-group')) {
                    setTimeout(function () {
                        window.initializeSelect2s();
                    }, 100);
                }
                return true;
            }
        });
    }
};

window.initializeSummernote = function () {
    let jqSummernote = $('textarea.summernote');

    if (jqSummernote.length > 0) {
        jqSummernote.summernote({
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                ['para', ['ul', 'ol']],
                ['insert', ['picture', 'link']]
            ],
            styleTags: ['blockquote'],
            callbacks: {
                onPaste(e) {
                    const bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                    e.preventDefault();
                    document.execCommand('insertText', false, bufferText);
                }
            },
            lang: 'fr-FR'
        });
    }
};

$(document).ready(function () {
    $.fn.select2.defaults.set('theme', 'bootstrap4');
    window.initializeSelect2s();
    window.initializeCollections();
    window.initializeSummernote();
    bsCustomFileInput.init()
});
