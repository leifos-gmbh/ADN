(function($, core, undefined) {

//#region constants
var p = 'nethelp-index',
	c_item = p + '-item',
	d_item = 'indexItem';
//#endregion

//#region Helpers
function normKeyword(kw) {
	if ($.isArray(kw)) {
		$.each(kw, function(i, v) { kw[i] = normKeyword(v); });
		return kw;
	}
	return kw && $.trim(kw.replace(/\s+/g, ' ')).toLowerCase();
}
function quickSearch(array, check, fi, li) {
	if (!array || !array.length || !$.isFunction(check)) {
		return -1;
	}
	if (fi == undefined) {
		fi = 0;
		li = array.length - 1;
	}
	var i, r, l = array.length;
	while (l--) {
		i = parseInt((fi + li) / 2);
		r = check(array[i]);
		if (r === -1) {
			fi = i + 1;
		}
		else if (r === 1) {
			li = i - 1;
		}
		else if (r === 0) {
			return i;
		}
		else {
			return -Math.abs(r); // error code
		}
		if (fi === li) {
			return check(array[li]) === 0 ? li : -1;
		}
		else if (fi > li) {
			return -1;
		}
	}
	return -1;
}
function parseKeywords(str, multi) {
	/*
		if multi !== false return [ [ , ], ... ]; - array of keyword paths
		else return [ , ]; - keyword-path
	*/
	if (!str) {
		return [];
	}
	multi = multi !== false;
	function replaceEscaped(match, esc, ch) {
		esc = esc || '';
		return esc.length % 2 ? (esc.substring(1) + ch) : (esc + spl);
	}
	var spl = '\0',
		arr = multi ? $.map(str.replace(/(\\*)(\+)/g, replaceEscaped).split(spl), 
			function(v) { return v ? v : undefined; }).sort() : [ str ];
	$.each(arr, function(i, s) {
		arr[i] = s ? s.replace(/(\\*)(\,)/g, replaceEscaped)
			.replace(/\\\\/g, '\\').split(spl) : undefined;
	});
	return multi ? arr : arr[0];
}
function equalKeywords(kw1, kw2) {
	if ($.isArray(kw1) && $.isArray(kw2)) {
		if (kw1.length === kw2.length) {
			var res = true;
			$.each(kw1, function(i, v) {
				if (!equalKeywords(v, kw2[i])) {
					return res = false;
				}
			});
			return res;
		}
		return false;
	}
	return kw1 === kw2;
}
//#endregion

//#region Map
function IndexMap(data) {
	if (!this.find) {
		return new IndexMap(data);
	}
	this._data = data;
}
IndexMap.prototype = {
	constructor: IndexMap,
	find: function(key, internal) {
		// return "data-file-url" | 0: same as last | -2: insufficient data | -1: not found;
		var self = this,
			last = self._last,
			lastr;
		if (!self._data || !self._data.length) {
			return -1;
		}
		key = internal ? key : normKeyword(key);
		if (last) {
			lastr = self.check(key, last);
			switch (lastr) {
				case 0:
				case -2:
					return lastr;
			}
		}
		lastr = quickSearch(self._data, function(item) { return self.check(key, item); });
		if (lastr < 0) {
			return lastr; 
		}
		last = self._last = self._data[lastr];
		return last && last.url || -1;
	},
	check: function(key, item) {
		// return 0: match, 1: item > key, -1: item < key, -2: can't compare
		var start = item.start,
			end = item.end;
		if (key.indexOf(start) === 0 || end && key > start && key <= end) {
			return 0;
		}
		if (start.indexOf(key) === 0) {
			return -2;
		}
		if (key < start) {
			return 1;
		}
		if (end && key > end || key > start) {
			return -1;
		}
		return 0;
	}
};
//#endregion

//#region DataSource
function IndexDataSource(data) {
	if (!this.find) {
		return new IndexDataSource();
	}
	if (data.map) {
		this._map = new IndexMap(data.map);
		this._dataProvider = data.dataProvider;
	}
	else {
		this._data = data.items;
	}
}
IndexDataSource.prototype = {
	constructor: IndexDataSource,
	check: function(key, item) {
		var ikey = item.key;
		return ikey > key ? 1 : ikey < key ? -1 : 0;
	},
	// find keyword-item by key (text)
	find: function(key, callback, multi) {
		/*
			callback: function(links-array)
			multi: (default: true) allow multikeywords
			return jQuery.Deferred (.resolve(links-array))
		*/
		var self = this,
			t, keytext,
			r = [],
			d = $.when();
		key = normKeyword(key);
		if (typeof key === 'string') {
			t = self._lastFind;
			if (t && t.key === key) {
				return d.pipe(function() {
					return t.result;
				}).done(callback);
			}
			keytext = key;
			// trim whitespaces after splitting
			key = normKeyword(parseKeywords(key, multi !== false));
		}
		$.each(key, function(i, kw) {
			var len = kw && kw.length,
				first = len && kw[0];
			if (first) {
				d = d.pipe(function() {
					return self.loadData(first, function(result) {
						var pkw = first,
							check = function(i) {
								return self.check(pkw, i);
							};
						for (var c = 1; pkw && c < len && $.isArray(result); ++c) {
							// results -2 and -1 are ignored
							result = result[quickSearch(result, check)];
							if (result) {
								result = result.items;
								pkw = kw[c];
							}
						}
						result = $.isArray(result) && result[quickSearch(result, check)];
						if (result) {
							self.appendLinks(r, result.links);
						}
					}, true);
				});
			}
		});
		d = d.pipe(function() {
			return r;
		});
		if (keytext) {
			d.done(function(result) {
				self._lastFind = {
					key: keytext,
					result: result
				};
			});
		}
		return d.done(callback);
	},
	appendLinks: function(array, appendArray) {
		var f, a, len = array.length;
		for (var i in appendArray) {
			f = true;
			a = appendArray[i];
			for (var j = 0; j < len; ++j) {
				if (array[j].url === a.url) {
					f = false;
					break;
				}
			}
			if (f) {
				array.push(a);
			}
		}
	},
	loadData: function(key, callback, internal) {
		/*
			callback: function(items|-2|-1|0)
			return jQuery.Deferred (.resolve(items|-2|-1|0))
		*/
		var self = this,
			t,
			d = $.when();
		key = internal ? key : normKeyword(key);
		t = 0;
		if (self._map) {
			t = self._map.find(key, true);
			if (typeof t === 'string') {
				d = d.pipe(function() {
					return self._dataProvider.read(t, function(data) {
						self._data = data.items;
					});
				});
				if (typeof internal === 'object') {
					internal.loading = true;
				}
				t = 0;
			}
		}
		d = d.pipe(function() {
			return t || self._data;
		});
		return d.done(callback);
	}
};
//#endregion

var Index = core.widget('index', {
	options: {
		dataPath: '',
		dataSource: 'keywords.xml',
		dataType: 'xml',

		itemTemplate: '<li><a class="nethelp-index-text">#{text}</a></li>',
		selectedClass: p + '-selected',

		inputElement: undefined,
		inputEvents: 'keyup',
		inputDelay: 600,

		allowSecondaryKeywords: false
	},
	_create: function() {
		var self = this,
			_ready = self._Ready(),
			options = self.options,
			element = self.element,
			inputElement = options.inputElement,
			_data;
		element.addClass(p);
		//#region _data init
		if (typeof options.dataPath !== 'string') {
			options.dataPath = '';
		}
		_data = typeof _data === 'string' ?
			Index.dataProviders[_data] :
			(_data && $.isFunction(_data.read) ? _data : undefined) || Index.dataProviders['default'];
		// for groups widget
		_data = $.extend(true, {}, _data);
		//#endregion
		element.delegate('.nethelp-index-text', 'click', function(e) {
			var target = $(this),
				li = self.getItem(target);
			e.preventDefault();
			self.deselect();
			target.addClass(options.selectedClass);
			self.trigger('select', e, { li: li, target: target });
		});
		//#region input init
		if (!inputElement) {
			inputElement = element.data('input');
		}
		if (inputElement) {
			if (typeof inputElement === 'string' || inputElement.nodeType) {
				inputElement = $(inputElement);
			}
			if (inputElement.jquery) {
				if (inputElement.length > 1) {
					inputElement = $(inputElement[0]);
				}
				self.input = inputElement;
				var f = function() {
					self.filter(inputElement.val());
				};
				if (options.inputDelay) {
					var timerid = 0;
					inputElement.bind(options.inputEvents, function(e) {
						if (e.which === 13) {
							e.preventDefault();
							return;
						}
						clearTimeout(timerid);
						timerid = setTimeout(f, options.inputDelay);
					});
				}
				else {
					inputElement.bind(options.inputEvents, f);
				}
			}
		}
		//#endregion
		self.queue(function(next) {
			self.trigger('initializing');
			_data.read(options.dataPath + options.dataSource, function(data, error) {
				if (error) {
					core.error('nethelp-index error: ' + error.errorThrow);
					self.disable();
					_ready.cancel(error);
				}
				else {
					data.dataProvider = _data;
					self.dataSource = new IndexDataSource(data);
					self.modeMap = !!data.map;
					if (!data.map) {
						element.empty();
						self._buildHtml(data.items);
					}
					_ready.fire();
				}
				next();
			});
		});
	},
	_normalizeKeyword: normKeyword,
	_buildHtml: function(data) {
		var self = this,
			options = self.options,
			tmpl = options.itemTemplate;
		if (typeof tmpl === 'string') {
			var stmpl = tmpl;
			options.itemTemplate = tmpl = function(i) {
				return stmpl.replace(/#\{text\}/g, i.text);
			};
		}

		function f(ul, d, level) {
			var li, obj;
			for (var i = 0; (obj = d[i]); ++i) {
				obj.level = level;
				li = $(tmpl(obj))
					.addClass(c_item)
					.appendTo(ul);
				li.attr('data-key', obj.key);
				li.data(d_item, {
					key: obj.key,
					links: obj.links,
					level: level
				});
				if (obj.items) {
					f($('<ul/>').appendTo(li), obj.items, level + 1);
				}
			}
		}
		f(self.element, data, 0);
	},
	_filterHtml: function(key, internal) {
		var self = this,
			element = self.element,
			t, els;
		key = key && (typeof key === 'string' ?
			normKeyword(parseKeywords(key, false)) :
			$.isArray(key) ? key : undefined);
		t = key && key[0];
		if (t) {
			var filter = function() { return ($(this).data('key') || '').indexOf(t) === 0; };
			els = element.children().hide()
				.filter(filter).show();
			els.find('li').show();
			if (els.length) {
				for (var i = 1; (t = key[i]) && els.length; ++i) {
					els = els.children('ul').children().hide()
						.filter(filter).show();
				}
			}
			else {
				self.trigger('notfound');
			}
		}
		else if (self.modeMap) {
			element.children().hide();
			if (!internal) {
				self.trigger('emptyfilter');
			}
		}
		else {
			element.find('li').show()
				.filter('.service').hide();
		}
	},
	getItem: function(li) {
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
	filter: function(text, ignoreMap) {
		var self = this;
		if (self.options.disabled) {
			return;
		}
		if (!arguments.length && self.input) {
			text = self.input.val();
		}
		text = normKeyword(text);
		if (!text) {
			self._filterHtml(undefined, true);
			return;
		}
		self.queue(function(next) {
			var p = {},
				key = self.options.allowSecondaryKeywords ?
					normKeyword(parseKeywords(text, false)) :
					[ text ],
				first = key && key[0];
			if (!first) {
				self._filterHtml(undefined, true);
				next();
				return;
			}
			self.dataSource.loadData(first, function(data) {
				if (data === -2 || data === -1) {
					self.trigger(data === -2 ? 'insufficientfilter' : 'notfound');
					key = undefined;
				}
				else {
					if (p.loadingTrigger) {
						self.trigger('load');
						self.element.empty();
						self._buildHtml(data);
					}
					p.loading = false;
				}
				self._filterHtml(key, true);
				next();
			}, p).fail(function(request, status, error) {
				self.trigger('loaderror', { request: request, status: status, error: error });
			});
			if (p.loading) {
				p.loadingTrigger = true;
				self.trigger('loading');
			}
		});
	},
	find: function(key, callback) {
		return this.dataSource.find(key, callback);
	},
	deselect: function() {
		var c_selected = this.options.selectedClass;
		this.element.find('.' + c_selected).removeClass(c_selected);
	}
});

core.widget('groups', {
	options: {
		dataPath: '',
		dataSource: 'groups.xml',
		dataType: 'xml'
	},
	_create: function() {
		var self = this,
			_ready = self._Ready(),
			options = self.options,
			_data;
		//#region _data init
		if (typeof options.dataPath !== 'string') {
			options.dataPath = '';
		}
		_data = typeof _data === 'string' ?
			Index.dataProviders[_data] :
			(_data && $.isFunction(_data.read) ? _data : undefined) || Index.dataProviders['default'];
		_data = $.extend(true, {}, _data);
		_data.rootTag = 'groups';
		_data.itemTag = 'group';
		//#endregion
		self.queue(function(next) {
			self.trigger('initializing');
			_data.read(options.dataPath + options.dataSource, function(data, error) {
				if (error) {
					core.error('nethelp-groups error: ' + error.errorThrow);
					self.disable();
					_ready.cancel(error);
				}
				else {
					data.dataProvider = _data;
					self.dataSource = new IndexDataSource(data);
					self.modeMap = !!data.map;
					_ready.fire();
				}
				next();
			});
		});
	},
	find: function(key, callback) {
		return this.dataSource.find(key, callback);
	}
});

//#region dataProviders
Index.dataProviders = {
	//TODO: json: $.extend(true, {}, core.dataProviders.json),
	xml: $.extend(true, {}, core.dataProviders.xml, {
		rootTag: 'keywords',
		itemTag: 'keyword',
		success: function(resp, status, request) {
			var self = this,
				map = $('map', resp),
				data = [];
			if (map && map.length) {
				map.children().each(function() {
					var i = $(this),
						ch = i.children(),
						mi = {
							url: i.attr('url'),
							start: normKeyword(ch.filter('start').text())
						},
						t = ch.filter('end');
					if (t.length) {
						mi.end = normKeyword(t.text());
					}
					data.push(mi);
				});
				return { map: data };
			}
			function f(c, items) {
				var len = items && items.length || 0;
				for (var i = 0, item, ch; i < len; ++i) {
					item = items[i];
					ch = self.getChildren(item);
					item = self.getObject(item);
					if (ch && ch.length) {
						item.items = [];
						f(item.items, ch);
					}
					c.push(item);
				}
			}
			f(data, $(self.rootTag, resp).children());
			return { items: data };
		},
		getObject: function(item) {
			item = $(item);
			var ch = item.children(), r;
			r = {
				text: ch.filter('text').text() || item.attr('text') || '',
				links: $.map(ch.filter('link'), function(link) {
					link = $(link);
					return { url: (link.attr('url') || '').toLowerCase(), text: link.text() };
				})
			};
			r.key = normKeyword(r.text);
			return r;
		},
		getChildren: function(item) {
			return $(item).children(this.itemTag);
		}
	})
};
Index.dataProviders['default'] = Index.dataProviders.xml;
//#endregion

//#region internal for testing
Index._internal = {
	normKeyword: normKeyword,
	quickSearch: quickSearch,
	parseKeywords: parseKeywords,
	indexMap: IndexMap
};
//#endregion

})(jQuery, nethelp);