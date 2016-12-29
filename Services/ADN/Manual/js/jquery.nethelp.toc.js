(function($, core, undefined) {

//#region constants
var p = 'nethelp-toc',
	c_item = p + '-item',
	c_item_leaf = p + '-item-leaf',
	c_item_nonleaf = p + '-item-nonleaf',
	c_item_collapsed = p + '-item-collapsed',
	c_item_expanded = p + '-item-expanded',

	d_leaf = 'leaf',
	d_expanded = 'expanded',
	d_selected = 'selected',
	d_iconElement = 'iconElement',
	d_item = 'tocItem';
//#endregion

var Toc = core.widget('toc', {
	options: {
		dataPath: '',
		dataSource: 'toc.xml',
		dataType: 'xml',

		itemTemplate: '<li><div class="inner"><span class="nethelp-toc-icon"></span><a class="nethelp-toc-text" title="#{tooltip}">#{title}</a></div></li>',

		icons: {
			leaf: 'ui-icon ui-icon-carat-1-e',
			collapsed: 'ui-icon ui-icon-triangle-1-e',
			expanded: 'ui-icon ui-icon-triangle-1-se'
		},
		selectedClass: 'ui-corner-all ui-state-hover',

		itemInit: function(li, isParent, options) {
			var icon;
			li.data(d_iconElement, icon = li.find('> .inner > .nethelp-toc-icon'));
			icon.addClass(options.icons[isParent ? (options.expanded ? 'expanded' : 'collapsed') : 'leaf'] || '');
		},
		itemExpand: function(li, options) {
			li.data(d_iconElement)
				.removeClass(options.icons.collapsed || '')
				.addClass(options.icons.expanded || '');
		},
		itemCollapse: function(li, options) {
			li.data(d_iconElement)
				.removeClass(options.icons.expanded || '')
				.addClass(options.icons.collapsed || '');
		},
		itemSelect: function(li, options) {
			li.children('.inner')
				.addClass(options.selectedClass || '');
		},
		itemDeselect: function(li, options) {
			li.children('.inner')
				.removeClass(options.selectedClass || '');
		},

		expandAnimation: { duration: 0 },   // for example, { effect: "blind", easing: "easeOutExpo", duration: 200 }
		collapseAnimation: { duration: 0 }, // for example, { effect: "blind", easing: "easeOutExpo", duration: 200 }

		scrollup: {
			position: 'center',
			always: false
		}
	},
	_data: undefined,
	_create: function() {
		var self = this,
			_ready = self._Ready(),
			options = self.options,
			element = self.element,
			_data = options.dataType;
		element.addClass(p);
		//#region _data init
		self._data = _data = typeof _data === 'string' ?
			Toc.dataProviders[_data] :
			(_data && $.isFunction(_data.read) ? _data : undefined) || Toc.dataProviders['default'];
		if (typeof options.dataPath !== 'string') {
			options.dataPath = '';
		}
		//#endregion
		element.delegate('.nethelp-toc-icon', 'click', function(e) {
			if (e.target.nodeName.toLowerCase() === 'a') {
				e.preventDefault();
			}
			self.toggle($(e.target).closest('li'));
		});
		element.delegate('.nethelp-toc-text', 'click', function(e) {
			var target = $(e.target),
				isLink = e.target.nodeName.toLowerCase() === 'a',
				li = self.getItem(target),
				wnd = li && li.data(d_item).window;
			if (isLink) {
				e.preventDefault();
			}
			if (!li) {
				return;
			}
			if (li.data(d_selected) || !li.data('url')) {
				self.toggle(li);
			}
			else if (wnd) {
				self.trigger('open', e, { url: li.data('url'), target: wnd }) &&
					self.expand(li);
			}
			else {
				self.select(li);
				self.expand(li);
			}
		});
		self.queue(function(next) {
			self.trigger('initializing');
			_data.read(options.dataPath + options.dataSource, function(data, error) {
				if (error) {
					core.error('nethelp-toc error: ' + error.errorThrow);
					self.disable();
					_ready.cancel(error);
				}
				else {
					self._buildHtml(data, element);
					_ready.fire();
				}
				next();
			});
		});
	},

	_buildHtml: function(data, parent) {
		var self = this,
			options = self.options,
			expanded = !!options.expanded,
			tmpl = options.itemTemplate,
			init = options.itemInit || $.noop,
			_data = self._data,
			getObject = _data.getObject,
			getChildren = _data.getChildren;
		if (typeof tmpl === 'string') {
			var stmpl = tmpl;
			options.itemTemplate = tmpl = function(i) {
				return stmpl
					.replace(/#\{title\}/g, i.title)
					.replace(/#\{tooltip\}/g, i.tooltip);
			};
		}

		function f(ul, d, level) {
			var li, obj, children, isParent;
			for (var i = 0; (li = d[i]) && (obj = getObject(li)); ++i) {
				children = getChildren(li);
				obj.isParent = isParent = children && children.length;
				obj.level = level;
				li = $(tmpl(obj))
					.addClass(c_item)
					.appendTo(ul);
				if (obj.id) {
					li.attr('id', 'tocitem' + obj.id);
				}
				if (obj.url) {
					li.attr('data-url', obj.url);
					li.find('a.nethelp-toc-text').attr('href', obj.url);
				}
				li.data(d_item, {
					title: obj.title,
					tooltip: obj.tooltip,
					url: obj.url,
					id: obj.id,
					window: obj.window
				});
				if (isParent) {
					li.addClass(c_item_nonleaf).addClass(expanded ? c_item_expanded : c_item_collapsed);
					// TODO: options.expanded won't work in multi-file toc 
					expanded && li.data(d_expanded, true);
					f($('<ul/>')[expanded ? 'show' : 'hide']().appendTo(li), children, ++level);
				}
				else {
					li.addClass(c_item_leaf).data(d_leaf, true);
				}
				init(li, isParent, options);
			}
		}
		f(self.element, data, 0);
	},
	_animation: function(ul, show, callback) {
		var self = this,
			animation = this.options[show ? 'expandAnimation' : 'collapseAnimation'],
			duration = animation ? animation.duration : 0;
		ul[show ? 'show' : 'hide']
			.apply(ul, ($.effects && duration ? [ animation.effect, {} ] : [])
				.concat([ duration, function() {
					core.call(callback, self);
				}]));
	},

	getItem: function(li) {
		if (!li) {
			return null;
		}
		li = li.nodeType ? $(li) : li;
		if (li.length > 1) {
			li = $(li[0]);
		}
		return (li = li.closest('li', this.element)).length ? li : null;
	},
	getData: function(li) {
		li = this.getItem(li);
		return li ? li.data(d_item) : null;
	},
	expand: function(li, event, params) {
		if (!params || typeof params !== 'object') {
			params = {};
		}
		var self = this,
			options = self.options,
			d;
		li = self.getItem(li);
		d = li.data(d_expanded);
		if (li && !self.options.disabled && !li.data(d_leaf) && !d) {
			if (!params.inQueue && !params.jumpQueue) {
				params.inQueue = true;
				self.queue(self.expand, li, event, params);
				return self;
			}
			li.data(d_expanded, 'inprogress');
			$.extend(params, { li: li });
			if (self.trigger('expanding', event, params) === false) {
				return self;
			}
			options.itemExpand(li, options);
			self._animation(li.children('ul'), true, core.concat(params.callback, function() {
				li.data(d_expanded, true);
				li.removeClass(c_item_collapsed).addClass(c_item_expanded);
				self.trigger('expand', event, params);
			}));
		}
		return self;
	},
	collapse: function(li, event, params) {
		if (!params || typeof params !== 'object') {
			params = {};
		}
		var self = this,
			options = self.options,
			d;
		li = self.getItem(li);
		d = li.data(d_expanded);
		if (li && !self.options.disabled && !li.data(d_leaf) && d && d !== 'inprogerss') {
			if (!params.inQueue && !params.jumpQueue) {
				params.inQueue = true;
				self.queue(self.collapse, li, event, params);
				return self;
			}
			li.data(d_expanded, 'inprogress');
			$.extend(params, { li: li });
			if (self.trigger('collapsing', event, params) === false) {
				return self;
			}
			options.itemCollapse(li, options);
			self._animation(li.children('ul'), false, core.concat(params.callback, function() {
				li.removeData(d_expanded);
				li.removeClass(c_item_expanded).addClass(c_item_collapsed);
				self.trigger('collapse', event, params);
			}));
		}
		return self;
	},
	toggle: function(li, event, params) {
		return this[li.data(d_expanded) ? 'collapse' : 'expand'](li, event, params);
	},
	expandAll: function(li, event, params) {
		if (!params || typeof params !== 'object') {
			params = {};
		}
		var self = this,
			ul = li && (li = self.getItem(li)) && li.children('ul') || self.element;
		params.originalJumpQueue = params.jumpQueue;
		params.jumpQueue = true;
		ul.children().each(function() {
			self.expand(this, event, params);
			self.expandAll(this);
		});
	},
	collapseAll: function(li, event, params) {
		if (!params || typeof params !== 'object') {
			params = {};
		}
		var self = this,
			ul = li && (li = self.getItem(li)) && li.children('ul') || self.element;
		params.originalJumpQueue = params.jumpQueue;
		params.jumpQueue = true;
		ul.children().each(function() {
			self.collapseAll(this);
			self.collapse(this, event, params);
		});
	},
	select: function(li, event, params) {
		if (!params || typeof params !== 'object') {
			params = {};
		}
		var self = this,
			options = self.options,
			element = self.element;
		if (li == undefined) {
			return self.deselect(event, params);
		}
		if (params.queue !== false && !params.inQueue) {
			self.queue(function() {
				params.inQueue = true;
				self.select(li, event, params);
			});
			return $.when(element, self._request);
		}
		if (typeof li === 'string') {
			return self.select(self.findByUrl(li), event, params);
		}
		self.deselect(event, params);
		li = self.getItem(li);
		if (li) {
			if (!('expandParents' in params)) {
				params.expandParents = true;
			}
			if (li && params.expandParents) {
				var items = [], i = li;
				while ((i = i.parent().closest('li', element)).length) {
					items.unshift(i);
				}
				for (i = 0; i < items.length; ++i) {
					self.expand(items[i], event, params);
				}
			}
			var data = $.extend(params, { li: li, url: li.data('url') });
			if (li && !li.data(d_selected) && self.trigger('selecting', event, data)) {
				options.itemSelect(li, options);
				li.data(d_selected, true);
				self._selected = li;
				if (li.scrollup && options.scrollup !== false) {
					li.find('.nethelp-toc-text').scrollup(options.scrollup);
				}
				self.trigger('select', event, data);
			}
		}
		return $.when();
	},
	deselect: function(event, params) {
		if (!params || typeof params !== 'object') {
			params = {};
		}
		var self = this,
			options = self.options,
			li = self._selected;
		if (li && li.data(d_selected)) {
			options.itemDeselect(li, options);
			li.removeData(d_selected);
			delete self._selected;
			self.trigger('deselect', event, $.extend(params, { li: li }));
		}
	},
	getSelected: function() {
		return this._selected;
	},
	findByUrl: function(url, context) {
		url = url.split('#')[0]; // trim url-hash
		return (context && $(context) || this.element).find('li[data-url="' + core.escapeAttr(url.toLowerCase()) + '"]:first');
	},
	getNext: function(hasUrl, rel) {
		if (hasUrl && typeof hasUrl !== 'boolean') {
			rel = hasUrl;
			hasUrl = false;
		}
		var self = this,
			element = self.element,
			li = rel && self.getItem(rel) || self._selected;
		if (!li) {
			return null;
		}
		else {
			var el = li.children('ul').children()[0];
			if (!el) {
				while(!(el = li.next()[0])) {
					li = li.parent().closest('li', element);
					if (!li.length) {
						el = undefined;
						break;
					}
				}
			}
			li = el ? $(el) : undefined;
		}
		return li && li.length ? 
			(hasUrl && !li.data('url') ? self.getNext(hasUrl, li) : li) : 
			null;
	},
	getPrev: function(hasUrl, rel) {
		if (hasUrl && typeof hasUrl !== 'boolean') {
			rel = hasUrl;
			hasUrl = false;
		}
		var self = this,
			element = self.element,
			li = rel && self.getItem(rel) || self._selected;
		if (!li) {
			return null;
		}
		else {
			var el = li.prev();
			if (el.length) {
				while ((li = el.children('ul').children(':last')).length) {
					el = li;
				}
				li = el;
			}
			else {
				el = li.parent().closest('li', element);
				li = el.length ? el : undefined;
			}
		}
		return li && li.length ? 
			(hasUrl && !li.data('url') ? self.getPrev(hasUrl, li) : li) : 
			null;
	},
	getFirst: function(hasUrl) {
		var self = this,
			li = self.element.children(':first');
		return li && li.length ? 
			(hasUrl && !li.data('url') ? self.getNext(hasUrl, li) : li) : 
			null;
	},
	gotoNext: function(event, params) {
		params = params || {};
		var self = this,
			li = self.getNext(params.withUrl !== false);
		if (li) {
			self.select(li, event, params);
		}
	},
	gotoPrev: function(event, params) {
		params = params || {};
		var self = this,
			li = self.getPrev(params.withUrl !== false);
		if (li) {
			self.select(li, event, params);
		}
	},
	getBreadcrumbs: function(andSelected) {
		var self = this,
			res = [],
			li = self._selected,
			i;
		if (!andSelected) {
			li = li.parent().closest('li', self.element);
		}
		while (li && li.length) {
			i = self.getData(li);
			res.unshift({
				url: i.url,
				title: i.title,
				tooltip: i.tooltip,
				li: li
			});
			li = li.parent().closest('li', self.element);
		}
		return res;
	}
}); // widget

//#region dataProviders
Toc.dataProviders = {
	json: $.extend(true, {}, core.dataProviders.json),
	xml: $.extend(true, {}, core.dataProviders.xml, {
		success: function(resp, status, request) {
			return $('toc', resp).children();
		},
		getObject: function(item) {
			item = $(item);
			return {
				title: item.children('title').text() || item.attr('title') || '',
				tooltip: item.children('tooltip').text() || item.attr('tooltip') || '',
				id: item.attr('id') || '',
				url: (item.attr('url') || ''),
				window: item.attr('window')
			};
		},
		getChildren: function(item) {
			item = $(item);
			return item.attr('items-file') || item.children('item');
		}
	})
};
Toc.dataProviders['default'] = Toc.dataProviders.xml;
//#endregion

})(jQuery, nethelp);