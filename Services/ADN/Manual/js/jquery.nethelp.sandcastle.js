(function($, core, shell, undefined) {

shell.mergeSettings({
	sandcastle: {
		importPlugins: 'a-link k-link inlineText popupText'.split(' '),
		frameTemplate: '<div class="topic-frame"><iframe class="sandcastle" style="border:0; width:100%; height:100%;" frameborder="0" /></div>'
	}
});
shell.plugin({
	name: 'sandcastle',
	create: function() {
		shell.bind('topicvalidate', function(e, d) {
			var doc = d.document,
				c1flip = $.ui.position.c1flip;
			if (d.document.find('meta[name="NetHelpPlugin"][content="sandcastle"]')[0]) {
				e.preventDefault();
				d.frame.remove();
				d.frame.remove = $.noop;
				var topic = shell.topic,
					element = topic.element,
					stg = shell.settings.sandcastle,
					ifr;
				d.frame = true;
				element.html(stg.frameTemplate);
				ifr = element.find('iframe');
				ifr.load(function() {
					var path = core.getUrlPath(d.src),
						idoc = ifr[0].contentWindow.document,
						head = $(idoc.head),
						body = $(idoc.body);
					doc = $(idoc);
					var p = head.find('link, script, style').eq(0);
					if (!p[0]) {
						p = $(idoc.createElement('meta')).appendTo(head);
					}
					$('link[rel~="stylesheet"]').each(function() {
						var t = $(this);
						if (t.hasClass('apply-to-frame') || !t.hasClass('from-theme-layout')) {
							var l = idoc.createElement('link');
							l.rel = this.rel || 'Stylesheet';
							l.type = this.type || 'text/css';
							p.before(l);
							l.href = core.expandUrl(this.href);
						}
					});
					topic.trigger('load', e, $.extend(d, {
						title: doc.title || d.title || 'NetHelp',
						frame: ifr
					}));
					c1flip.container = body;
					if (!body.c1popupGlobals) {
						body.bind('mousedown keydown', function(e, d) {
							d = d || {};
							core.popup.globalEvent.call(this, e);
							d.frame = true;
							if (!d.main) {
								$('body').trigger(e.type, d);
							}
						});
						body.c1popupGlobals = true;
					}
					body.css({ position: 'relative', width: '100%', height: '100%' });
					body.find('#mainSection').css('position', 'relative');
					$.each(stg.importPlugins || [], function(i, name) {
						shell.plugin(name).create(body);
					});
					body.delegate('a, area, .topic-link, .external-link', 'click', function(e) {
						var el = $(this);
						if (el.is('.inline-text, .popup-text, .k-link, .a-link, .service')) {
							return;
						}
						var islink = el.is('a, area'),
							target = el.data('target') || islink && el.attr('target'),
							ref = el.data('ref'),
							href = !ref && el.attr('href');
						if ((ref || href || '').charAt(0) === '#') {
							if (ref) {
								el.attr('href', ref);
								el.removeData('ref');
							}
							return;
						}
						ref = ref || href;
						islink && e.preventDefault();
						if (ref) {
							ref = el.closest('.aklinks-menu, .popup-page').length ? ref :
								core.replaceUrl(ref, path);
							shell.topicLink(ref, {
								event: e,
								element: el,
								external: el.hasClass('external-link'),
								target: target
							});
						}
					});
					// fix cursor style for <area /> without href attribute
					body.find('area[data-ref]').each(function() {
						var el = $(this);
						el.attr('href', el.attr('data-ref'));
					});
					
					d.afterLoad = true;
					doc.inTopic = true;
					doc.title = idoc.title;
					doc.links = doc.find('link');
					topic.html(doc, e, d);
				});
				ifr[0].src = d.src;
			}
			else {
				c1flip.container = shell.topic.element;
			}
		});
		$('body').bind('mousedown keydown', function(e, d) {
			d = d || {};
			var ifr = $('#topic iframe.sandcastle')[0];
			if (ifr && !d.frame) {
				d.main = true;
				$('body', ifr.contentWindow.document).trigger(e.type, d);
			}
		});
		shell.print = function() {
			shell.trigger('beforeprint');
			var ifr = shell.topic.element.find('iframe.sandcastle')[0];
			(ifr && ifr.contentWindow || window).print();
			shell.trigger('afterprint');
		}
	}
});

})(jQuery, nethelp, nethelpshell);