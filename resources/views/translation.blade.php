<script src="//code.jquery.com/jquery-latest.js"></script>
<script>var $jqueryLatest = jQuery.noConflict(true);</script>

@include('inline-translation::featherlight')

<link href="//cdn.rawgit.com/noelboss/featherlight/1.7.13/release/featherlight.min.css" type="text/css" rel="stylesheet" />

<style>
    .featherlight .featherlight-content {
        min-width: 80%;
    }
    *[data-translate] {
        border: 1px solid red !important;
        cursor: pointer !important;
    }
    #beyondcode-translate-button {
        padding: 2px;
        background: white;
        border: 1px solid #333;
    }
</style>
<script>
    $jqueryLatest(function ($) {

        function createTrigger(el) {
            var trigger = $('<span id="beyondcode-translate-button" class="translate-button">'+ {!! json_encode(trans("laravel-inline-translation::form.translate")) !!} +'</span>');

            return $(trigger)
                .css({
                    position: 'absolute',
                    cursor: 'pointer',
                    display: 'none',
                    'z-index': 2000
                })
                .click(function() {
                    openModal($(this).data('translate'));
                })
                .appendTo(el);
        }

        function showTranslateIcon(el) {
            var offset = el.offset();

            $('#beyondcode-translate-button').css({
                top: offset.top + el.outerHeight() + -3,
                left: offset.left
            })
                .data('translate', el.data('translate'))
                .show();
        }

        function openModal(translateData) {
            var form = $('<div/>');
            var table = $('<table id="beyondcode-translation-table" width="100%" border="1">');
            table.append(`
                <tr>
                    <th>Key</th>
                    <th>{!! (trans("laravel-inline-translation::form.value")) !!}</th>
                </tr>
            `);
            translateData.forEach(function (data) {
                var tr = $('<tr>')
                    .append('<td>'+ {!! json_encode(trans("laravel-inline-translation::form.translated")) !!} +'</td>')
                    .append(
                        $('<td>')
                            .append(
                                $('<input type="text">')
                                    .attr('name', data.original)
                                    .val(data.translated)
                                    .css('width', '100%')
                            )

                    );
                table.append(tr);

                table.append(`
                    <tr>
                        <td>{!! (trans("laravel-inline-translation::form.original")) !!}</td>
                        <td>${data.original}</td>
                    </tr>
                    <tr>
                        <td>{!! (trans("laravel-inline-translation::form.location")) !!}</td>
                        <td>${data.location}</td>
                    </tr>
                    <tr>
                        <td>{!! (trans("laravel-inline-translation::form.parameters")) !!}</td>
                        <td><pre>${JSON.stringify(data.parameters, null, 2)}</pre></td>
                    </tr>
                `);
            });

            form.append(table);
            var btn = $('<button type="submit"> '+ {!! json_encode(trans("laravel-inline-translation::form.save")) !!} +' </button>')
                .click(function (){
                    translateData.forEach(function (data) {
                        var name = data.original;
                        var value = $(`#beyondcode-translation-table input[name="${data.original}"]`).val();

                        $.post('/_beyondcode/translation', {
                            key: name,
                            value: value,
                            _token: '<?= csrf_token() ?>'
                        });
                    });
                    $.featherlight.current().close();
                });

            form.append(btn);

            $.featherlight(form);
        }

        createTrigger($('body'));

        $(document).on('mousemove', '[data-translate]', function (e) {
            showTranslateIcon($(e.target));
        });
        
        $(document).on('mouseleave', '#beyondcode-translate-button', function (e) {
            $('#beyondcode-translate-button').hide()
        });
    });
</script>
