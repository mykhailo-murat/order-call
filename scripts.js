/* global localize */

(function ($) {

    $(document).ready(function () {

        $('.order-call__form').submit(function (e) {
            e.preventDefault();

            var button = $('.order-call__form button');
            var name = $(".order-call__form-input[type=text]").val();
            var email = $(".order-call__form-input[type=email]").val();
            var phone = $(".order-call__form-input[type=tel]").val();
            var nonce =  $('.order-call__form').data('nonce');

            button.attr('disabled', 'disabled');
            var data = {
                action: 'send_form_ajax',
                name: name,
                email: email,
                phone: phone,
                nonce: nonce
            }

            $.ajax({
                type: 'POST',
                url: localize.ajax_url,
                data: data,
                success: (res) => {
                    $('.order-call__response').html(res.data);
                },
                error: function (request, status, error) {
                    $('.order-call__response').html(request.responseText);
                }
            });
        });

    });

})(jQuery);