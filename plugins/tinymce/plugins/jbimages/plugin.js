/**
 * Justboil.me - a TinyMCE image upload plugin
 * jbimages/plugin.js
 *
 * Released under Creative Commons Attribution 3.0 Unported License
 *
 * License: http://creativecommons.org/licenses/by/3.0/
 * Plugin info: http://justboil.me/
 * Author: Viktor Kuzhelnyi
 *
 * Version: 2.3 released 23/06/2013
 */

tinymce.PluginManager.add('jbimages', function(editor, url) {
	
	function jbBox() {
		editor.windowManager.open({
			title: 'Kép feltöltése',
			file : url + '/dialog.php',
			width : 350,
			height: 135,
			buttons: [{
				text: 'Feltöltés',
				classes:'widget btn primary first abs-layout-item',
				disabled : true,
				onclick: 'close'
			},
			{
				text: 'Close',
				onclick: 'close'
			}]
		});
	}
	
	// Add a button that opens a window
	editor.addButton('jbimages', {
		tooltip: 'Kép feltöltése',
		icon : 'image',
		text: 'Feltöltés',
		onclick: jbBox
	});

	// Adds a menu item to the tools menu
	editor.addMenuItem('jbimages', {
		text: 'Kép feltöltése',
		icon : 'image',
		context: 'insert',
		onclick: jbBox
	});
});