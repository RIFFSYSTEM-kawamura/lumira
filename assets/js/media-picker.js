/**
 * LUMIRA Media Picker
 * Page Hero Image Selection
 */

jQuery(function($) {
  'use strict';

  // ページヒーロー画像ピッカー
  var mediaUploader;

  $('#lumira-hero-upload-btn').on('click', function(e) {
    e.preventDefault();

    if (mediaUploader) {
      mediaUploader.open();
      return;
    }

    mediaUploader = wp.media({
      title: 'ページヒーロー画像を選択',
      button: {
        text: '選択'
      },
      multiple: false,
      library: {
        type: 'image'
      }
    });

    mediaUploader.on('select', function() {
      var attachment = mediaUploader.state().get('selection').first().toJSON();
      $('#lumira_page_hero_image').val(attachment.id);
      $('#hero-image-preview').html('<img src="' + attachment.url + '" style="max-width: 300px; height: auto;">');

      // 削除ボタンを表示
      if (!$('#lumira-hero-remove-btn').length) {
        $('#lumira-hero-upload-btn').after(' <button type="button" id="lumira-hero-remove-btn" class="button" style="color: #d32f2f;">削除</button>');
      }
    });

    mediaUploader.open();
  });

  // 削除ボタン
  $(document).on('click', '#lumira-hero-remove-btn', function(e) {
    e.preventDefault();
    $('#lumira_page_hero_image').val('');
    $('#hero-image-preview').html('');
    $(this).remove();
  });

  // キャンペーン単一ヒーロー画像
  var campaignMediaUploader;

  $(document).on('click', '#lumira-campaign-single-hero-upload-btn', function(e) {
    e.preventDefault();

    if (campaignMediaUploader) {
      campaignMediaUploader.open();
      return;
    }

    campaignMediaUploader = wp.media({
      title: 'キャンペーン単一ヒーロー画像を選択',
      button: {
        text: '選択'
      },
      multiple: false,
      library: {
        type: 'image'
      }
    });

    campaignMediaUploader.on('select', function() {
      var attachment = campaignMediaUploader.state().get('selection').first().toJSON();
      $('#campaign_single_hero_image_id').val(attachment.id);
      $('#campaign-single-hero-preview').html('<img src="' + attachment.url + '" style="max-width: 300px; height: auto;">');
    });

    campaignMediaUploader.open();
  });

  // キャンペーンアーカイブヒーロー画像
  var campaignArchiveMediaUploader;

  $(document).on('click', '#lumira-campaign-archive-hero-upload-btn', function(e) {
    e.preventDefault();

    if (campaignArchiveMediaUploader) {
      campaignArchiveMediaUploader.open();
      return;
    }

    campaignArchiveMediaUploader = wp.media({
      title: 'キャンペーンアーカイブヒーロー画像を選択',
      button: {
        text: '選択'
      },
      multiple: false,
      library: {
        type: 'image'
      }
    });

    campaignArchiveMediaUploader.on('select', function() {
      var attachment = campaignArchiveMediaUploader.state().get('selection').first().toJSON();
      $('#campaign_archive_hero_image_id').val(attachment.id);
      $('#campaign-archive-hero-preview').html('<img src="' + attachment.url + '" style="max-width: 300px; height: auto;">');
    });

    campaignArchiveMediaUploader.open();
  });

  // About ヒーロー画像
  var aboutMediaUploader;

  $(document).on('click', '#lumira-about-hero-upload-btn', function(e) {
    e.preventDefault();

    if (aboutMediaUploader) {
      aboutMediaUploader.open();
      return;
    }

    aboutMediaUploader = wp.media({
      title: 'About ページヒーロー画像を選択',
      button: {
        text: '選択'
      },
      multiple: false,
      library: {
        type: 'image'
      }
    });

    aboutMediaUploader.on('select', function() {
      var attachment = aboutMediaUploader.state().get('selection').first().toJSON();
      $('#about_hero_image_id').val(attachment.id);
      $('#about-hero-preview').html('<img src="' + attachment.url + '" style="max-width: 300px; height: auto;">');
    });

    aboutMediaUploader.open();
  });
});
