jQuery(document).ready(function () {
    jQuery('.simpleTabs-content').hide();
    jQuery("#simpleTabs-content-1").show();

    jQuery(".nav-tab").click(function (event) {
        jQuery(".nav-tab").removeClass('nav-tab-active');
        jQuery(event.currentTarget).addClass('nav-tab-active');
        jQuery('.simpleTabs-content').hide();
        var tab = jQuery(event.currentTarget).data('tab');
        jQuery("#simpleTabs-content-" + tab).show();
    });

    // Run initial style and icon set previews
    changeStyle('imageTemplatePreview', 'postie-settings-imagetemplate', 'imagetemplateselect', 'postie-settings-selected_imagetemplate', 'smiling.jpg', false);
    changeStyle('audioTemplatePreview', 'postie-settings-audiotemplate', 'audiotemplateselect', 'postie-settings-selected_audiotemplate', 'funky.mp3', false);
    changeStyle('video1TemplatePreview', 'postie-settings-video1template', 'video1templateselect', 'postie-settings-selected_video1template', 'hi.mp4', false);
    changeStyle('video2TemplatePreview', 'postie-settings-video2template', 'video2templateselect', 'postie-settings-selected_video2template', 'hi.flv', false);
    
    var iconSelect = document.getElementById('icon_set_select');
    if (iconSelect) {
        changeIconSet(iconSelect);
    }
});

function changeIconSet(selectBox, size) {
    var iconSet = document.getElementById('postie-settings-icon_set');
    var iconSize = document.getElementById('postie-settings-icon_size');
    var preview = document.getElementById('postie-settings-attachment_preview');
    var iconDir = postieAdminData.iconDir + '/';
    if (size == true) {
        var hiddenInput = iconSize;
    } else {
        var hiddenInput = iconSet;
    }
    for (var i = 0; i < selectBox.options.length; i++) {
        if (selectBox.options[i].selected == true) {
            hiddenInput.value = selectBox.options[i].value;
        }
    }
    var fileTypes = new Array('doc', 'pdf', 'xls', 'default');
    if (preview) {
        preview.innerHTML = '';
        for (var j = 0; j < fileTypes.length; j++) {
            preview.innerHTML += "<img src='" + iconDir + iconSet.value + '/' +
                    fileTypes[j] + '-' + iconSize.value + ".png' />";
        }
    }
}

function changeStyle(previewId, template, select, selected, sample, custom) {
    var preview = document.getElementById(previewId);
    var pageStyles = document.getElementById(select);
    var selectedStyle;
    var hiddenStyle = document.getElementById(selected);
    var pageStyle = document.getElementById(template);
    if (!preview || !pageStyles || !hiddenStyle || !pageStyle) {
        return;
    }
    if (custom == true) {
        selectedStyle = pageStyles.options[pageStyles.options.length - 1];
        selectedStyle.value = pageStyle.value;
        selectedStyle.selected = true;
    } else {
        for (var i = 0; i < pageStyles.options.length; i++) {
            if (pageStyles.options[i].selected == true) {
                selectedStyle = pageStyles.options[i];
            }
        }
    }
    hiddenStyle.value = selectedStyle.innerHTML;
    var previewHTML = selectedStyle.value;
    var fileLink = postieAdminData.templateDir + '/' + sample;
    var thumb = postieAdminData.templateDir + '/' + sample.replace(/\.jpg/, '-150x150.jpg');
    var medium = postieAdminData.templateDir + '/' + sample.replace(/\.jpg/, '-300x200.jpg');
    var large = postieAdminData.templateDir + '/' + sample.replace(/\.jpg/, '-1024x682.jpg');
    var pagelink = postieAdminData.siteUrl + '/?attachment_id=9999';
    var fileType = 'mp4';
    previewHTML = previewHTML.replace(/{FILELINK}/g, fileLink);
    previewHTML = previewHTML.replace(/{FULL}/g, fileLink);
    previewHTML = previewHTML.replace(/{IMAGE}/g, fileLink);
    previewHTML = previewHTML.replace(/{FILENAME}/, sample);
    previewHTML = previewHTML.replace(/{FILETYPE}/, fileType);
    previewHTML = previewHTML.replace(/{PAGELINK}/, pagelink);
    previewHTML = previewHTML.replace(/{RELFILENAME}/, sample);
    previewHTML = previewHTML.replace(/{THUMB(NAIL|)}/, thumb);
    previewHTML = previewHTML.replace(/{MEDIUM}/, medium);
    previewHTML = previewHTML.replace(/{LARGE}/, large);
    previewHTML = previewHTML.replace(/{HEIGHT}/, 800);
    previewHTML = previewHTML.replace(/{WIDTH}/, 1200);
    previewHTML = previewHTML.replace(/{THUMBWIDTH}/, 150);
    previewHTML = previewHTML.replace(/{THUMBHEIGHT}/, 150);
    previewHTML = previewHTML.replace(/{MEDIUMWIDTH}/, 300);
    previewHTML = previewHTML.replace(/{MEDIUMHEIGHT}/, 200);
    previewHTML = previewHTML.replace(/{LARGEWIDTH}/, 1024);
    previewHTML = previewHTML.replace(/{LARGEHEIGHT}/, 682);
    previewHTML = previewHTML.replace(/{ID}/, 9999);
    previewHTML = previewHTML.replace(/{FILEID}/, 9999);
    previewHTML = previewHTML.replace(/{POSTTITLE}/g, 'Post title');
    previewHTML = previewHTML.replace(/{CAPTION}/g, 'Spencer smiling');
    preview.innerHTML = previewHTML;
    pageStyle.value = selectedStyle.value;
}

function showAdvanced(advancedId, arrowId) {
    var advanced = document.getElementById(advancedId);
    var arrow = document.getElementById(arrowId);
    if (!advanced || !arrow) {
        return;
    }
    if (advanced.style.display == 'none') {
        advanced.style.display = 'block';
        arrow.innerHTML = '&#9660;';
    } else {
        advanced.style.display = 'none';
        arrow.innerHTML = '&#9654;';
    }
}
