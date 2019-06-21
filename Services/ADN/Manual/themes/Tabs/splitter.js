(function($, core, shell, undefined) {

shell.plugin({
	name: 'splitter',
	create: function() {
		if (shell.isTopicOnlyMode()) {
			return;
		}
		var splitter = $('#pageSplitter').css('position', 'absolute'), // otherwise draggable would set position to 'relative'
			btn = $('#pageSplitterButton'),
			btnHide = $('#pageSplitterButtonHide'),
			btnShow = $('#pageSplitterButtonShow').hide(),
			sw,
			lpanel = splitter.prev(),
			rpanel = splitter.next(),
			parent = splitter.parent(),
			left = splitter.position().left,
			width = splitter.width(),
			hidden = false,
			overlay = $('<div id="pageSplitterOverlay" class="ui-widget-overlay" />').hide()
				.css({ opacity: 0.1, zIndex: 99 });
		parent.append(overlay).append(splitter);
		function sideHide(reset) {
			if (reset !== true && hidden) {
				return;
			}
			lpanel.hide();
			splitter.css('left', 0);
			rpanel.css('left', width);
			btnHide.hide();
			btnShow.show();
			hidden = true;
		}
		function sideShow(reset) {
			if (reset !== true && !hidden) {
				return;
			}
			rpanel.css('left', left + width);
			splitter.css('left', left);
			lpanel.show();
			btnHide.show();
			btnShow.hide();
			hidden = false;
		}
		function sideToggle(show) {
			((show == undefined ? hidden : show) ? sideShow : sideHide)();
		};
		shell.sideToggle = sideToggle;
		sw = splitter.draggable({
			axis: 'x',
			start: function(e, d) {
				overlay.show();
				sw.containment = sw.options.containment = [ 100, 0, parent.width() - 100, 0 ];
			},
			drag: function(e, d) {
				(e.pageX > width ? sideShow : sideHide)();
			},
			stop: function(e, d) {
				overlay.hide();
				if (hidden) {
					sideHide(true);
				}
				else {
					left = splitter.position().left;
					lpanel.show().width(left);
					rpanel.css('left', left + width);
					shell.trigger('splitter');
					hidden = false;
				}
			}
		}).data('draggable');
		splitter.hover(function() {
			splitter.addClass('ui-state-hover');
		}, function() {
			splitter.removeClass('ui-state-hover');
		}).dblclick(function() {
			sideToggle();
		});
		btn.hover(function() {
			btn.addClass('ui-state-hover');
		}, function() {
			btn.removeClass('ui-state-hover');
		}).click(function(e) {
			sideToggle();
		});
	}
});

})(jQuery, nethelp, nethelpshell);