import './styles/app.css';
import bsCustomFileInput from 'bs-custom-file-input'
import './jquery.collection.js'

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

$(document).ready(function () {
    $.fn.select2.defaults.set('theme', 'bootstrap4');
    window.initializeSelect2s();
    window.initializeCollections();
    bsCustomFileInput.init()
});
