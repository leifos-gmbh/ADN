(function($, core, shell, undefined) {

shell.driver({
	name: 'stringsInLayout',
	create: function() {
		$.each(shell.settings.strings || [], function(id, s) {
			$('#' + id).html(core.str(s));
		});

		var strings = shell.settings.strings;
		if (!strings) {
			shell.settings.strings = strings = {};
		}

		// window title
		strings.title = strings.pageHeaderText || 'No title';

		// toc, index, and search tab labels
		$('#tocLabel').html(core.str(shell.setting('toc.label')));
		$('#indexLabel').html(core.str(shell.setting('index.label')));
		$('#searchLabel').html(core.str(shell.setting('search.label')));
	}
});

})(jQuery, nethelp, nethelpshell);