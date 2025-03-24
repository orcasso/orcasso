import './styles/app.css';

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


$(document).ready(function () {
    $.fn.select2.defaults.set('theme', 'bootstrap4');
    window.initializeSelect2s();
});
