(function($, core, shell, undefined) {

/*
	pluginDescriptor: {
		name: string, (required)
		create: function,
		created: boolean,
		remove: function
	}
*/

//#region helpers
function getRefElement(el, context, cache) {
	var ref = el.data('ref');
	if (typeof ref === 'string') {
		ref = $(ref, context);
		if (cache !== false) {
			el.data('ref', ref);
		}
	}
	return ref;
}
var flip = $.ui.position.flip;
$.ui.position.c1flip = {
	left: function(position, data) {
		var t = $.ui.position.c1flip;
		min = (t.container || shell.topic.element).offset().left;
		flip.left(position, data);
		if (position.left < min) {
			position.left = min;
		}
	},
	top: function(position, data) {
		var t = $.ui.position.c1flip;
		min = (t.container || shell.topic.element).offset().top;
		flip.top(position, data);
		if (position.top < min) {
			position.top = data.collisionPosition.top;
		}
	}
};
shell.ready(function() {
	$.ui.position.c1flip.container = shell.topic.element;
});
function popupInTopicOptions(options) {
	return $.extend(true, {
		popupCss: {
			maxHeight: 400,
			overflow: 'auto'
		},
		position: {
			collision: 'c1flip'
		}
	}, options || {});
}
//#endregion

// inline-text
shell.plugin({
	name: 'inlineText',
	create: function(topicElement) {
		topicElement = topicElement ? $(topicElement) : $('body');
		if (topicElement) {
			topicElement.delegate('.inline-text:not(.service)', 'click', function(e) {
				getRefElement($(this), topicElement).toggle();
			});
		}
	}
});
// popup-text
shell.plugin({
	name: 'popupText',
	create: function(topicElement) {
		topicElement = topicElement ? $(topicElement) : $('body');
		if (topicElement) {
			topicElement.delegate('.popup-text:not(.service)', 'click keydown', function(e) {
				var isclick = e.type === 'click';
				if (isclick || e.which === 13) {
					var el = $(this);
					if (!shell.popup(el, 'toggle')) {
						var s = el.data('ref');
						if (s) {
							s = 'title' === s ? el.attr('title') : $(s).html();
							if (s) {
								shell.popup(el, { html: s }).popup.addClass('nethelp-topic-popup');
							}
						}
					}
				}
			});
		}
	}
});
// a/k-links
$.each({ index: 'k-link', groups: 'a-link' }, function(widget, plugin) {
	shell.plugin({
		name: plugin,
		create: function(topicElement) {
			function genAKLinksHtml(links, options) {
				if (typeof options === 'string') {
					options = { tmpl: options };
				}
				options = options || {};
				var s = '<ul class="aklinks-menu">',
					tmpl = options.tmpl || '<li><a href="#{url}"#{target}>#{text}</a></li>';
				if (typeof tmpl === 'string') {
					var stmpl = tmpl;
					tmpl = function(l, o) {
						var t = o.target || l.target;
						return stmpl
							.replace(/#{url}/, l.url)
							.replace(/#{text}/, l.text)
							.replace(/#{target}/, t ? (' target="' + t + '"') : '');
					}
				}
				for (var i in links) {
					s += tmpl(links[i], options);
				}
				return s + '</ul>';
			}
			topicElement = topicElement ? $(topicElement) : $('body');
			if (topicElement) {
				topicElement.delegate('.' + plugin + ':not(.service)', 'click', function(e) {
					e.preventDefault();
					var w = shell[widget];
					if (w) {
						var el = $(this),
							key = el.data('ref'),
							target = el.data('target') || el.attr('target');
						w.find(key, function(links) {
							if (links.length === 1) {
								shell.topicLink(links[0].url, {
									target: target,
									element: el,
									event: e
								});
							}
							else if (links.length > 1) {
								if (!shell.popup(el, 'toggle')) {
									shell.popup(el, popupInTopicOptions({ html: genAKLinksHtml(links, { target: target }) }));
								}
							}
						});
					}
				});
			}
		}
	});
});
// links in topic
shell.mergeSettings({
	topic: {
		externalLinkTarget: '_blank'
	},
	windows: {
		secondary: 'left=100,top=100,width=800,height=600'
	}
});
function normalizeSettingsWindows() {
	var wins = shell.settings.windows,
		t = $.type(wins),
		torig = t;
	if (t === 'object') {
		wins = wins.window;
		if (wins && ($.isArray(wins) || wins.name)) {
			wins = $.makeArray(wins);
			t = 'array';
		}
		else {
			return;
		}
	}
	if (t === 'array') {
		t = {};
		$.each(wins, function(i, v) {
			var name = core.str(v.name, '');
			if (name) {
				t[name] = v.features;
			}
		});
		if (torig === 'object') {
			delete shell.settings.windows.window;
			shell.mergeSettings({ windows: t });
		}
		else {
			shell.settings.windows = t;
		}
	}
	else {
		shell.settings.windows = {};
	}
}
shell.plugin({
	name: 'topicLinks',
	create: function() {
		shell.normalizeSettingsWindows = normalizeSettingsWindows;
		normalizeSettingsWindows();
		var rIsId = /^\w+$/,
			namesCache = {},
			guid = 189543;
		function popupLink(el, ref) {
			var w = shell.popup(el, 'toggle');
			if (w && !w.state()) {
				return;
			}
			var popup = w && w.popup,
				hash = ref.split('#');
			ref = hash[0];
			hash = hash[1];
			function toAnchor() {
				if (hash) {
					var anchor = popup.findAnchor(hash);
					if (anchor.length) {
						anchor.scrollup({ parent: popup });
					}
				}
			}
			if (w) {
				w.onShow(toAnchor);
			}
			else {
				var c = $('<p/>'),
					part = el.data('part');
				$.ajax({
					url: ref,
					dataType: 'text',
					success: function(resp, status, req) {
						var c = $('<p/>').html(core.escapeJS(resp));
						if (part) {
							c = c.find(part);
						}
						c.find('script[type="_js"], style, link[rel~="stylesheet"]').remove();
						var html = c.html(),
							path;
						if (html) {
							path = core.getUrlPath(ref);
							w = shell.popup(el, { html: html });
							popup = w.popup;
							w.onShow(toAnchor);
							core.replaceUrls(popup, shell.topic.options.replaceUrlElements, path);
							popup.addClass('nethelp-topic-popup popup-page');
						}
					}
				});
			}
		}
		function winName(name, prefix) {
			return rIsId.test(name) ? name :
				namesCache[name] || (namesCache[name] = prefix + guid++);
		}
		var topicLink = shell.topicLink = function(ref, options) {
			/*
				options: {
					target,
					element,
					event,
					external
				}
			*/
			if (!ref || typeof ref !== 'string') {
				return;
			}
			options = options || {};
			var target = options.target,
				el = options.element,
				e = options.event,
				abs = ref && core.isAbsoluteUrl(ref),
				win = target && target.charAt(0) !== '_' && wins[target];
			if (!abs && !options.external) {
				// topic-link
				if (target === 'popup' && el) {
					popupLink(el, ref);
					return;
				}
				else if (!target) {
					shell.loadTopic(ref, e);
					return;
				}
				else if (win) {
					ref = core.addUrlSearch(ref, 'topiconly=true');
				}
			}
			else if (!target) {
				target = core.str(shell.setting('topic.externalLinkTarget'));
			}
			if (win) {
				window.open(ref, winName(target, 'swindowName'), win);
			}
			else if (target) {
				window.open(ref, winName(target, 'windowName'));
			}
			else {
				window.open(ref);
			}
		};
		var topic = shell.topic,
			ignore = '.inline-text, .popup-text, .k-link, .a-link, .service';
		if (topic) {
			// block handle links in widget topic
			topic.options.linkCheck = function() { return false; };
			topic.options.replaceUrlElements.push([
				'a, area', 'data-ref', ignore
			]);
			var wins = shell.settings.windows || {};
			var handler = function(e) {
				var el = $(this);
				if (el.is(ignore)) {
					return;
				}
				var href = el.is('a, area'),
					target = el.data('target') || href && el.attr('target'),
					ref = el.data('ref') || href && core.normalizeHref(el, true);
				href && e.preventDefault();
				ref && topicLink(ref, {
					event: e,
					element: el,
					external: el.hasClass('external-link'),
					target: target
				});
			};
			topic.element.delegate('a, area, .topic-link, .external-link', 'click', handler);
			$('body').delegate('.nethelp-popup a, .nethelp-popup area, .nethelp-popup .topic-link, .nethelp-popup .external-link', 'click', handler);
			shell.bind('tocopen', function(e, d) {
				topicLink(d.url, { target: d.target });
			});
			shell.bind('topicupdate', function() {
				// fix cursor style for <area /> without href attribute
				topic.element.find('area[data-ref]').each(function() {
					var el = $(this);
					el.attr('href', el.attr('data-ref'));
				});
				// fix: Backspace key does not work in IE after clicking a link in topic text
				$('body').focus();
			});
		}
	}
});
// goto Prev/Next/Home functions
shell.plugin({
	name: 'goto_',
	create: function() {
		var topic = shell.topic,
			toc = shell.toc,
			buttons = shell.buttons;
		$.each({ gotoPrev: 'prev', gotoNext: 'next' }, function(name, data) {
			shell[name] = function() {
				var url = (topic.getData() || {})[data];
				if (url) {
					shell.loadTopic(url);
				}
				else {
					toc[name]();
				}
			};
		});
		shell.gotoHome = function() {
			var navigator = shell.navigator;
			navigator && navigator.home();
		};
		shell.bind('topicupdate', function() {
			buttons.btnGotoPrev
				.button((topic.getData() || {})['prev'] || toc.getPrev() ? 'enable' : 'disable')
				.removeClass('ui-state-hover');
			buttons.btnGotoNext
				.button((topic.getData() || {})['next'] || toc.getNext() ? 'enable' : 'disable')
				.removeClass('ui-state-hover');
		});
	}
});
//#region collapsible sections
shell.mergeSettings({
	topic: {
		collapsibleSections: {
			expanded: {
				icon: "ui-icon ui-icon-circle-triangle-n"
			},
			collapsed: {
				icon: "ui-icon ui-icon-circle-triangle-s"
			},
			expandAll: {
				icon: "ui-icon ui-icon-circle-triangle-s",
				label: undefined
			},
			collapseAll: {
				icon: "ui-icon ui-icon-circle-triangle-n",
				label: undefined
			},
			highlight: undefined
		}
	}
});
shell.plugin({
	name: 'collapsibleSection',
	create: function() {
		var topic = shell.topic,
			topicElement = topic.element,
			panel = $('#collapsibleAll'),
			stg = shell.setting('topic.collapsibleSections') || {},

			c_header = 'collapsible-header',
			c_section = 'collapsible-section',
			c_highlight = core.str(shell.setting(stg, 'highlight')),
			ic_expanded = core.str(shell.setting(stg, 'expanded.icon')),
			ic_collapsed = core.str(shell.setting(stg, 'collapsed.icon'));
		function cookieName(id) {
			return encodeURIComponent(((topic.getData() || {}).query || '').toLowerCase() + id);
		}
		function toggle(el, expand, store) {
			var elems = el.children(),
				header = elems.eq(0),
				section = elems.eq(1),
				icon = header.children('.icon'),
				id = el.attr('id');
			if (expand == undefined) {
				expand = !el.hasClass('expanded');
			}
			el.toggleClass('expanded', expand)
				.toggleClass('collapsed', !expand);
			icon.removeClass(expand ? ic_collapsed : ic_expanded)
				.addClass(expand ? ic_expanded : ic_collapsed);
			section.toggle(expand);
			if (store !== false && $.cookie && id) {
				$.cookie(cookieName(id),
					expand ? true : false, { expires: 365 });
			}
		}
		function createCollapsible(el, expand) {
			var elems = el.children(),
				header = elems.eq(0),
				section = elems.eq(1),
				icon, id;
			if (header.length && section.length) {
				header.addClass(c_header);
				if (header.attr('tabindex') == undefined) {
					header.attr('tabindex', 0);
				}
				section.addClass(c_section);
				icon = header.children('.icon');
				if (!icon.length) {
					icon = $('<span class="icon"/>').prependTo(header);
				}
				id = el.attr('id');
				if ($.cookie && id) {
					switch ($.cookie(cookieName(id))) {
						case 'true':
							expand = true;
							break;
						case 'false':
							expand = false;
							break;
						default:
							expand = undefined;
					}
				}
				if (c_highlight) {
					header.hover(function() {
						section.addClass(c_highlight);
					}, function() {
						section.removeClass(c_highlight);
					});
				}
				header.bind('click keydown', function(e) {
					if (e.type === 'click' || e.which == 13) {
						toggle($(this).parent());
					}
				});
				toggle(el, expand == undefined ? el.hasClass('expanded') : expand, false);
			}
		}
		function expandAll() {
			topicElement.find('.collapsible').each(function() {
				toggle($(this), true);
			});
		};
		function collapseAll() {
			topicElement.find('.collapsible').each(function() {
				toggle($(this), false);
			});
		};
		shell.expandAll = expandAll;
		shell.collapseAll = collapseAll;
		$('#expandAll').click(expandAll)
			.children()
			.filter('.icon').addClass(core.str(shell.setting(stg, 'expandAll.icon'), ''))
			.end()
			.filter('.label').text(core.str(shell.setting(stg, 'expandAll.label')));
		$('#collapseAll').click(collapseAll)
			.children()
			.filter('.icon').addClass(core.str(shell.setting(stg, 'collapseAll.icon'), ''))
			.end()
			.filter('.label').text(core.str(shell.setting(stg, 'collapseAll.label')));
		shell.bind('topicupdate', function() {
			var len = topicElement.find('.collapsible').each(function() {
				createCollapsible($(this));
			}).length;
			panel.toggle(!!len);
		});
	}
});
//#endregion
//#region related topics
shell.mergeSettings({
	topic: {
		relatedTopics: {
			icon: "ui-icon ui-icon-arrowreturnthick-1-e"
		}
	}
});
shell.plugin({
	name: 'relatedTopics',
	create: function() {
		var c_icon = core.str(shell.setting('topic.relatedTopics.icon')),
			topicElement = shell.topic.element;
		shell.bind('topicupdate', function() {
			topicElement.find('.related-topics').children().each(function() {
				$(this).css('clear', 'left');
				$('<span/>')
					.addClass('related-topic-icon')
					.addClass(c_icon)
					.prependTo(this);
			});
		});
	}
});
//#endregion
//#region context-sensitive
shell.mergeSettings({
	context: {
		dataPath: '',
		dataSource: 'context.xml',
		dataType: 'xml',
		strings: {
			title: 'Topics for "#{key}": "#{value}"',
			notfound: 'No topics found.',
			notsupported: 'The key "#{key}" is not supported.'
		}
	}
});
shell.plugin({
	name: 'contextSensitive',
	create: function() {
		var ids = {},
			options = shell.settings.context || {},
			strings = options.strings || {};
		// xmlDataProvider
		var _data = $.extend(true, core.dataProviders['xml'], {
			success: function(resp, status, request) {
				var r = {};
				$('context', resp).children().each(function() {
					var i = $(this),
						url = i.attr('url');
					if (url) {
						i.children('id').each(function() {
							var id = $(this).text();
							if (id) {
								r[id] = url;
							}
						});
					}
				});
				return r;
			}
		});
		shell.bind('navigatorchange', function(e, d) {
			if (d.isQuery && d.key && d.value) {
				var toc = shell.toc,
					topic = shell.topic,
					index = shell.index,
					groups = shell.groups,
					search = shell.search,
					key = d.key,
					value = d.value,
					t,
					html = '';
				function linksHandler(links) {
					if (links.length === 1) {
						shell.loadTopic(links[0].url);
					}
					else if (links.length > 0) {
						var s = '<ul>';
						for (var i = 0, l; (l = links[i]); ++i) {
							s += '<li><a href="' + l.url + '">' + l.text + '</a></li>';
						}
						topic.html(s + '</ul>');
					}
					else {
						topic.html(core.str(strings.notfound, ''));
					}
				}
				if (topic.options.updateTitle) {
					document.title = core.str(strings.title, '')
						.replace(/#{key}/g, key)
						.replace(/#{value}/g, value);
				}
				toc && toc.deselect();
				switch (key.toLowerCase()) {
					case 'keyword':
						index && index.find(value, linksHandler, false);
						break;
					case 'group':
						groups && groups.find(value, linksHandler, false);
						break;
					case 'id':
						t = ids[value];
						if (t) {
							shell.loadTopic(t);
						}
						else {
							topic.html(core.str(strings.notfound, ''));
						}
						break;
					case 'search':
						if (search) {
							if (!search.readyState()) {
								topic.html($('<p/>').html(core.str(shell.setting('search.strings.loading'), '')));
							}
							search.ready(function() {
								t = $('<ul />');
								if (search.options.disabled) {
									t = $('<ul/>');
									search.trigger('disabled', undefined, { element: t, highlight: false });
									topic.html(t);
								}
								else {
									search.search(value, t, function() {
										t.find('li.service a').each(function() {
											var el = $(this);
											el.addClass('service')
												.attr('href', '#?search=' + encodeURIComponent(el.text()));
										});
										topic.html(t);
									});
								}
							}, function() {
								t = $('<ul/>');
								search.trigger('loaderror', undefined, { element: t, highlight: false });
								topic.html(t);
							});
						}
						break;
					default:
						topic.html(core.str(strings.notsupported, '').replace(/#{key}/g, key));
						break;
				}
			}
		});
		return _data.read(options.dataPath + options.dataSource, function(data, error) {
			if (!error) {
				ids = data;
			}
		});
	}
});
//#endregion

})(jQuery, nethelp, nethelpshell);