function sanitizeTabKey(key) {
    if (!key || typeof key !== 'string') {
        return '';
    }
    return key.replace(/[^a-zA-Z0-9_-]/g, '');
}

if (typeof jQuery !== 'undefined') {
jQuery(document).ready(function () {
    function activateTab(tabKey) {
        tabKey = sanitizeTabKey(tabKey);
        if (!tabKey) {
            return;
        }

        var $targetContent = jQuery('.simpleTabs-content').filter(function () {
            var $el = jQuery(this);
            return $el.attr('data-tab-content') === tabKey ||
                   $el.attr('id') === 'postie-tab-content-' + tabKey ||
                   $el.attr('id') === 'simpleTabs-content-' + tabKey;
        });

        var $targetNav = jQuery('.nav-tab').filter(function () {
            var $el = jQuery(this);
            return $el.attr('data-tab') === tabKey ||
                   $el.attr('id') === 'simpleTabs-nav-' + tabKey;
        });

        if ($targetContent.length) {
            jQuery('.simpleTabs-content').hide();
            jQuery('.nav-tab').removeClass('nav-tab-active');
            $targetContent.show();
            if ($targetNav.length) {
                $targetNav.addClass('nav-tab-active');
            }
            try {
                sessionStorage.setItem('postie_active_tab', tabKey);
            } catch (e) {
                // Ignore storage errors in restricted environments
            }
        }
    }

    jQuery('.simpleTabs-content').hide();

    // Determine initial tab: 1) URL hash, 2) sessionStorage from previous save/load, 3) first tab
    var initialTab = null;
    if (window.location.hash) {
        initialTab = sanitizeTabKey(window.location.hash.replace(/^#tab-/, '').replace(/^#/, ''));
    }

    if (!initialTab) {
        try {
            var savedTab = sessionStorage.getItem('postie_active_tab');
            if (savedTab) {
                initialTab = sanitizeTabKey(savedTab);
            }
        } catch (e) {}
    }

    if (initialTab) {
        activateTab(initialTab);
    } else {
        jQuery('.simpleTabs-content').first().show();
        jQuery('.nav-tab').first().addClass('nav-tab-active');
    }

    jQuery(".nav-tab").click(function (event) {
        event.preventDefault();
        var tab = jQuery(event.currentTarget).data('tab');
        if (!tab) {
            tab = jQuery(event.currentTarget).data('tab-index');
        }
        tab = sanitizeTabKey(String(tab));
        activateTab(tab);
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, null, '#tab-' + tab);
        }
    });

    // Unobtrusive event bindings for icon set and template dropdowns
    jQuery('#icon_set_select').on('change', function () {
        changeIconSet(this, false);
    });
    jQuery('#icon_size_select').on('change', function () {
        changeIconSet(this, true);
    });

    var iconSelect = document.getElementById('icon_set_select');
    if (iconSelect) {
        changeIconSet(iconSelect);
    }

    // Configuration-driven template preview bindings
    var previewConfigs = [
        { preview: 'imageTemplatePreview',   textarea: 'postie-settings-imagetemplate',   select: 'imagetemplateselect',   selected: 'postie-settings-selected_imagetemplate',   sample: 'smiling.jpg' },
        { preview: 'video1TemplatePreview',  textarea: 'postie-settings-video1template',  select: 'video1templateselect',  selected: 'postie-settings-selected_video1template',  sample: 'hi.mp4' },
        { preview: 'video2TemplatePreview',  textarea: 'postie-settings-video2template',  select: 'video2templateselect',  selected: 'postie-settings-selected_video2template',  sample: 'hi.flv' },
        { preview: 'audioTemplatePreview',   textarea: 'postie-settings-audiotemplate',   select: 'audiotemplateselect',   selected: 'postie-settings-selected_audiotemplate',   sample: 'funky.mp3' },
        { preview: 'generalTemplatePreview', textarea: 'postie-settings-generaltemplate', select: 'generaltemplateselect', selected: 'postie-settings-selected_generaltemplate', sample: 'interesting_document.doc' }
    ];

    previewConfigs.forEach(function (cfg) {
        jQuery('#' + cfg.select).on('change', function () {
            changeStyle(cfg.preview, cfg.textarea, cfg.select, cfg.selected, cfg.sample, false);
        });
        jQuery('#' + cfg.textarea).on('input change', function () {
            changeStyle(cfg.preview, cfg.textarea, cfg.select, cfg.selected, cfg.sample, true);
        });
        // Run initial preview
        changeStyle(cfg.preview, cfg.textarea, cfg.select, cfg.selected, cfg.sample, false);
    });

    initPostieProcessModal();
});
}

function initPostieProcessModal() {
    var $backdrop = jQuery('#postie-modal-backdrop');
    if (!$backdrop.length) {
        return;
    }

    jQuery('#postie-run-manual').on('click', function (e) {
        e.preventDefault();
        runPostieMailCheck(false);
    });

    jQuery('#postie-run-debug').on('click', function (e) {
        e.preventDefault();
        runPostieMailCheck(true);
    });

    jQuery('#postie-run-test').on('click', function (e) {
        e.preventDefault();
        runPostieConfigTest();
    });

    // Close modal handlers
    $backdrop.on('click', function (e) {
        if (e.target === this) {
            closePostieModal();
        }
    });

    jQuery('.postie-modal-close, .postie-modal-close-btn').on('click', function (e) {
        e.preventDefault();
        closePostieModal();
    });

    jQuery(document).on('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            if ($backdrop.is(':visible')) {
                closePostieModal();
            }
        }
    });

    // Copy logs handler
    jQuery(document).on('click', '.postie-copy-logs-btn', function (e) {
        e.preventDefault();
        var $btn = jQuery(this);
        var textToCopy = $btn.siblings('.postie-log-content').text();
        var originalText = $btn.text();
        var strings = (window.postieAdminData && window.postieAdminData.strings) ? window.postieAdminData.strings : {};
        var copiedLabel = strings.copied || 'Copied!';

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(function () {
                $btn.text(copiedLabel);
                setTimeout(function () { $btn.text(originalText); }, 2000);
            });
        } else {
            var $temp = jQuery('<textarea>');
            jQuery('body').append($temp);
            $temp.val(textToCopy).select();
            document.execCommand('copy');
            $temp.remove();
            $btn.text(copiedLabel);
            setTimeout(function () { $btn.text(originalText); }, 2000);
        }
    });
}

function openPostieModal(title) {
    var $backdrop = jQuery('#postie-modal-backdrop');
    jQuery('#postie-modal-title').text(title);
    $backdrop.removeAttr('aria-hidden').fadeIn(150);
}

function closePostieModal() {
    var $backdrop = jQuery('#postie-modal-backdrop');
    $backdrop.attr('aria-hidden', 'true').fadeOut(150);
}

function escapePostieHtml(str) {
    if (str === null || str === undefined) {
        return '';
    }
    if (typeof jQuery !== 'undefined') {
        return jQuery('<div>').text(String(str)).html();
    }
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function runPostieMailCheck(isDebug) {
    var strings = (window.postieAdminData && window.postieAdminData.strings) ? window.postieAdminData.strings : {};
    var ajaxUrl = (window.postieAdminData && window.postieAdminData.ajaxUrl) ? window.postieAdminData.ajaxUrl : 'admin-ajax.php';
    var nonce = (window.postieAdminData && window.postieAdminData.nonce) ? window.postieAdminData.nonce : '';

    var title = isDebug ? (strings.processingDebug || 'Processing emails (Debug)...') : (strings.processing || 'Processing emails...');
    var desc = strings.processingDesc || 'Connecting to mail server and checking for new messages. Please wait...';

    openPostieModal(title);

    var $body = jQuery('#postie-modal-body');
    $body.html(
        '<div class="postie-modal-loading">' +
            '<span class="spinner is-active postie-modal-spinner"></span>' +
            '<p class="postie-modal-loading-text">' + escapePostieHtml(desc) + '</p>' +
        '</div>'
    );

    jQuery('#postie-run-manual, #postie-run-debug, #postie-run-test').prop('disabled', true).addClass('button-disabled');

    jQuery.ajax({
        url: ajaxUrl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'postie_run_manual',
            postie_action_nonce: nonce,
            debug: isDebug ? 'true' : 'false'
        },
        success: function (response) {
            renderPostieModalResult(response, isDebug);
        },
        error: function (xhr, status, error) {
            var errorMsg = (strings.serverError || 'An unexpected server error occurred.') + ' (' + (error || status) + ')';
            renderPostieModalError(errorMsg, []);
        },
        complete: function () {
            jQuery('#postie-run-manual, #postie-run-debug, #postie-run-test').prop('disabled', false).removeClass('button-disabled');
        }
    });
}

function runPostieConfigTest() {
    var strings = (window.postieAdminData && window.postieAdminData.strings) ? window.postieAdminData.strings : {};
    var ajaxUrl = (window.postieAdminData && window.postieAdminData.ajaxUrl) ? window.postieAdminData.ajaxUrl : 'admin-ajax.php';
    var nonce = (window.postieAdminData && window.postieAdminData.nonce) ? window.postieAdminData.nonce : '';

    var title = strings.testingConfig || 'Testing Configuration...';
    var desc = strings.testingConfigDesc || 'Connecting to mail server and verifying configuration. Please wait...';

    openPostieModal(title);

    var $body = jQuery('#postie-modal-body');
    $body.html(
        '<div class="postie-modal-loading">' +
            '<span class="spinner is-active postie-modal-spinner"></span>' +
            '<p class="postie-modal-loading-text">' + escapePostieHtml(desc) + '</p>' +
        '</div>'
    );

    jQuery('#postie-run-manual, #postie-run-debug, #postie-run-test').prop('disabled', true).addClass('button-disabled');

    jQuery.ajax({
        url: ajaxUrl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'postie_test_config',
            postie_action_nonce: nonce
        },
        success: function (response) {
            renderPostieTestResult(response);
        },
        error: function (xhr, status, error) {
            var errorMsg = (strings.serverError || 'An unexpected server error occurred.') + ' (' + (error || status) + ')';
            renderPostieModalError(errorMsg, []);
        },
        complete: function () {
            jQuery('#postie-run-manual, #postie-run-debug, #postie-run-test').prop('disabled', false).removeClass('button-disabled');
        }
    });
}

function renderPostieTestResult(response) {
    var strings = (window.postieAdminData && window.postieAdminData.strings) ? window.postieAdminData.strings : {};

    if (!response || !response.success) {
        var msg = (response && response.data && response.data.message) ? response.data.message : (strings.testFailed || 'Configuration Test Failed');
        var logs = (response && response.data && response.data.logs) ? response.data.logs : [];
        renderPostieModalError(msg, logs);
        return;
    }

    var data = response.data || {};
    var result = data.result || {};
    var duration = data.duration || '';
    var logs = data.logs || [];
    var errors = result.errors || [];
    var isConnected = result.connect_success;
    var serverSettings = result.server_settings || {};
    var clock = result.clock || {};
    var env = result.environment || {};

    jQuery('#postie-modal-title').text(strings.testComplete || 'Configuration Test Complete');

    var html = '';

    // Duration & status pill bar
    html += '<div class="postie-modal-stats-bar">';
    html += '<div class="postie-stat-pill ' + (isConnected ? 'postie-stat-pill-success' : 'postie-stat-pill-error') + '">';
    html += '<span class="postie-stat-val">' + (isConnected ? '&#10003; Connected' : '&#10007; Failed') + '</span>';
    html += '</div>';
    if (isConnected) {
        html += '<div class="postie-stat-pill"><span class="postie-stat-val">' + escapePostieHtml(result.message_count) + '</span> <span class="postie-stat-label">Waiting Messages</span></div>';
    }
    if (duration) {
        html += '<div class="postie-stat-pill postie-stat-duration"><span class="postie-stat-val">' + escapePostieHtml(duration) + '</span> <span class="postie-stat-label">Duration</span></div>';
    }
    html += '</div>';

    // Connection Alert Banner
    if (isConnected) {
        html += '<div class="postie-modal-notice notice-success">';
        html += '<p><strong>' + escapePostieHtml(strings.connSuccess || 'Mail server connected successfully!') + '</strong></p>';
        if (result.connection_msg) {
            html += '<p style="margin: 4px 0 0 0;">' + escapePostieHtml(result.connection_msg) + ' &bull; ' + escapePostieHtml(result.message_count) + ' waiting message(s) in mailbox.</p>';
        }
        html += '</div>';
    } else {
        html += '<div class="postie-modal-notice notice-error">';
        html += '<p><strong>' + escapePostieHtml(strings.connFailed || 'Mail server connection failed') + ':</strong></p>';
        if (errors.length > 0) {
            html += '<ul>';
            for (var e = 0; e < errors.length; e++) {
                html += '<li>' + escapePostieHtml(errors[e]) + '</li>';
            }
            html += '</ul>';
        }
        html += '</div>';
    }

    // Diagnostics 3-Card Grid
    html += '<div class="postie-test-grid">';

    // Card 1: Mail Server
    html += '<div class="postie-test-card">';
    html += '<div class="postie-test-card-title">Mail Server</div>';
    html += '<ul>';
    html += '<li><strong>Server:</strong> ' + escapePostieHtml(serverSettings.server || '(not set)') + '</li>';
    html += '<li><strong>Port:</strong> ' + escapePostieHtml(serverSettings.port || '(not set)') + '</li>';
    html += '<li><strong>User:</strong> ' + escapePostieHtml(serverSettings.user || '(not set)') + '</li>';
    html += '<li><strong>Protocol:</strong> ' + escapePostieHtml(serverSettings.protocol || '') + '</li>';
    html += '<li><strong>Connection:</strong> ' + escapePostieHtml(serverSettings.connection || 'socket') + '</li>';
    html += '</ul>';
    html += '</div>';

    // Card 2: Clock & Timezone
    html += '<div class="postie-test-card">';
    html += '<div class="postie-test-card-title">Clock & Timezone</div>';
    html += '<ul>';
    html += '<li><strong>WP Timezone:</strong> ' + escapePostieHtml(clock.timezone || '') + '</li>';
    html += '<li><strong>Current Time:</strong> ' + escapePostieHtml(clock.current_time || '') + '</li>';
    html += '<li><strong>GMT Time:</strong> ' + escapePostieHtml(clock.gmt_time || '') + '</li>';
    html += '<li><strong>Time Offset:</strong> ' + escapePostieHtml(clock.offset || 0) + '</li>';
    html += '<li><strong>Post Time:</strong> ' + escapePostieHtml(clock.post_time || '') + '</li>';
    html += '</ul>';
    html += '</div>';

    // Card 3: Environment
    html += '<div class="postie-test-card">';
    html += '<div class="postie-test-card-title">Environment</div>';
    html += '<ul>';
    html += '<li><strong>OS:</strong> ' + escapePostieHtml(env.os || '') + '</li>';
    html += '<li><strong>PHP Version:</strong> ' + escapePostieHtml(env.php_version || '') + '</li>';
    html += '<li><strong>WordPress:</strong> ' + escapePostieHtml(env.wp_version || '') + '</li>';
    html += '<li><strong>Charset:</strong> ' + escapePostieHtml(env.charset || 'UTF-8') + '</li>';
    html += '</ul>';
    html += '</div>';

    html += '</div>';

    // Detailed Diagnostics Accordion
    if (logs.length > 0) {
        var openAttr = !isConnected ? ' open' : '';
        html += '<details class="postie-modal-details"' + openAttr + '>';
        html += '<summary class="postie-modal-summary">';
        html += '<span>Detailed Diagnostics (' + logs.length + ' lines)</span>';
        html += '</summary>';
        html += '<div class="postie-logs-container">';
        html += '<button type="button" class="button button-small postie-copy-logs-btn">' + escapePostieHtml(strings.copyLogs || 'Copy Logs') + '</button>';
        html += '<pre class="postie-log-content">' + escapePostieHtml(logs.join("\n")) + '</pre>';
        html += '</div></details>';
    }

    jQuery('#postie-modal-body').html(html);
}

function renderPostieModalResult(response, isDebug) {
    var strings = (window.postieAdminData && window.postieAdminData.strings) ? window.postieAdminData.strings : {};

    if (!response || !response.success) {
        var msg = (response && response.data && response.data.message) ? response.data.message : (strings.processFailed || 'Email Processing Failed');
        var logs = (response && response.data && response.data.logs) ? response.data.logs : [];
        renderPostieModalError(msg, logs);
        return;
    }

    var data = response.data || {};
    var result = data.result || {};
    var duration = data.duration || '';
    var logs = data.logs || [];
    var posts = result.posts || [];
    var comments = result.comments || [];
    var errors = result.errors || [];
    var skipped = result.skipped || 0;
    var total = result.total || 0;
    var processed = result.processed || 0;

    jQuery('#postie-modal-title').text(strings.processComplete || 'Email Processing Complete');

    var html = '';

    // Duration & Status Pill Bar
    html += '<div class="postie-modal-stats-bar">';
    html += '<div class="postie-stat-pill"><span class="postie-stat-val">' + escapePostieHtml(total) + '</span> <span class="postie-stat-label">Checked</span></div>';
    html += '<div class="postie-stat-pill"><span class="postie-stat-val">' + escapePostieHtml(posts.length) + '</span> <span class="postie-stat-label">Posts</span></div>';
    html += '<div class="postie-stat-pill"><span class="postie-stat-val">' + escapePostieHtml(skipped) + '</span> <span class="postie-stat-label">Skipped</span></div>';
    if (duration) {
        html += '<div class="postie-stat-pill postie-stat-duration"><span class="postie-stat-val">' + escapePostieHtml(duration) + '</span> <span class="postie-stat-label">Duration</span></div>';
    }
    html += '</div>';

    // Errors if any occurred during fetch
    if (errors.length > 0) {
        html += '<div class="postie-modal-notice notice-error">';
        html += '<p><strong>' + escapePostieHtml(strings.errorsEncountered || 'Errors Encountered') + ':</strong></p><ul>';
        for (var e = 0; e < errors.length; e++) {
            html += '<li>' + escapePostieHtml(errors[e]) + '</li>';
        }
        html += '</ul></div>';
    }

    // Created Posts Section
    if (posts.length > 0) {
        html += '<div class="postie-modal-section">';
        html += '<h4 class="postie-section-title">' + escapePostieHtml(strings.postsCreated || 'Posts Created') + ' (' + posts.length + ')</h4>';
        html += '<div class="postie-post-list">';
        for (var i = 0; i < posts.length; i++) {
            var post = posts[i];
            html += '<div class="postie-post-item">';
            html += '<div class="postie-post-info">';
            html += '<span class="postie-post-title">' + escapePostieHtml(post.title) + '</span>';
            html += '<div class="postie-post-meta">';
            if (post.type) {
                html += '<span class="postie-badge postie-badge-type">' + escapePostieHtml(post.type) + '</span> ';
            }
            if (post.status) {
                var statusClass = 'postie-badge-' + post.status;
                html += '<span class="postie-badge ' + escapePostieHtml(statusClass) + '">' + escapePostieHtml(post.status) + '</span>';
            }
            html += '</div></div>';

            html += '<div class="postie-post-actions">';
            if (post.edit_url) {
                html += '<a href="' + escapePostieHtml(post.edit_url) + '" target="_blank" class="button button-small button-primary">' + escapePostieHtml(strings.editPost || 'Edit') + ' &rarr;</a> ';
            }
            if (post.view_url) {
                html += '<a href="' + escapePostieHtml(post.view_url) + '" target="_blank" class="button button-small">' + escapePostieHtml(strings.viewPost || 'View') + '</a>';
            }
            html += '</div></div>';
        }
        html += '</div></div>';
    }

    // Created Comments Section
    if (comments.length > 0) {
        html += '<div class="postie-modal-section">';
        html += '<h4 class="postie-section-title">' + escapePostieHtml(strings.commentsCreated || 'Comments Created') + ' (' + comments.length + ')</h4>';
        html += '<div class="postie-comment-list">';
        for (var c = 0; c < comments.length; c++) {
            var comment = comments[c];
            html += '<div class="postie-comment-item">';
            html += '<span>Comment by <strong>' + escapePostieHtml(comment.author || 'Anonymous') + '</strong></span>';
            if (comment.view_url) {
                html += ' <a href="' + escapePostieHtml(comment.view_url) + '" target="_blank" class="button button-small">' + escapePostieHtml(strings.viewComment || 'View Comment') + '</a>';
            }
            html += '</div>';
        }
        html += '</div></div>';
    }

    // Empty state if no posts/comments and no errors
    if (total === 0 && posts.length === 0 && comments.length === 0 && errors.length === 0) {
        html += '<div class="postie-modal-empty-state">';
        html += '<div class="postie-empty-icon">&#10003;</div>';
        html += '<p class="postie-empty-text">' + escapePostieHtml(strings.noEmails || 'Mailbox checked. No new emails found.') + '</p>';
        html += '</div>';
    }

    // Logs Accordion
    if (logs.length > 0) {
        var openAttr = (isDebug || errors.length > 0) ? ' open' : '';
        html += '<details class="postie-modal-details"' + openAttr + '>';
        html += '<summary class="postie-modal-summary">';
        html += '<span>' + escapePostieHtml(strings.copyLogs ? 'Execution Logs' : 'Logs') + ' (' + logs.length + ' lines)</span>';
        html += '</summary>';
        html += '<div class="postie-logs-container">';
        html += '<button type="button" class="button button-small postie-copy-logs-btn">' + escapePostieHtml(strings.copyLogs || 'Copy Logs') + '</button>';
        html += '<pre class="postie-log-content">' + escapePostieHtml(logs.join("\n")) + '</pre>';
        html += '</div></details>';
    }

    jQuery('#postie-modal-body').html(html);
}

function renderPostieModalError(message, logs) {
    var strings = (window.postieAdminData && window.postieAdminData.strings) ? window.postieAdminData.strings : {};
    jQuery('#postie-modal-title').text(strings.processFailed || 'Email Processing Failed');

    var html = '';
    html += '<div class="postie-modal-notice notice-error">';
    html += '<p><strong>' + escapePostieHtml(message) + '</strong></p>';
    html += '</div>';

    if (logs && logs.length > 0) {
        html += '<details class="postie-modal-details" open>';
        html += '<summary class="postie-modal-summary">';
        html += '<span>Execution Logs (' + logs.length + ' lines)</span>';
        html += '</summary>';
        html += '<div class="postie-logs-container">';
        html += '<button type="button" class="button button-small postie-copy-logs-btn">' + escapePostieHtml(strings.copyLogs || 'Copy Logs') + '</button>';
        html += '<pre class="postie-log-content">' + escapePostieHtml(logs.join("\n")) + '</pre>';
        html += '</div></details>';
    }

    jQuery('#postie-modal-body').html(html);
}

function changeIconSet(selectBox, size) {
    var iconSet = document.getElementById('postie-settings-icon_set');
    var iconSize = document.getElementById('postie-settings-icon_size');
    var preview = document.getElementById('postie-settings-attachment_preview');
    var iconDir = postieAdminData.iconDir + '/';
    var hiddenInput = (size === true) ? iconSize : iconSet;
    if (selectBox && selectBox.value) {
        hiddenInput.value = selectBox.value;
    }
    var fileTypes = ['doc', 'pdf', 'xls', 'default'];
    if (preview && iconSet && iconSize) {
        var html = '';
        for (var j = 0; j < fileTypes.length; j++) {
            html += "<img src='" + iconDir + iconSet.value + '/' +
                    fileTypes[j] + '-' + iconSize.value + ".png' />";
        }
        preview.innerHTML = html;
    }
}

function sanitizePreviewHtml(rawHtml) {
    if (!rawHtml || typeof rawHtml !== 'string') {
        return '';
    }
    // Strip script, iframe, object, embed tags and their contents
    var sanitized = rawHtml.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
                           .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '')
                           .replace(/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi, '')
                           .replace(/<embed\b[^>]*>/gi, '');

    // Strip inline event attributes (e.g. onerror=, onclick=, onload=)
    sanitized = sanitized.replace(/\s+on[a-zA-Z]+\s*=\s*(['"]).*?\1/gi, '')
                         .replace(/\s+on[a-zA-Z]+\s*=\s*[^>\s]+/gi, '');

    // Strip javascript: pseudo-protocols
    sanitized = sanitized.replace(/(href|src)\s*=\s*(['"])\s*javascript:[^'"]*\2/gi, '$1="#"');

    return sanitized;
}

function applyTemplatePlaceholders(templateStr, data) {
    if (!templateStr || typeof templateStr !== 'string') {
        return '';
    }
    data = data || {};
    var sample = data.sample || '';
    var templateDir = data.templateDir || '';
    var siteUrl = data.siteUrl || '';
    var fileType = data.fileType || 'mp4';

    var fileLink = (templateDir ? templateDir + '/' : '') + sample;
    var thumb = (templateDir ? templateDir + '/' : '') + sample.replace(/\.jpg/, '-150x150.jpg');
    var medium = (templateDir ? templateDir + '/' : '') + sample.replace(/\.jpg/, '-300x200.jpg');
    var large = (templateDir ? templateDir + '/' : '') + sample.replace(/\.jpg/, '-1024x682.jpg');
    var pagelink = siteUrl + '/?attachment_id=9999';

    var replacements = {
        '{FILELINK}': fileLink,
        '{FULL}': fileLink,
        '{IMAGE}': fileLink,
        '{FILENAME}': sample,
        '{FILETYPE}': fileType,
        '{PAGELINK}': pagelink,
        '{RELFILENAME}': sample,
        '{THUMB}': thumb,
        '{THUMBNAIL}': thumb,
        '{MEDIUM}': medium,
        '{LARGE}': large,
        '{HEIGHT}': '800',
        '{WIDTH}': '1200',
        '{THUMBWIDTH}': '150',
        '{THUMBHEIGHT}': '150',
        '{MEDIUMWIDTH}': '300',
        '{MEDIUMHEIGHT}': '200',
        '{LARGEWIDTH}': '1024',
        '{LARGEHEIGHT}': '682',
        '{ID}': '9999',
        '{FILEID}': '9999',
        '{POSTTITLE}': 'Post title',
        '{CAPTION}': 'Spencer smiling'
    };

    return templateStr.replace(/\{[A-Z0-9_()]+\}/g, function (match) {
        return Object.prototype.hasOwnProperty.call(replacements, match) ? replacements[match] : match;
    });
}

function changeStyle(previewId, template, select, selected, sample, custom) {
    var preview = document.getElementById(previewId);
    var pageStyles = document.getElementById(select);
    var hiddenStyle = document.getElementById(selected);
    var pageStyle = document.getElementById(template);
    if (!preview || !pageStyles || !hiddenStyle || !pageStyle) {
        return;
    }
    var selectedStyle;
    if (custom === true) {
        selectedStyle = pageStyles.options[pageStyles.options.length - 1];
        selectedStyle.value = pageStyle.value;
        selectedStyle.selected = true;
    } else {
        selectedStyle = (pageStyles.selectedIndex >= 0) ? pageStyles.options[pageStyles.selectedIndex] : null;
    }
    if (!selectedStyle) {
        return;
    }
    hiddenStyle.value = selectedStyle.innerHTML;

    var adminData = (typeof postieAdminData !== 'undefined') ? postieAdminData : {};
    var previewHTML = applyTemplatePlaceholders(selectedStyle.value, {
        templateDir: adminData.templateDir,
        siteUrl: adminData.siteUrl,
        sample: sample,
        fileType: 'mp4'
    });

    preview.innerHTML = sanitizePreviewHtml(previewHTML);
    pageStyle.value = selectedStyle.value;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        sanitizeTabKey: sanitizeTabKey,
        sanitizePreviewHtml: sanitizePreviewHtml,
        escapePostieHtml: escapePostieHtml,
        applyTemplatePlaceholders: applyTemplatePlaceholders
    };
}

