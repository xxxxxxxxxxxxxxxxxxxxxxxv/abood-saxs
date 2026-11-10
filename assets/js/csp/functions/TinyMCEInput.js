/**
 * TinyMCEInput() function JS
 *
 * @since 12.5
 *
 * @package RosarioSIS
 */

var tinymceSettings = {
	selector: '.tinymce',
	plugins: 'link image pagebreak paste table textcolor colorpicker code fullscreen hr media lists',
	toolbar: "bold italic underline bullist numlist alignleft aligncenter alignright alignjustify link image forecolor backcolor code fullscreen",
	menu: {
		// file: {title: 'File', items: 'newdocument'},
		edit: {title: 'Edit', items: 'undo redo | cut copy paste pastetext'},
		insert: {title: 'Insert', items: 'media | hr pagebreak | inserttable cell row column'},
		// view: {title: 'View', items: 'visualaid'},
		format: {title: 'Format', items: 'formats | removeformat'}
	},
	paste_data_images: true,
	images_upload_handler: function (blobInfo, success, failure) {
		success("data:" + blobInfo.blob().type + ";base64," + blobInfo.base64());
	},
	pagebreak_separator: '<div style="page-break-after: always;"></div>',
	language: $('#tinymce_language').val(),
	directionality : (document.documentElement.dir === 'RTL' ? 'rtl' : 'ltr'),
	min_height: 200,
	relative_urls: false,
	// verify_html: false,
	remove_script_host: false,
	external_plugins: {
		// Add your plugins using the action hook below.
	}
};

csp.functions.tinyMCEInput = function() {
	tinymce.init(tinymceSettings);
}

$(csp.functions.tinyMCEInput);
