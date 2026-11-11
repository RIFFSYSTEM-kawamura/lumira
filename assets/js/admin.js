/**
 * RIFF Admin JS
 * LUMIRA WordPress Theme
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        // Initialize color picker
        if ($.fn.wpColorPicker) {
            $('.wp-color-picker-field').wpColorPicker();
            $('.riff-color-picker').wpColorPicker();
        }

        // Logo image uploader
        var logoFrame;
        $('#logo-upload-btn').on('click', function (e) {
            e.preventDefault();
            if (logoFrame) {
                logoFrame.open();
                return;
            }
            logoFrame = wp.media({
                title: 'ロゴ画像を選択',
                button: { text: '選択' },
                multiple: false,
                library: { type: 'image' }
            });
            logoFrame.on('select', function () {
                var attachment = logoFrame.state().get('selection').first().toJSON();
                $('#header_logo_image_id').val(attachment.id);
                var previewHtml = '<img src="' + attachment.url + '" style="max-width:200px;height:auto;" alt="Logo Preview">' +
                    '<p><a href="#" id="logo-remove-btn" style="color:#d32f2f;text-decoration:none;">削除</a></p>';
                $('#logo-image-preview').html(previewHtml);
                attachLogoRemoveHandler();
            });
            logoFrame.open();
        });

        function attachLogoRemoveHandler() {
            $('#logo-remove-btn').off('click').on('click', function (e) {
                e.preventDefault();
                $('#header_logo_image_id').val('');
                $('#logo-image-preview').html('');
            });
        }
        attachLogoRemoveHandler();

        // Menu background image uploader
        var navBgFrame;
        $('#nav-bg-upload-btn').on('click', function (e) {
            e.preventDefault();
            if (navBgFrame) {
                navBgFrame.open();
                return;
            }
            navBgFrame = wp.media({
                title: 'メニュー背景画像を選択',
                button: { text: '選択' },
                multiple: false,
                library: { type: 'image' }
            });
            navBgFrame.on('select', function () {
                var attachment = navBgFrame.state().get('selection').first().toJSON();
                $('#nav_bg_image_id').val(attachment.id);
                var previewHtml = '<img src="' + attachment.url + '" style="max-width:200px;height:auto;" alt="Preview">' +
                    '<p><a href="#" id="nav-bg-remove-btn" style="color:#d32f2f;text-decoration:none;">削除</a></p>';
                $('#nav-bg-image-preview').html(previewHtml);
                attachRemoveHandler();
            });
            navBgFrame.open();
        });

        function attachRemoveHandler() {
            $('#nav-bg-remove-btn').off('click').on('click', function (e) {
                e.preventDefault();
                $('#nav_bg_image_id').val('');
                $('#nav-bg-image-preview').html('');
            });
        }
        attachRemoveHandler();

        // Generic image uploader for .riff-img-upload buttons
        var mediaFrames = {};
        $(document).on('click', '.riff-img-upload', function (e) {
            e.preventDefault();
            var inputId = $(this).data('input');
            var previewId = $(this).data('preview');

            if (mediaFrames[inputId]) {
                mediaFrames[inputId].open();
                return;
            }

            mediaFrames[inputId] = wp.media({
                title: '画像を選択',
                button: { text: '選択' },
                multiple: false,
                library: { type: 'image' }
            });

            mediaFrames[inputId].on('select', function () {
                var attachment = mediaFrames[inputId].state().get('selection').first().toJSON();
                $('#' + inputId).val(attachment.id);
                if (previewId) {
                    $('#' + previewId).attr('src', attachment.url).show();
                }
            });

            mediaFrames[inputId].open();
        });

        // Generic image remove handler for .riff-img-remove buttons
        $(document).on('click', '.riff-img-remove', function (e) {
            e.preventDefault();
            var inputId = $(this).data('input');
            var previewId = $(this).data('preview');
            $('#' + inputId).val('');
            if (previewId) {
                $('#' + previewId).hide();
            }
        });

        // Menu image uploader - 動的イベントハンドラ
        var menuFrames = {};
        $(document).on('click', '[id^="menu-upload-btn-"]', function(e) {
            e.preventDefault();
            var btnId = $(this).attr('id');
            var num = btnId.replace('menu-upload-btn-', '');
            var inputId = 'menu_image_' + num;
            var previewId = 'menu-preview-' + num;
            var removeBtnId = 'menu-remove-btn-' + num;

            if (menuFrames[inputId]) {
                menuFrames[inputId].open();
                return;
            }

            menuFrames[inputId] = wp.media({
                title: 'メニュー画像を選択',
                button: { text: '選択' },
                multiple: false,
                library: { type: 'image' }
            });

            menuFrames[inputId].on('select', function() {
                var attachment = menuFrames[inputId].state().get('selection').first().toJSON();
                $('#' + inputId).val(attachment.id);
                var previewHtml = '<img src="' + attachment.url + '" style="width:100%;height:100%;object-fit:cover;" alt="Menu Preview">';
                $('#' + previewId).html(previewHtml);

                // Show remove button if not exists
                var removeBtn = $('#' + removeBtnId);
                if (removeBtn.length === 0) {
                    $('#' + btnId).after('<button type="button" id="' + removeBtnId + '" class="button button-small" style="width:100%;color:#d32f2f;margin-top:4px;">削除</button>');
                }
            });

            menuFrames[inputId].open();
        });

        // Menu image remove handler
        $(document).on('click', '[id^="menu-remove-btn-"]', function(e) {
            e.preventDefault();
            var btnId = $(this).attr('id');
            var num = btnId.replace('menu-remove-btn-', '');
            var inputId = 'menu_image_' + num;
            var previewId = 'menu-preview-' + num;

            $('#' + inputId).val('');
            $('#' + previewId).html('<span style="color:#999;font-size:12px;">プレビュー</span>');
            $(this).remove();
        });

        // Campaign image uploader - 動的イベントハンドラ
        var campaignFrames = {};
        $(document).on('click', '[id^="campaign-upload-btn-"]', function(e) {
            e.preventDefault();
            var btnId = $(this).attr('id');
            var num = btnId.replace('campaign-upload-btn-', '');
            var inputId = 'campaign_image_' + num;
            var previewId = 'campaign-preview-' + num;
            var removeBtnId = 'campaign-remove-btn-' + num;

            if (campaignFrames[inputId]) {
                campaignFrames[inputId].open();
                return;
            }

            campaignFrames[inputId] = wp.media({
                title: 'キャンペーン画像を選択',
                button: { text: '選択' },
                multiple: false,
                library: { type: 'image' }
            });

            campaignFrames[inputId].on('select', function() {
                var attachment = campaignFrames[inputId].state().get('selection').first().toJSON();
                $('#' + inputId).val(attachment.id);
                var previewHtml = '<img src="' + attachment.url + '" style="width:100%;height:100%;object-fit:cover;" alt="Campaign Preview">';
                $('#' + previewId).html(previewHtml);

                // Show remove button if not exists
                var removeBtn = $('#' + removeBtnId);
                if (removeBtn.length === 0) {
                    $('#' + btnId).after('<button type="button" id="' + removeBtnId + '" class="button button-small" style="width:100%;color:#d32f2f;margin-top:4px;">削除</button>');
                }
            });

            campaignFrames[inputId].open();
        });

        // Campaign image remove handler
        $(document).on('click', '[id^="campaign-remove-btn-"]', function(e) {
            e.preventDefault();
            var btnId = $(this).attr('id');
            var num = btnId.replace('campaign-remove-btn-', '');
            var inputId = 'campaign_image_' + num;
            var previewId = 'campaign-preview-' + num;

            $('#' + inputId).val('');
            $('#' + previewId).html('<span style="color:#999;font-size:12px;">プレビュー</span>');
            $(this).remove();
        });

        // Page Hero image uploader
        var heroFrame;
        $('#lumira-hero-upload-btn').on('click', function(e) {
            e.preventDefault();
            if (heroFrame) {
                heroFrame.open();
                return;
            }
            heroFrame = wp.media({
                title: 'ページヒーロー背景画像を選択',
                button: { text: '選択' },
                multiple: false,
                library: { type: 'image' }
            });
            heroFrame.on('select', function() {
                var attachment = heroFrame.state().get('selection').first().toJSON();
                $('#lumira_page_hero_image').val(attachment.id);
                var previewHtml = '<img src="' + attachment.url + '" style="max-width:300px;height:auto;">';
                $('#hero-image-preview').html(previewHtml);

                // Show remove button if not exists
                if (!$('#lumira-hero-remove-btn').length) {
                    $('#lumitra-hero-upload-btn').after('<button type="button" id="lumira-hero-remove-btn" class="button" style="color:#d32f2f;margin-left:4px;">削除</button>');
                }
            });
            heroFrame.open();
        });

        // Page Hero image remove handler
        $(document).on('click', '#lumira-hero-remove-btn', function(e) {
            e.preventDefault();
            $('#lumira_page_hero_image').val('');
            $('#hero-image-preview').html('');
            $(this).remove();
        });
    });

})(jQuery);
