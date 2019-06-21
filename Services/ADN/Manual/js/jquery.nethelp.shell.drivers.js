(function($, core, shell, undefined) {

// method shell.popup()
shell.driver({
	name: 'popup',
	create: function() {
		shell.popup = function(el, options) {
			var w = el.data('popup'),
				t = $.type(options);
			if (w) {
				if (t === 'string') {
					w[options].apply(w, core.slice(arguments, 2));
				}
				return w;
			}
			else if (t === 'object') {
				return core.popup(el, options);
			}
			return undefined;
		}
	}
});
// topic spinner
shell.driver({
	name: 'topicSpinner',
	create: function() {
		var spinner = $('#topicSpinner'),
			spinnerMsg = $('#topicSpinnerMessage'),
			topicBlock = $('#topicBlock'),
			timer = 0,
			delay = 300;
		spinnerMsg.css({
			marginTop: -Math.round(spinnerMsg.outerHeight() / 2),
			marginLeft: -Math.round(spinnerMsg.outerWidth() / 2)
		});
		function spin(show) {
			show = show !== false;
			topicBlock.toggle(!show);
			spinner.toggle(show);
		}
		shell.topicSpin = spin;
		shell.bind('topicloading', function() {
			timer = setTimeout(spin, delay);
		});
		shell.bind('topicload', function() {
			timer && clearTimeout(timer);
			spin(false);
		});
		shell.ready(function() {
			spin(false);
		});
	}
});
// breadcrumbs
shell.driver({
	name: 'breadcrumbs',
	create: function() {
		function genBreadcrumbsHtml(links) {
			var s = '';
			for (var i in links) {
				i = links[i];
				s += ' / <a href="' + (i.url || '') + '" title="' + (i.tooltip || '') + '">' + i.title + '</a>';
			}
			return s.substring(3);
		}
		var panel = $('#breadcrumbs');
		if (panel.length) {
			panel.delegate('a', 'click', function(e) {
				e.preventDefault();
				shell.loadTopic($(this).attr('href'));
			});
			shell.bind('tocdeselect', function() {
				panel.empty().hide();
			});
			function printBC() {
				var bc = shell.toc.getBreadcrumbs(shell.setting('breadcrumbs.andselected'));
				if (bc.length) {
					panel.append(genBreadcrumbsHtml(bc)).show();
				}
				shell.trigger('breadcrumbsupdate');
			}
			shell.bind('tocselect', printBC);
		}
	}
});

})(jQuery, nethelp, nethelpshell);