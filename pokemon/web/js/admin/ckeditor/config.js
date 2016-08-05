/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
  //  config.filebrowserUploadUrl = '/upload.php';


    config.coreStyles_bold = { element: 'b', overrides: 'strong' };
    config.coreStyles_italic = { element: 'i', overrides: 'em' };

    config.protectedSource.push(/<(style)[^>]*>.*<\/style>/ig);
    // ��������� ���� <script>
    config.protectedSource.push(/<(script)[^>]*>.*<\/script>/ig);
    // ��������� php-���
    config.protectedSource.push(/<\?[\s\S]*?\?>/g);

    // ��������� <%
    config.protectedSource.push(/<\%[\s\S]*?\%>/g);

    // ��������� ����� ���: <!--dev-->��� ������ ��� ���<!--/dev-->
    config.protectedSource.push(/<!--dev-->[\s\S]*<!--\/dev-->/g);

    config.protectedSource.push( /<script[\s\S]*?script>/g ); /* script tags */
    config.allowedContent = true; /* all tags */
    var dom = 'http://pokemon.loc/js/admin';
    config.filebrowserBrowseUrl = dom+'/ckfinder/ckfinder.html';
    config.filebrowserImageBrowseUrl = dom+'/ckfinder/ckfinder.html?type=Images';
    config.filebrowserFlashBrowseUrl = dom+'/ckfinder/ckfinder.html?type=Flash';
    config.filebrowserUploadUrl = dom+'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
    config.filebrowserImageUploadUrl = dom+'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
    config.filebrowserFlashUploadUrl = dom+'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
};
