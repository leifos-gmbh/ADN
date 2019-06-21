(function($, core, shell, undefined) {

/*
	widgetDescriptor: {
		name: string, (required)
		widgetName: string || this.name,
		check: function, (check whether an instance of this widget needs to be created)
		options: object|function|null
		create: function, (custom widget constructor, if defined)
		created: boolean, (set to true after widget is created, initialized, and registered)
		events: boolean|string || true, (flag indicating whether widget events need to be handled by the shell)
		init: function, (after create)
		nowait: boolean (default false) (if true, shell fires its ready event without waiting for this widget's ready event)
		ready: function, (fire on widget ready event)
		cancel: function, (fire on widget cancel event)
		finish: function, (fire when all widgets are created and ready)
		shellready: function, (fire when all drivers, widgets, and plugins are created and ready)
		remove: function (call when an already created widget is removed from collection)
	}
*/

var resolved = $.when();
// topic
// add settings-normalizator for option "replaceUrlElements"
shell.addSettingsNorm(function(s) {
	if ($.isArray(shell.setting('topic.options.replaceUrlElements.item'))) {
		var r = [];
		$.each(s.topic.replaceUrlElements.item, function(i, p) {
			r[i] = [ p.selector, p.attribute, p.not ];
		});
		s.topic.replaceUrlElements = r;
	}
});
var defStrTopicNotFound = 'Topic not found';
shell.mergeSettings({
	topic: {
		strings: {
			notfoundText: defStrTopicNotFound,
			notfoundTitle: defStrTopicNotFound
		}
	}
});
shell.widget({
	name: 'topic',
	options: function() {
		return shell.setting('topic.options');
	},
	init: function(topic) {
		var load = shell.loadTopic = function(url, e) {
			e = e && e.handled ? e : $.Event('unknown');
			if (!(e && e.isHandled('topicload')) && url) {
				url = core.normalizeHref(url);
				topic.abort();
				return topic.load(url, e);
			}
			return resolved;
		};
		shell.bind('tocselect navigatorchange', function(e, d) {
			if (d.url) {
				load(d.url, e);
			}
		});
		shell.bind('navigatorblank', function(e) {
			topic.html('', e);
			if (topic.options.updateTitle) {
				document.title = core.str(shell.setting('strings.title'), 'No title');
			}
		});
		shell.bind('topicloaderror', function(e, d) {
			if (d.status !== 'abort') {
				topic.html(core.str(shell.setting('topic.strings.notfoundText'), defStrTopicNotFound));
				if (topic.options.updateTitle) {
					document.title = core.str(shell.setting('topic.strings.notfoundTitle'), defStrTopicNotFound)
				}
			}
		});
	}
});
// toc
shell.widget({
	name: 'toc',
	options: function() {
		return shell.setting('toc.options');
	},
	init: function(toc) {
		var sync = shell.syncToc = function(url, e) {
			if (!(e && e.isHandled('tocselect')) && url) {
				return toc.select(url, e);
			}
			return resolved;
		};
		shell.bind('topicupdate', function(e, d) {
			if (d.afterLoad) {
				sync(d.query, e);
			}
			else {
				toc.deselect(e);
			}
		});
		shell.bind('topicloaderror', function(e, d) {
			if (d.status !== 'abort') {
				toc.deselect();
			}
		});
	}
});
// navigator
shell.widget({
	name: 'navigator',
	element: 'html',
	options: function() {
		var options = shell.settings.navigator || {};
		options.home = shell.setting('topic.home');
		return options;
	},
	init: function(navigator) {
		var navigate = shell.navigate = function(url, e) {
			if (!(e && e.isHandled('navigatorchange'))) {
				navigator.val({ url: url, isUrl: true }, e);
			}
		};
		shell.bind('topicload', function(e, d) {
			if (!d.error) {
				navigate(d.url, e);
			}
		});
	},
	shellready: function() {
		var w = this.instance,
			h = w.options.home,
			toc = shell.toc;
		if (!h && h !== false && toc) {
			h = toc.getFirst(true);
			h = h && h.data('url');
			w.options.home = h;
		}
		w.init();
	}
});
// index
shell.mergeSettings({
	index: {
		strings: {
			loading: 'Loading...',
			loaderror: 'Error: Index engine failed to load.',
			emptyFilter: 'To load index keywords, enter first character(s) of the keyword.',
			insufficientFilter: 'Insufficient filter, please enter more characters.',
			notfound: 'No keywords found.'
		}
	}
});
shell.widget({
	name: 'index',
	options: function() {
		return shell.setting('index.options');
	},
	init: function(index) {
		shell.bind('indexselect', function(e, d) {
			var links = index.getData(d.li).links;
			if (links.length === 1) {
				shell.loadTopic(links[0].url, e);
			}
			else if (links.length > 0) {
				var el = d.target;
				if (!shell.popup(el, 'toggle')) {
					var s = '<ul class="aklinks-menu">', l;
					for (var i in links) {
						l = links[i];
						s += '<li><a data-ref="' + l.url + '" class="k-link">' + l.text + '</a></li>';
					}
					s += '</ul>';
					var popup = shell.popup(el, { html: s }).popup;
					popup.delegate('a.k-link', 'click', function(e) {
						e.handled('indexselect');
						e.preventDefault();
						e.stopPropagation();
						popup.hide();
						shell.loadTopic($(this).data('ref'), e);
					});
				}
			}
		});
		shell.bind('topicupdate', function(e, d) {
			if (!e || !e.isHandled('indexselect')) {
				index.deselect();
			}
		});
		var indexElement = index.element;
		//#region messages
		var msgs = shell.setting('index.strings') || {},
			tmpl = '<li class="service" />';
		function showMsg(type, highlight) {
			var m = indexElement
				.children().hide()
				.filter('.' + type).show();
			if (!m.length) {
				m = $(tmpl).addClass(type)
					.html(core.str(msgs[type], type))
					.appendTo(indexElement);
			}
			if (highlight) {
				m.delay(10).effect('highlight', 1000);
			}
		}
		$.each('emptyFilter insufficientFilter notfound loading loaderror'.split(' '), function(i, type) {
			index.bind(type, function() {
				showMsg(type);
			});
		});
		index.bind('load loaderror', function() {
			indexElement.children('.loading').hide();
		});
		//#endregion
	}
});
// groups
shell.widget({
	name: 'groups',
	element: 'body',
	options: function() {
		return shell.setting('groups.options');
	}
});
// search
shell.mergeSettings({
	search: {
		strings: {
			helpMessage: 'You can use logical operations in the search string: #{and}, #{or}, #{not}.' + 
				' Examples: football #{or} hockey, sports #{and} #{not} baseball',
			loading: 'Loading search engine...',
			loaderror: 'Error: Search engine failed to load.',
			disabled: 'Search is disabled.',
			notfound: 'No topics found.',
			correcting: 'Did you mean: #{query}'
		},
		buttons: {
			go: {
				label: 'Search',
				icon: 'ui-icon ui-icon-search'
			},
			highlight: {
				label: 'Highlight search hits',
				icon: 'ui-icon ui-icon-lightbulb'
			},
			help: {
				label: 'Help',
				icon: 'ui-icon ui-icon-help'
			}
		}
	}
});
shell.widget({
	name: 'search',
	nowait: true,
	options: function() {
		return shell.setting('search.options') || {};
	},
	init: function(search) {
		var searchElement = search.element;
		shell.bind('searchselect', function(e, d) {
			shell.loadTopic(d.url, e);
		});
		shell.bind('topicupdate', function(e, d) {
			if (e && e.isHandled('searchselect')) {
				search.highlight();
			}
			else {
				search.deselect();
			}
		});
		//#region messages
		var msgs = shell.setting('search.strings') || {},
			tmpl = '<li class="service message" />';
		function correctingMsg(query) {
			return $(tmpl).html(core.str(msgs.correcting, '')
				.replace(/#{query}/gi, '<a class="correcting">' + query + '</a>'));
		}
		function showMsg(type, element, highlight) {
			if (element === true) {
				highlight = element;
				element = searchElement;
			}
			var m = $(tmpl).addClass(type)
				.html(core.str(msgs[type], type))
				.appendTo((element || searchElement).empty());
			if (highlight) {
				m.delay(10).effect('highlight', 1000);
			}
		}
		search.showMessage = showMsg;
		search.bind('disabled notfound loaderror', function(e, d) {
			showMsg(e.type.substring(search.widgetEventPrefix.length), d.element, d.highlight);
		})
		.bind('correcting', function(e, d) {
			(d.element || searchElement).append(correctingMsg(d.correcting));
		});
		if (!search.readyState()) {
			showMsg('loading', false);
		}
		search.ready(undefined, function() {
			showMsg('loaderror');
		});
		//#endregion
	},
	shellready: function() {
		shell.search.options.highlight.element = shell.topic.element;
	}
});

})(jQuery, nethelp, nethelpshell);