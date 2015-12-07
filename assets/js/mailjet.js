/**
 * Created with JetBrains PhpStorm.
 * User: jonathan
 * Date: 5/25/12
 * Time: 4:07 PM
 * To change this template use File | Settings | File Templates.
 */

jQuery(document).ready(function ($) {
    showPorts = function ($el) {
        $('#mailjet_port').empty()

        if ($el.attr('checked') == 'checked') {
            $('#mailjet_port').append('<option value="465">465</option>');
        } else {
            $('#mailjet_port')
                .append('<option value="25">25</option>')
                .append('<option value="587">587</option>')
                .append('<option value="588">588</option>')
                .append('<option value="80">80</option>');
        }
    };

    $('#addContact').on('click', function (e) {
        e.preventDefault();
        var contactInput = $('#firstContactAdded').clone();
        var $el = $(e.currentTarget);
        $el.before(contactInput);
    });

    $('select[name=action2]').change(function (e) {
        $('select[name=action]').val($(this).val());
    });

    $('#mailjet_ssl').change(function (e) {
        showPorts($(this));
    });

    showPorts($('#mailjet_ssl'));
    $('.newPropertyBtn').live('click', function () {
        $('.newPropertyForm').toggle();
    });

    $('.response').append($('.error:first, .success:first')[0]);
    // code to remove double widget message on front end
    var hidden = 0;
    $('div.response > p').each(function(i, o){
        if ($(o).attr('listid') !== $(o).parent().parent().find('form input[name="list_id"]').val()) {
            $(o).hide();
            hidden++;
        }
    });
    // if all are hidden, show at least one
    if ($('div.response > p:visible').length === 0) {
        $('div.response > p:first').show();
    }

    initAccordion();
    initStringTranslations();
    initNewPropertyForm();
    initSortable();

    $(document).on('focus', ':input', function () {
        $(this).attr('autocomplete', 'off');
    });

    if (typeof $(document).tooltip === 'function') {
        $(document).tooltip();
    }
    // TODO Uncomment the code bellow for the next release for the tabs to function properly
    // catch next button click and check for correct number of properties and create a form with input text fields and meta properties as labels
    /*$(".tabs-menu a").click(function (event) {
     event.preventDefault();
     $(this).parent().addClass("current");
     $(this).parent().siblings().removeClass("current");
     var tab = $(this).attr("href");
     $(".tabcontent").not(tab).css("display", "none");
     $(tab).fadeIn();
     });*/

    // on add widget to widget area
    $(document).on('widget-added', function () {
        initAccordion();
        initStringTranslations();
        initNewPropertyForm();
        initSortable();
        $(".accordion").accordion('enable');
    });

    // on widget save
    $(document).on('widget-updated', function (e, widget) {
        $(document).tooltip();
        initStringTranslations();
        initNewPropertyForm();
        initSortable();
        $('.accordion').accordion({
            active: false,
            collapsible: true,
            heightStyle: 'content'
        });
        $('.accordion').accordion('enable');
        registerAccordionNavButtons();
    });

});

function registerAccordionNavButtons() {
    jQuery('.accordion .next, .accordion .previous').unbind('click').live('click', (function (e) {
        e.preventDefault();
        jQuery('.widget.open .tab input[type="text"]:enabled, .widget.open .tab select').each(function () {
            if (checkRequiredFields(this) === false) {
                return false;
            }
        });
        jQuery('.widget.open .tab input[type="text"]:enabled, .widget.open .tab select').unbind('keyup')
            .on('keyup', function (e) {
                e.preventDefault();
                checkRequiredFields(this);
            });
        jQuery(e.currentTarget).closest('.accordion').accordion(
            'option',
            'active',
            Math.round((jQuery(this).is('.next') === true ? 1 : -1) +
                ((jQuery(this).parent().parent().parent().index() - 1) / 2))
        );
    }));
}

// http://api.jqueryui.com/sortable
function initSortable() {
    if (typeof jQuery(".sortable1, .sortable2").sortable === 'function') {
        jQuery(".sortable1, .sortable2").sortable({
            cancel: ".ui-state-disabled",
            items: "li:not(.ui-state-disabled)",
            connectWith: ".connectedSortable",
            // Triggered when the sortable is created.
            create: function (event, ui) {
                hideMetaInputFields(ui);
                var sortable2 = jQuery('.sortable2');
                jQuery.each(sortable2, function () {
                    jQuery.each(jQuery(this).find('li'), function (k, v) {
                        showMetaInputFields(k, v);
                    });
                })
            },
            // Triggered when an item from a connected sortable list has been dropped into another list. The latter is the event target.
            receive: function (event, ui) {
                var children = jQuery(this).children();
                if (jQuery(children.context).attr('class').search('sortable2') !== -1 && children.length > 4) {
                    alert('Please note that currently you may have a maximum of 3 contact properties in your widget');
                    jQuery(this).sortable('cancel');
                    jQuery(ui.sender).sortable('cancel');
                }
            },
            // Triggered when the user stopped sorting and the DOM position has changed.
            update: function (event, ui) {
                checkRequiredFields();
                var children = jQuery(this).children();
                var sortable = jQuery(children.context);
                var newElements = jQuery(sortable[sortable.length - 1]);
                if (newElements.hasClass('sortable2')) {
                    hideMetaInputFields(ui);
                    jQuery.each(newElements[0].children, function (k, v) {
                        showMetaInputFields(k, v);
                    });
                }
            }
        });
        jQuery("#sortable1 li, #sortable2 li").disableSelection();
    }
}

function hideMetaInputFields(ui) {
    var obj = jQuery.isEmptyObject(ui) ? jQuery('.map-meta-properties div') :
        jQuery(ui.item).parent().parent().parent().parent().parent().find('.map-meta-properties div');
    obj.each(function () {
        jQuery(this).hide().find('input[type!="select"]').prop('disabled', true);
    });
}

function showMetaInputFields(k, v) {
    if (k === 0) {
        return;
    }
    var metaProperty = jQuery(v);
    if (metaProperty.parent().hasClass('sortable2')) {
        var inputDiv = metaProperty.parent().parent().parent().parent().parent()
            .find('.map-meta-properties div:nth-child(' + k + ')');
        if (jQuery(inputDiv.context).attr('class') === 'ui-state-disabled') {
            return;
        }
        inputDiv.show();
        inputDiv.find('label').html(metaProperty.text().trim() + ':');
        inputDiv.find('input[type="hidden"]').val(metaProperty.text().trim());
        inputDiv.find('input').prop('disabled', false);

        var arr = jQuery(v.firstChild).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().attr('id').split('-'),
            widgetId = arr[2];

        for (var i = 1; i <= 3; i++) {
            if (typeof mjGlobalVars[widgetId]['metaPropertyName' + i] !== 'undefined' &&
                mjGlobalVars[widgetId]['metaPropertyName' + i] === metaProperty.text().trim()) {
                inputDiv.find('input[type="text"]').val(mjGlobalVars[widgetId]['metaProperty' + i]);
                return;
            } else {
                inputDiv.find('input[type="text"]').val('');
            }
        }
    }
}

function initNewPropertyForm() {
    jQuery('.new-meta-submit').on('click', function (e) {
        var name = jQuery(this).parent().parent().find('input[type="text"]');
        var dataType = jQuery(this).parent().parent().find('select');
        if (name.val() === '' || dataType.val() === '') {
            alert('Please enter Name and Contact property type');
            return false;
        } else {
            e.preventDefault();
            jQuery.ajax({
                dataType: "json",
                url: ajaxurl,
                data: {
                    'action': 'mailjet_subscribe_ajax_add_meta_property',
                    'name': name.val(),
                    'type': dataType.val()
                },
                success: function (data) {
                    jQuery('.new-meta-property-response').html(data.message).removeClass('green red').addClass(data.status === 'OK' ? 'green' : 'red');
                    if (data.status === 'OK') {
                        jQuery('div.noProperties').hide();
                        jQuery('div.yesProperties').show();
                        jQuery('.sortable').show();
                        jQuery('.sortable1').append('<li class="ui-state-default"><div class="cursorMoveImg"></div>&nbsp;&nbsp;&nbsp;&nbsp;' + name.val() + '</li>');
                    }
                    jQuery(name).val('').focus();
                },
                error: function (errorThrown) {
                    jQuery('.new-meta-property-response').html(errorThrown).removeClass('green red').addClass('red');
                }
            });
        }
    });
}

function initStringTranslations() {
    jQuery('.mj-string-translations').hide();
    jQuery('.mj-translations-title a').unbind('click').click(function () {
        jQuery('.mj-string-translations').toggle('slow');
    });
}

function checkRequiredFields(o) {
    if (jQuery(o).val() === '') {
        jQuery(o).addClass('borderRed');
        jQuery('.accordion .next,.accordion .previous, input[type="submit"]').each(function () {
            jQuery(this).prop('disabled', true);
        });
        jQuery('.widget-control-actions input[type="submit"]').hide();
        return false;
    } else {
        jQuery(o).removeClass('borderRed');
        jQuery('.accordion .next,.accordion .previous, input[type="submit"]').each(function () {
            jQuery(this).prop('disabled', false);
        });
    }
    jQuery('.widget-control-actions input[type="submit"]').show();
    return true;
}

function initAccordion() {
    if (jQuery(".accordion").accordion !== undefined) {
        jQuery(".accordion").accordion({
            disabled: true,
            heightStyle: "content"
        });
        jQuery('.accordion .next,.accordion .previous').click(function (e) {
            e.preventDefault();
            jQuery('.widget.open .tab input[type="text"]:enabled, .widget.open .tab select').each(function () {
                if (checkRequiredFields(this) === false) {
                    return false;
                }
            });
            jQuery('.widget.open .tab input[type="text"]:enabled, .widget.open .tab select').on('keyup', function (e) {
                e.preventDefault();
                checkRequiredFields(this);
            });
            var delta = (jQuery(this).is('.next') ? 1 : -1);
            jQuery(e.currentTarget).closest('.accordion').accordion(
                'option',
                'active',
                (jQuery(e.currentTarget).closest('.accordion').accordion('option', 'active') + delta)
            );
        });
    }
}
