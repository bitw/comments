(function ($) {
    var
        defaults = {
            className: 'autosizejs',
            append: '',
            callback: false,
            resizeDelay: 10
        },

        // border:0 is unnecessary, but avoids a bug in Firefox on OSX
        copy = '<textarea tabindex="-1" style="position:absolute; top:-999px; left:0; right:auto; bottom:auto; border:0; padding: 0; -moz-box-sizing:content-box; -webkit-box-sizing:content-box; box-sizing:content-box; word-wrap:break-word; height:0 !important; min-height:0 !important; overflow:hidden; transition:none; -webkit-transition:none; -moz-transition:none;"/>',

        // line-height is conditionally included because IE7/IE8/old Opera do not return the correct value.
        typographyStyles = [
            'fontFamily',
            'fontSize',
            'fontWeight',
            'fontStyle',
            'letterSpacing',
            'textTransform',
            'wordSpacing',
            'textIndent'
        ],

        // to keep track which textarea is being mirrored when adjust() is called.
        mirrored,

        // the mirror element, which is used to calculate what size the mirrored element should be.
        mirror = $(copy).data('autosize', true)[0];

    // test that line-height can be accurately copied.
    mirror.style.lineHeight = '99px';
    if ($(mirror).css('lineHeight') === '99px') {
        typographyStyles.push('lineHeight');
    }
    mirror.style.lineHeight = '';

    $.fn.autosize = function (options) {
        if (!this.length) {
            return this;
        }

        options = $.extend({}, defaults, options || {});

        if (mirror.parentNode !== document.body) {
            $(document.body).append(mirror);
        }

        return this.each(function () {
            var
                ta = this,
                $ta = $(ta),
                maxHeight,
                minHeight,
                boxOffset = 0,
                callback = $.isFunction(options.callback),
                originalStyles = {
                    height: ta.style.height,
                    overflow: ta.style.overflow,
                    overflowY: ta.style.overflowY,
                    wordWrap: ta.style.wordWrap,
                    resize: ta.style.resize
                },
                timeout,
                width = $ta.width();

            if ($ta.data('autosize')) {
                // exit if autosize has already been applied, or if the textarea is the mirror element.
                return;
            }
            $ta.data('autosize', true);

            if ($ta.css('box-sizing') === 'border-box' || $ta.css('-moz-box-sizing') === 'border-box' || $ta.css('-webkit-box-sizing') === 'border-box') {
                boxOffset = $ta.outerHeight() - $ta.height();
            }

            // IE8 and lower return 'auto', which parses to NaN, if no min-height is set.
            minHeight = Math.max(parseInt($ta.css('minHeight'), 10) - boxOffset || 0, $ta.height());

            $ta.css({
                overflow: 'hidden',
                overflowY: 'hidden',
                wordWrap: 'break-word', // horizontal overflow is hidden, so break-word is necessary for handling words longer than the textarea width
                resize: ($ta.css('resize') === 'none' || $ta.css('resize') === 'vertical') ? 'none' : 'horizontal'
            });

            // The mirror width must exactly match the textarea width, so using getBoundingClientRect because it doesn't round the sub-pixel value.
            function setWidth() {
                var style, width;

                if ('getComputedStyle' in window) {
                    style = window.getComputedStyle(ta, null);
                    width = ta.getBoundingClientRect().width;

                    $.each(['paddingLeft', 'paddingRight', 'borderLeftWidth', 'borderRightWidth'], function (i, val) {
                        width -= parseInt(style[val], 10);
                    });

                    mirror.style.width = width + 'px';
                }
                else {
                    // window.getComputedStyle, getBoundingClientRect returning a width are unsupported and unneeded in IE8 and lower.
                    mirror.style.width = Math.max($ta.width(), 0) + 'px';
                }
            }

            function initMirror() {
                var styles = {};

                mirrored = ta;
                mirror.className = options.className;
                maxHeight = parseInt($ta.css('maxHeight'), 10);

                // mirror is a duplicate textarea located off-screen that
                // is automatically updated to contain the same text as the
                // original textarea.  mirror always has a height of 0.
                // This gives a cross-browser supported way getting the actual
                // height of the text, through the scrollTop property.
                $.each(typographyStyles, function (i, val) {
                    styles[val] = $ta.css(val);
                });
                $(mirror).css(styles);

                setWidth();

                // Chrome-specific fix:
                // When the textarea y-overflow is hidden, Chrome doesn't reflow the text to account for the space
                // made available by removing the scrollbar. This workaround triggers the reflow for Chrome.
                if (window.chrome) {
                    var width = ta.style.width;
                    ta.style.width = '0px';
                    var ignore = ta.offsetWidth;
                    ta.style.width = width;
                }
            }

            // Using mainly bare JS in this function because it is going
            // to fire very often while typing, and needs to very efficient.
            function adjust() {
                var height, original;

                if (mirrored !== ta) {
                    initMirror();
                } else {
                    setWidth();
                }

                mirror.value = ta.value + options.append;
                mirror.style.overflowY = ta.style.overflowY;
                original = parseInt(ta.style.height, 10);

                // Setting scrollTop to zero is needed in IE8 and lower for the next step to be accurately applied
                mirror.scrollTop = 0;

                mirror.scrollTop = 9e4;

                // Using scrollTop rather than scrollHeight because scrollHeight is non-standard and includes padding.
                height = mirror.scrollTop;

                if (maxHeight && height > maxHeight) {
                    ta.style.overflowY = 'scroll';
                    height = maxHeight;
                } else {
                    ta.style.overflowY = 'hidden';
                    if (height < minHeight) {
                        height = minHeight;
                    }
                }

                height += boxOffset;

                if (original !== height) {
                    ta.style.height = height + 'px';
                    if (callback) {
                        options.callback.call(ta, ta);
                    }
                }
            }

            function resize() {
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    var newWidth = $ta.width();

                    if (newWidth !== width) {
                        width = newWidth;
                        adjust();
                    }
                }, parseInt(options.resizeDelay, 10));
            }

            if ('onpropertychange' in ta) {
                if ('oninput' in ta) {
                    // Detects IE9.  IE9 does not fire onpropertychange or oninput for deletions,
                    // so binding to onkeyup to catch most of those occasions.  There is no way that I
                    // know of to detect something like 'cut' in IE9.
                    $ta.on('input.autosize keyup.autosize', adjust);
                } else {
                    // IE7 / IE8
                    $ta.on('propertychange.autosize', function () {
                        if (event.propertyName === 'value') {
                            adjust();
                        }
                    });
                }
            } else {
                // Modern Browsers
                $ta.on('input.autosize', adjust);
            }

            // Set options.resizeDelay to false if using fixed-width textarea elements.
            // Uses a timeout and width check to reduce the amount of times adjust needs to be called after window resize.

            if (options.resizeDelay !== false) {
                $(window).on('resize.autosize', resize);
            }

            // Event for manual triggering if needed.
            // Should only be needed when the value of the textarea is changed through JavaScript rather than user input.
            $ta.on('autosize.resize', adjust);

            // Event for manual triggering that also forces the styles to update as well.
            // Should only be needed if one of typography styles of the textarea change, and the textarea is already the target of the adjust method.
            $ta.on('autosize.resizeIncludeStyle', function () {
                mirrored = null;
                adjust();
            });

            $ta.on('autosize.destroy', function () {
                mirrored = null;
                clearTimeout(timeout);
                $(window).off('resize', resize);
                $ta
                    .off('autosize')
                    .off('.autosize')
                    .css(originalStyles)
                    .removeData('autosize');
            });

            // Call adjust in case the textarea already contains text.
            adjust();
        });
    };
}(window.jQuery || window.$)); // jQuery or jQuery-like library, such as Zepto
window.setReply = function(reply_for){
    $('#js-comments-reply_for_id').val(reply_for.id);
    $('#js-comments-reply_for_name').text(reply_for.name);
    $('#js-comments-reply').removeAttr('hidden');
};
window.forgetReply = function(){
    $('#js-comments-reply_for_id').val('');
    $('#js-comments-reply_for_name').text('');
    $('#js-comments-reply').attr('hidden', true);
};
$(function () {
    var page_id = $('#comments-form').data('page_id');
    $('#comments-text').val(localStorage.getItem(page_id + '.comments'));
    $('#comments-text').autosize();
    $('#comments-text').on('keypress', function (ev) {
        localStorage.setItem(page_id + '.comments', $(this).val());
        if (ev.ctrlKey && ev.which === 13) {
            var $form = $('#comments-form');
            var $name = $form.find('#comments-name');
            var $email = $form.find('#comments-email');
            var $text = $form.find('#comments-text');
            var $reply_for = $form.find('#comments-reply_for');
            var form_data = $form.serializeArray();

            if (!$text.val().trim().length) {
                return;
            }

            $text.attr('disabled', true);
            $name.removeClass('is-invalid');
            $email.removeClass('is-invalid');

            $.post($form.attr('action'), form_data)
                .done(function (response) {
                    localStorage.setItem(page_id + '.comments', '');
                    $text.val('');
                    $reply_for.val('');
                    console.log(
                        $form.find('input#js-comments-reply_for_id').val(),
                        response.comment.parent_id
                    );
                    if ($form.find('input#js-comments-reply_for_id').val() == response.comment.parent_id) {
                        // insert as child comment
                        $('li.js-comments-node[data-id=' + response.comment.parent_id + ']').last().append('<ul>'+response.html+'</ul>');
                    }else if($form.find('input#js-comments-reply_for_id').val() > 0){
                        // insert as child sub comment
                        $('li.js-comments-node[data-id=' + response.comment.parent_id + ']').last().find('div').last().append('<ul>'+response.html+'</ul>');
                    } else {
                        // insert as last root comment
                        $('.js-comments-node').last().append();
                        $('#js-comments-tree').append(response.html);
                    }
                })
                .fail(function (response) {
                    if (response.responseJSON.errors.name && !$name.hasClass('is-invalid')) {
                        $name.addClass('is-invalid')
                    }
                    if (response.responseJSON.errors.email && !$email.hasClass('is-invalid')) {
                        $email.addClass('is-invalid')
                    }
                })
                .always(function () {
                    $text.removeAttr('disabled');
                });
        }
    });

    $(document).on('click', '.js-comments-reply', function(){
        var reply_for = {
            id: $(this).closest('li.js-comments-node').data('id'),
            name: $(this).closest('li.js-comments-node').find('.js-comments-author_name').first().text()
        };
        setReply(reply_for);
    });

    $(document).on('click', '#js-comments-reply-cancel', function(){
        forgetReply();
    });
});
