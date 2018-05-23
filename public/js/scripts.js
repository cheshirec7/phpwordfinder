$(document).ready(function () {

    let $loading = $('.loader'),
        $spinner = $('.ajax-spinner'),
        $div = $('div', '.wordcontainer').click(function () {
            let $this = $(this),
                k = $this.attr('data-k'),
                word = $this.html();

            $.ajax({
                type: 'POST',
                url: 'updateword',
                data: {
                    word: word,
                    k: 1 - k
                },
                success: function (data) {
                    $this.attr('data-k', 1 - k);
                    // console.log(data);
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });
        });

    $(document).ajaxStart(function () {
        $loading.show();
    }).ajaxError(function (event, jqxhr, settings, thrownError) {
        $loading.hide();
        // alert(thrownError);
        location.reload();
    }).ajaxStop(function () {
        $loading.hide();
    });
});