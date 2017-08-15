/**
 * Created with JetBrains PhpStorm.
 * User: jonathan
 * Date: 5/25/12
 * Time: 4:07 PM
 * To change this template use File | Settings | File Templates.
 */

jQuery(document).ready(function ($) {

    $('select[name=mailjet_initial_sync_list_id]').ready(function (e) {
        $('select[name=mailjet_initial_sync_list_id]').val('');
    });

    removeSpaces = function ($el) {
        $el.val($el.val().split(' ').join(''));
    };

    $('#mailjet_username').on('blur', function () {
        removeSpaces($(this));
    });
    $('#mailjet_password').on('blur', function () {
        removeSpaces($(this));
    });

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
    $('.newPropertyBtn').on('click', function () {
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
    
    // init tabs with translations
    initTabsWithTranslations();
    // hide content of disabled tabs
    hideTabActivatorSiblings();
    // change Step3 content depending on language activations on Step2
    chamgeStep3DependingOnStep2ActiveTabs();
    $(document).on('focus', ':input', function () {
        $(this).attr('autocomplete', 'off');
    });

    // Validates current user inputs
    validateState();
    
    if (typeof $(document).tooltip === 'function') {
        $('.mj-tab-content').tooltip();
    }
   
    // on add widget to widget area
    $(document).on('widget-added', function () {
        initAccordion();
        initStringTranslations();
        initNewPropertyForm();
        initSortable();
        // hide content of disabled tabs
        hideTabActivatorSiblings();
        // init tabs with translations
        initTabsWithTranslations();
        // change Step3 content depending on language activations on Step2
        chamgeStep3DependingOnStep2ActiveTabs();
        $(".mj-accordion").accordion('enable');
        // Validates current user inputs
        validateState();        
    });

    // on widget save
    $(document).on('widget-updated', function (e, widget) {
        $('.mj-tab-content').tooltip();
        initAccordion();
        initStringTranslations();
        initNewPropertyForm();
        initSortable();
        // init tabs with translations
        initTabsWithTranslations();
        // hide content of disabled tabs
        hideTabActivatorSiblings();
        // change Step3 content depending on language activations on Step2
        chamgeStep3DependingOnStep2ActiveTabs();
        $('.mj-accordion').accordion({
            active: false,
            collapsible: true,
            heightStyle: 'content'
        });
        $('.mj-accordion').accordion('enable');
        registerAccordionNavButtons();
        // Validates current user inputs
        validateState();        
    });


});

// init tabs with translations
function initTabsWithTranslations() 
{
    jQuery(".mj-tabs-menu a").click(function(event) {
        event.preventDefault();
        var className = jQuery(this).attr("href");
        jQuery(className).each(function(){
            jQuery(this).parent().parent().find('li a[href="' + className + '"]').parent()
                .addClass("current").siblings().removeClass("current");
        });
        jQuery(".mj-tab-content").not(className).css("display", "none");
        jQuery(className).fadeIn();
    });    
}



function hideTabActivatorSiblings()
{
    jQuery('.tabActivator').each(function(){
        if (typeof jQuery(this).find('input').attr('checked') !== 'undefined') {
            jQuery(this).siblings().show();
        } else {
            jQuery(this).siblings().hide();
        }
    });
    
    jQuery('.tabActivator').on('click', function(){
        if (typeof jQuery(this).find('input').attr('checked') !== 'undefined') {
            jQuery(this).siblings().show();
        } else {
            jQuery(this).siblings().hide();
        }
        validateState();
    });
    
}



function chamgeStep3DependingOnStep2ActiveTabs()
{
    jQuery('.tabActivator').each(function(){
        if (typeof jQuery(this).attr('id') !== 'undefined') {
            var arr = jQuery(this).attr('id').split('-');
            var lang = arr[4];
            var widgetId = arr[2];
            var widgetName = arr[0] + '-' + arr[1] + '-' + arr[2];
            
            if (typeof jQuery(this).find('input').attr('checked') !== 'undefined') {
                jQuery('a#' + widgetName + '-tabLinkStep3-' + lang).show();
                jQuery('#' + widgetName + '-mj-string-translations-' + lang).show();
                jQuery('a#' + widgetName + '-tabLinkStep3-' + lang).parent().find('div').hide();
            } else {
                jQuery('a#' + widgetName + '-tabLinkStep3-' + lang).hide();
                jQuery('#' + widgetName + '-mj-string-translations-' + lang).hide();
                jQuery('a#' + widgetName + '-tabLinkStep3-' + lang).parent().find('div').show();
            }
        } 
    });
    
    jQuery('.tabActivator').on('click', function(){
        if (typeof jQuery(this).attr('id') !== 'undefined') {
            var arr = jQuery(this).attr('id').split('-');
            var lang = arr[4];
            var widgetId = arr[2];
            var widgetName = arr[0] + '-' + arr[1] + '-' + arr[2];
            
            if (typeof jQuery(this).find('input').attr('checked') !== 'undefined') {
                jQuery('a#' + widgetName + '-tabLinkStep3-' + lang).show();
                jQuery('#' + widgetName + '-mj-string-translations-' + lang).show();
                jQuery('a#' + widgetName + '-tabLinkStep3-' + lang).parent().find('div').hide();
            } else {
                jQuery('a#' + widgetName + '-tabLinkStep3-' + lang).hide();
                jQuery('#' + widgetName + '-mj-string-translations-' + lang).hide();
                jQuery('a#' + widgetName + '-tabLinkStep3-' + lang).parent().find('div').show();
            }
        } 
    });
}


function registerAccordionNavButtons() {
    jQuery('.mj-accordion .next, .mj-accordion .previous').unbind('click').on('click', (function (e) {
        e.preventDefault();
        jQuery(e.currentTarget).closest('.mj-accordion').accordion(
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
                var children = jQuery(this).children();
                var sortable = jQuery(children.context);
                var newElements = jQuery(sortable[sortable.length - 1]);
                if (newElements.hasClass('sortable2')) {
                    hideMetaInputFields(ui);
                    jQuery.each(newElements[0].children, function (k, v) {
                        showMetaInputFieldsOnUpdate(k, v);
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

        var arr = jQuery(v.firstChild).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().attr('id');
        if (typeof arr === 'undefined') {
            return;
        }
        arr = arr.split('-');
        var widgetId = arr[1];

        var langs = ['en', 'fr', 'de', 'es'];

        jQuery.each(langs, function (kk, language) {
            for (var i = 1; i <= 3; i++) {
                if (typeof mjGlobalVars !== 'undefined' 
                        && typeof mjGlobalVars[widgetId] !== 'undefined' 
                        && typeof mjGlobalVars[widgetId]['metaPropertyName' + i + language] !== 'undefined' 
                        && mjGlobalVars[widgetId]['metaProperty' + i + language] !== '' 
                        && mjGlobalVars[widgetId]['metaPropertyName' + i + language] === metaProperty.text().trim()) { 
                    inputDiv.find('input[type="text"]').val(mjGlobalVars[widgetId]['metaProperty' + i + language]);
                    return;
                } else {
                    //inputDiv.find('input[type="text"]').val('');
                }
            }
        });
    }
}



function showMetaInputFieldsOnUpdate(k, v) {
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

        var arr = jQuery(v.firstChild).parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().parent().attr('id');
        if (typeof arr === 'undefined') {
            return;
        }
        arr = arr.split('-');
        var widgetId = arr[1];

        //var widgetId = metaProperty.closest(".widget").find(".widget_number").val();
        
        inputDiv.find('input[type="hidden"]').each(function(){
            var arrHiddenInput = jQuery(this).attr('id').split('-');
            var widgetId = arrHiddenInput[2];
            var widgetName = arrHiddenInput[0] + '-' + arrHiddenInput[1] + '-' + arrHiddenInput[2];
            var arrHiddenInputName = arrHiddenInput[3].split('metaPropertyName');
                    
            var lang = arrHiddenInputName[1].substring(1);
            if (typeof mjGlobalVarsProps !== 'undefined' 
                && typeof mjGlobalVarsProps[widgetId] !== 'undefined' 
                && typeof mjGlobalVarsProps[widgetId][metaProperty.text().trim() + lang] !== 'undefined' 
                && mjGlobalVarsProps[widgetId][metaProperty.text().trim() + lang] !== '') { 
                jQuery('#' + widgetName + '-metaProperty' + arrHiddenInputName[1]).val(mjGlobalVarsProps[widgetId][metaProperty.text().trim() + lang]);
            } else {
                jQuery('#' + widgetName + '-metaProperty' + arrHiddenInputName[1]).val('');
            }
        });
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

function checkRequiredField(o) {
    if(Boolean(jQuery(o).val())){
        jQuery(o).removeClass('borderRed');
        jQuery('.mj-accordion .next, .mj-accordion .previous, input[type="submit"]').each(function () {
            jQuery(this).prop('disabled', false);
        });
    } else {
        jQuery(o).addClass('borderRed');
        jQuery('.mj-accordion .next, .mj-accordion .previous, input[type="submit"]').each(function () {
            jQuery(this).prop('disabled', true);
        });
    }
    // check for empty siblings input fields; check each input field for each activated language tab
    jQuery(o).closest('.mj-tabs-container').find('.mj-tab-content').each(function(){
        if(jQuery(this).find('.tabActivator input[type="checkbox"]').prop('checked') === true){
            jQuery(this).find('input:enabled[type="text"], input:enabled[type="select"]').each(function(){
                if(!Boolean(jQuery(this).val())){
                    jQuery(this).addClass('borderRed');
                    jQuery('.mj-accordion .next, .mj-accordion .previous, input[type="submit"]').each(function () {
                        jQuery(this).prop('disabled', true);
                    });
                    //return false;
                }
            });
        }
    });
    //return true;
}
function initAccordion() {
    if (jQuery(".mj-accordion").accordion !== undefined) {
        jQuery(".mj-accordion").accordion({
            disabled: true,
            heightStyle: "content"
        });
        jQuery('.mj-accordion .next, .mj-accordion .previous').click(function (e) {
            e.preventDefault();
            validateState();
            jQuery(e.currentTarget).closest('.mj-accordion').accordion(
                'option',
                'active',
                (jQuery(e.currentTarget).closest('.mj-accordion').accordion('option', 'active') +
                    (jQuery(this).is('.next') ? 1 : -1))
            );
        });
    }
}


function validateState()
{
    jQuery('.widget.open .mj-tab input[type="text"]:enabled, .widget.open .mj-tab select').each(function () {
        checkRequiredField(this);
    });
    jQuery('.widget.open .mj-tab input[type="text"]:enabled, .widget.open .mj-tab select').unbind('keyup').on('keyup', function (e) {
        e.preventDefault();
        checkRequiredField(this);
    });
     
}