(function($, core, shell, undefined) {

// default button options
shell.mergeSettings({
	buttons: {
		btnGotoPrev: {
			showLabel: false,
			label: undefined, //'Previous topic',
			icon: 'ui-icon-circle-arrow-w',
			click: 'this.gotoPrev(e);'
		},
		btnGotoNext: {
			showLabel: false,
			label: undefined, //'Next topic',
			icon: 'ui-icon-circle-arrow-e',
			click: 'this.gotoNext(e);'
		},
		btnGotoHome: {
			showLabel: false,
			label: undefined, //'Home topic',
			icon: 'ui-icon-home',
			click: 'this.gotoHome(e);'
		},
		btnPrint: {
			showLabel: false,
			label: undefined, //'Print',
			icon: 'ui-icon-print',
			click: 'this.print();'
		},
		btnEmail: {
			showLabel: false,
			label: undefined, //'Email',
			icon: 'ui-icon-mail-closed',
			click: 'location.href = this.mailtoUrl();'
		},
		btnPoweredBy: {
			showLabel: false,
			label: undefined, //'Powered by Doc-To-Help'
			icon: 'ui-icon-power'
		}
	}
});

shell.driver({
	name: 'buttons',
	create: function() {
		var buttons = {};
		shell.buttons = buttons;
		$.each(shell.settings.buttons || [], function(id, b) {
			var bel = typeof b === 'object' ? $('#' + id) : undefined,
				f;
			if (bel && bel.length) {
				buttons[id] = bel.button({
					text: b.showLabel !== false && b.label !== '',
					label: b.label,
					icons: { primary: b.icon, secondary: b.icon2 }
				});
				if (b.label === '') {
					bel.find('.ui-button-text').html('&nbsp;');
				}
				f = b.click;
				f = $.isFunction(f) ? f :
					typeof f === 'string' ? Function('e', 'target', 'options', f) :
					undefined;
				f && bel.click(function(e) { return f.call(shell, e, this, b); });
				bel[b.visible || b.visible == undefined ? 'show' : 'hide']();
			}
		});
		// fix for the browser default behavior: focus out from button after click
		$('body').delegate('.ui-button', 'mouseup', function() { $(this).blur(); });
	}
});

})(jQuery, nethelp, nethelpshell);