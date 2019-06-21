(function ($, core, undefined) {

var searchAnd,
	searchOr,
	searchNot,
	searchAndInSpaces,
	searchOrInSpaces,
	searchNotInSpaces = searchNot + " ";
var d2hQuotePrefix = "_d2hQuote";
var quotes = new Array();
var aliasesHT = new Array();

function isFarEasternLanguage(s) {
	var codes = [[0x2E80, 0x9FFF], [0xA000, 0xA63F], [0xA6A0, 0xA71F], [0xA800, 0xA82F], [0xA840, 0xD7FF], [0xF900, 0xFAFF], [0xFE30, 0xFE4F]];
	for (var i = 0; i < s.length; i++) {
		var code = s.charCodeAt(i);
		if (code < codes[0][0])
			continue;
		for (var j = 0; j < codes.length; j++)
			if (code >= codes[j][0] && code <= codes[j][1])
				return true;
	}
	return false;
}

function addSpace(str, words, startIndex, checkForEastern) {
	for (var i = startIndex; i < words.length; i++) {
		var pos = str.indexOf(" ", 0);
		if (pos != -1)
			return addSpace(str.substring(0, pos), words, i, checkForEastern) + " " + addSpace(str.substring(pos + 1), words, i, checkForEastern);
		else if (pos == -1 && (!checkForEastern || isFarEasternLanguage(str))) {
			pos = str.indexOf(words[i], 0);
			if (pos != -1) {
				var left = addSpace(str.substring(0, pos), words, i, checkForEastern);
				var right = addSpace(str.substring(pos + words[i].length), words, i, checkForEastern);
				if (left && left != "\"")
					left += " ";
				if (right && right != "\"")
					right = " " + right;
				var newStr = words[i];
				if (left)
					newStr = left + newStr;
				if (right)
					newStr += right;
				return newStr;
			}
		}
	}
	return str;
}

function getWildcardRegexp(wildcard) {
	wildcard = wildcard
		.replace(/[-[\]{}()+.,\\^$|]/g, '\\$&')
		.replace(/\*/g, '\\w*')
		.replace(/\?/g, '.?')
		.replace(/\s+/g, '\\s+');
	if (!isFarEasternLanguage(wildcard)) {
		var l = wildcard.length,
			isWordChar = /\w/;
		wildcard = (isWordChar.test(wildcard.charAt(0)) ? '\\b' : '') + 
			wildcard +
			(isWordChar.test(wildcard.charAt(l - 1)) ? '\\b' : '');
	}
	return wildcard;
}

function Clause(str, quotes) {
	if (!this || !this._prepare) {
		return new Clause(str, quotes);
	}
	if (!quotes) {
		quotes = {};
		str = this._prepare(str, quotes);
	}
	str = str || '';
	var exactPhrase = (str.indexOf(d2hQuotePrefix) == 0 && quotes[str] != null) || (str.toLowerCase().indexOf((searchNotInSpaces + d2hQuotePrefix).toLowerCase()) == 0 && quotes[str.substring(searchNotInSpaces.length, str.length)] != null);
	var parts;
	var foundNot = false;
	if (!exactPhrase) {
		parts = str.split(new RegExp(searchOrInSpaces.replace(/[-[\]{}()*+?.,\\^$|]/g, '\\$&'), "gi"));
		if (parts.length == 1) {
			parts = str.split(new RegExp(searchAndInSpaces.replace(/[-[\]{}()*+?.,\\^$|]/g, '\\$&'), "gi"));
			this.type = 2;
		}
		else {
			this.type = 1;
		}
		if (parts.length == 1) {
			foundNot = str.toUpperCase().indexOf(searchNotInSpaces) === 0;
			if (foundNot) {
				str = str.substring(searchNotInSpaces.length, str.length);
			}
			parts = str.split(" ");
			this.type = 2;
		}
		parts = removeRepeatingTerms(parts);
	}
	else {
		foundNot = str.toUpperCase().indexOf(searchNotInSpaces) === 0;
		if (foundNot) {
			str = str.substring(searchNotInSpaces.length, str.length);
		}
		this.type = 0;
		parts = quotes[str].split(" ");
		str = quotes[str];
	}
	if (parts.length > 1 || exactPhrase) {
		this.children = new Array(parts.length);
		for (var i = 0; i < parts.length; i++) {
			this.children[i] = new Clause(parts[i], quotes);
			if (exactPhrase) {
				this.children[i].type = 0;
			}
		}
	}
	if (foundNot) {
		if (parts.length == 1 || exactPhrase) {
			this.not = true;
		}
		else {
			this.children[0].not = true;
		}
	}
	this.value = str.toLowerCase();
}

Clause.prototype = {
	value: "",
	children: null,
	type: 0, //0 - exact phrase, 1 - OR, 2 - AND
	not: false,
	docs: null,
	_prepare: function(str, quotes) {
		if (!str) {
			return;
		}
		var i = 0;
		str = str.replace(/"([^"]+)"/g, function(m, phrase) {
			var key = d2hQuotePrefix + (i++);
			quotes[key] = phrase;
			return key;
		});
		for (var i = 0; i < g_sStopWords.length; i++) {
			var word = g_sStopWords[i].toUpperCase();
			if (word === searchOr || word === searchAnd || word === searchNot)
				continue;
			str = str.replace(RegExp("\\b" + g_sStopWords[i] + "\\b", 'ig'), "");
		}
		str = addAND(str
			.replace(/[\.,";(){}[\]]/g, " ")
			.replace(/\s+/g, " ")
			.replace(/^\s+/g, "").replace(/\s+$/g, ""));
		return str;
	},
	execute: function() {
		if (this.children == null) {
			if (this.type != 0) {
				var aliases = getAliases(this.value);
				for (var i = 0; i < aliases.length; i++)
					this.docs = mergeDocs(this.docs, searchInIndex(aliases[i], true));
			}
			else
				this.docs = searchInIndex(this.value, this.type != 0);
			if (!this.docs && !isFarEasternLanguage(this.value)) {
				var newString = addSpace(this.value, getWords(), 0, false);
				if (newString != this.value) {
					var words = newString.split(" ");
					for (var i = 0; i < words.length; i++) {
						if (getWordIndex(g_sStopWords, words[i]) != -1)
							continue;
						var documents = searchInIndex(words[i], true);
						if (!documents)
							break;
						this.docs = !this.docs ? documents : intersect(this.docs, documents, true);
					}
				}
			}

		}
		else {
			for (var i = 0; i < this.children.length; i++) {
				if (this.type == 0 && getWordIndex(g_sStopWords, this.children[i].value) != -1)
					continue;
				if (this.children[i].execute()) {
					if (this.type == 0 || this.type == 2)
						this.docs = !this.docs ? this.children[i].docs : intersect(this.docs, this.children[i].docs, this.type == 0);
					else
						this.docs = !this.docs ? this.children[i].docs : mergeDocs(this.docs, this.children[i].docs);
				}
				else if (this.type != 1 || i == 0)
					this.docs = null;
				if (this.docs == null && this.type != 1)
					break;
			}
		}
		if (this.not && this.docs)
			this.docs = invert(this.docs);
		return this.docs != null;
	},
	getQueryString: function(corrected) {
		if (this.children == null) {
			var word;
			if (corrected && this.type != 0 && !isWildcard(this.value) && (!this.docs || this.docs.length < 2) && !aliasesHT[this.value])
				// looks misspelled
				word = getSimilarWord(this.value);
			else
				word = this.value;
			return this.not && this.type != 0 ? searchNotInSpaces + word : word;
		}
		else {
			var query = "";
			var typeName = this.type == 1 ? searchOrInSpaces : " ";
			for (var i = 0; i < this.children.length; i++) {
				var subquery = this.children[i].getQueryString(corrected);
				if (i == 0)
					query = subquery;
				else if (this.children[i].not && this.type == 2)
					query += searchAndInSpaces + subquery;
				else
					query += typeName + subquery;
			}
			if (this.type == 0) {
				query = "\"" + query + "\"";
				if (this.not)
					query = searchNotInSpaces + query;
			}
			return query;
		}
	},
	getWords: function(usePhrase) {
		if (!this.children || usePhrase && this.type === 0) {
			return this.not ? [] : [ this.value ];
		}
		return $.map(this.children, function(i) {
			return i.getWords();
		});
	}
}

function defineOperators(and, or, not) {
	searchAnd = and.toUpperCase();
	searchOr = or.toUpperCase();
	searchNot = not.toUpperCase();
	searchAndInSpaces = " " + searchAnd + " ";
	searchOrInSpaces = " " + searchOr + " ";
	searchNotInSpaces = searchNot + " ";
}

defineOperators('AND', 'OR', 'NOT');

function getWords() {
	var wordsSorted = new Array();
	wordsSorted.length = g_sWords.length;
	for (var i = 0; i < g_sWords.length; i++)
		wordsSorted[i] = g_sWords[i];
	wordsSorted.sort(sortByWordsLength);
	return wordsSorted;
}

function sortByWordsLength(x, y) {
	var delta = x.length - y.length;
	if (delta < 0)
		return 1;
	if (delta > 0)
		return -1;
	return 0;
}

function getAliases(word) {
	var indexes = aliasesHT[word];
	var words;
	if (indexes && (typeof indexes != "function")) {
		words = g_sAliases[indexes[0]];
		for (var i = 1; i < indexes.length; i++)
			words = mergeSimple(words, g_sAliases[indexes[i]]);
	}
	else {
		words = new Array(1);
		words[0] = word;
	}
	return words;
}

function getDistance(x, y, maxDelta) {
	if (x.charAt(0) != y.charAt(0) || Math.abs(x.length - y.length) > 2)
		return maxDelta + 1;
	var N = x.length + 1, M = y.length + 1, min;
	var a = new Array(N);
	for (var i = 0; i < N; i++) {
		a[i] = new Array(M);
		a[i][0] = i;
	}
	for (var i = 0; i < M; i++)
		a[0][i] = i;
	for (var i = 1; i < N; i++) {
		min = N + M;
		for (var j = 1; j < M; j++) {
			a[i][j] = Math.min(a[i - 1][j - 1] + (x.charAt(i - 1) == y.charAt(j - 1) ? 0 : 1), Math.min(a[i - 1][j], a[i][j - 1]) + 1);
			if (a[i][j] < min)
				min = a[i][j];
		}
		if (min > maxDelta)
			return maxDelta + 1;
	}
	return a[N - 1][M - 1];
}

function getSimilarWord(w) {
	if (isFarEasternLanguage(w))
		return w;
	var maxDelta = 3 * Math.round(0.4 + w.length / 10);
	var bestWordIndex = -1, bestLength = -1;
	for (var i = 0; i < g_sWords.length; i++) {
		var word = g_sWords[i], topicsCount = getWordTopicsItemLength(i);
		if (topicsCount == 1 && !aliasesHT[word])
			continue;
		var d = getDistance(word, w, maxDelta);
		if (d > maxDelta || d == 0)
			continue;
		if (d < maxDelta || bestWordIndex == -1) {
			maxDelta = d;
			bestWordIndex = i;
			bestLength = topicsCount;
		}
		else if (topicsCount > bestLength) {
			bestWordIndex = i;
			bestLength = topicsCount;
		}
	}
	return bestWordIndex != -1 ? g_sWords[bestWordIndex] : w;
}

var _aliasesInited = false;
function initAliases() {
	if (_aliasesInited) {
		return;
	}
	for (var i = 0; i < g_sAliases.length; i++) {
		for (var j = 0; j < g_sAliases[i].length; j++) {
			var word = g_sAliases[i][j];
			if (aliasesHT[word] == null)
				aliasesHT[word] = new Array(1);
			aliasesHT[word][aliasesHT[word].length - 1] = i;
		}
	}
	_aliasesInited = true;
}

function addAND(str) {
	for (var i = str.length - searchNotInSpaces.length - 1; i >= 0; i--)
		if (str.substring(i, i + searchNotInSpaces.length + 1).toUpperCase() == " " + searchNotInSpaces) {
			var startIndex = i - searchAndInSpaces.length + 1;
			var found = startIndex >= 0 ? str.substring(startIndex, i + 1).toUpperCase() == searchAndInSpaces : false;
			if (!found) {
				startIndex = i - searchOrInSpaces.length + 1;
				found = startIndex >= 0 ? str.substring(startIndex, i + 1).toUpperCase() == searchOrInSpaces : false;
			}
			if (!found)
				str = str.substring(0, i) + " " + searchAnd + str.substring(i, str.length);
		}
	return str;
}

function jExecQuery(strQuery, callback) {
	// required: this is widget
	var self = this;
	initAliases();
	var root = new Clause(strQuery);
	root.execute();
	var original = root.getQueryString(false),
		corrected = root.getQueryString(true);
	if (original == corrected) {
		corrected = "";
	}
	(callback || $.noop)(original, root);
	if (this.docs) {
		root.docs = calcHistogram(root.docs);
	}
	return getQueryResult.call(self, corrected, root.docs);
}

function getDocID(arr) {
	return arr[0];
}

function invert(docs) {
	var docsLength = docs ? docs.length : 0;
	var cnt = g_sTopics.length - docsLength;
	var newDocs = new Array(cnt);
	var j = 0, l = 0;
	var id = j < docsLength ? getDocID(docs[j++]) : g_sTopics.length;
	for (var i = 0; i < g_sTopics.length; i++) {
		if (i < id) {
			newDocs[l] = new Array(1);
			newDocs[l][0] = i;
			l++;
		}
		else if (i == id)
			id = j < docsLength ? getDocID(docs[j++]) : g_sTopics.length;
	}
	return newDocs;
}

function intersect(docs1, docs2, exactPhrase) {
	if (!docs1  || !docs2)
		return null; 
	var docs = new Array(docs1.length);
	var i = 0, j = 0, k = 0;
	while (i < docs1.length && j < docs2.length) {
		var id1 = getDocID(docs1[i]), id2 = getDocID(docs2[j]);
		if (id1 == id2) {
			var p1 = 1, p2 = 1, p = 1;
			var positions = new Array();
			positions[0] = id1;
			if (exactPhrase) {
				while (p1 < docs1[i].length && p2 < docs2[j].length) {
					if (docs1[i][p1] == docs2[j][p2] - 1) {
						positions[p++] = docs2[j][p2];
						p1++;
						p2++;
					}
					while (p1 < docs1[i].length && docs1[i][p1] < docs2[j][p2] - 1)
						p1++;
					while (p2 < docs2[j].length && docs2[j][p2] <= docs1[i][p1])
						p2++;
				}
			}
			if (!exactPhrase || positions.length > 1) {
				docs[k] = positions;
				k++;
			}
			i++;
			j++;
		}
		else if (id1 < id2) {
			while (i < docs1.length && getDocID(docs1[i]) < id2)
				i++;
		}
		else {
			while (j < docs2.length && getDocID(docs2[j]) < id1)
				j++;
		}
	}
	if (docs.length > k)
		docs.length = k;
	return docs;
}

function mergeSimple(x, y) {
	if (x == null)
		return y;
	if (y == null)
		return x;
	var res = new Array(x.length + y.length);
	var i = 0, j = 0, k = 0;
	while (i < x.length && j < y.length) {
		if (x[i] == y[j]) {
			res[k++] = x[i++];
			j++;
		}
		else
			res[k++] = x[i] < y[j] ? x[i++] : y[j++];
	}
	while (i < x.length)
		res[k++] = x[i++];
	while (j < y.length)
		res[k++] = y[j++];
	if (res.length > k)
		res.length = k;
	return res;
}

function mergeDocs(x, y) {
	if (x == null)
		return y;
	if (y == null)
		return x;
	var res = new Array(x.length + y.length);
	var i = 0, j = 0, k = 0;
	while (i < x.length && j < y.length) {
		var id1 = getDocID(x[i]), id2 = getDocID(y[j]);
		if (id1 == id2) {
			res[k] = new Array(2);
			res[k++] = mergeSimple(x[i++], y[j++]);
		}
		else
			res[k++] = id1 < id2 ? x[i++] : y[j++];
	}
	while (i < x.length)
		res[k++] = x[i++];
	while (j < y.length)
		res[k++] = y[j++];
	if (res.length > k)
		res.length = k;
	return res;
}

function getWordIndex(words, word) {
	var l = 0, r = words.length - 1;
	while (r > l) {
		var m = Math.round((l + r) / 2);
		if (words[m] < word)
			l = m + 1;
		else if (words[m] > word)
			r = m - 1;
		else
			return m;
	}
	return l == r && words[l] == word ? l : -1;
}

function getWordTopicsItemLength(i) {
	var j = 0, k = 0;
	while (j < g_sWordTopics[i].length) {
		k++;
		j += g_sWordTopics[i][j + 1] + 2;
	}
	return k;
}

function getWordTopicsItem(i) {
	var arr = new Array(getWordTopicsItemLength(i));
	var j = 0, k = 0;
	while (j < g_sWordTopics[i].length) {
		arr[k] = new Array(g_sWordTopics[i][j + 1] + 1);
		arr[k][0] = g_sWordTopics[i][j];
		for (var l = 0; l < g_sWordTopics[i][j + 1]; l++)
			arr[k][l + 1] = g_sWordTopics[i][j + 2 + l];
		k++;
		j += g_sWordTopics[i][j + 1] + 2;
	}
	return arr;
}

function searchInIndex(term, allowWildcards) {
	var wildcard = allowWildcards && isWildcard(term);
	if (wildcard) {
		var re = new RegExp(getWildcardRegexp(term), "gi");
		var indx;
		var res = null;
		for (var i = 0; i < g_sWords.length; i++) {
			indx = g_sWords[i].search(re);
			if (indx > -1) {
				if (res)
					res = mergeDocs(res, getWordTopicsItem(i));
				else
					res = getWordTopicsItem(i);
			}
		}
		return res;
	}
	else {
		var index = getWordIndex(g_sWords, term);
		return index != -1 ? getWordTopicsItem(index) : null;
	}
}

function getWordsFromIndex(termWithWildcards) {
	var words = new Array();
	var re = new RegExp(getWildcardRegexp(termWithWildcards), "gi");
	for (var i = 0; i < g_sWords.length; i++)
		if (g_sWords[i].search(re) > -1) {
			words.length = words.length + 1;
			words[words.length - 1] = g_sWords[i];
		}
	return words;
}

function isWildcard(term) {
	return term.indexOf("?") > -1 || term.indexOf("*") > -1;
}

function removeRepeatingTerms(terms) {
	var htbl = new Array();
	var res = new Array();
	for (var i = 0; i < terms.length; i++)
		if (!htbl[terms[i]]) {
			res[res.length] = terms[i];
			htbl[terms[i]] = true;
		}
	return res;
}

function calcHistogram(arr) {
	var tbl = new Array();
	var id;
	for (var i = 0; i < arr.length; i++) {
		id = "x" + arr[i][0];
		if (tbl[id]) {
			tbl[id] = new Array(tbl[id].length + arr[i].length);
			arr[i] = null;
		}
		else
			tbl[id] = arr[i];
	}
	arr.sort(sortByCounterNumber);
	return arr;
}

function sortByCounterNumber(x, y) {
	if (x == null)
		return 1;
	if (y == null)
		return -1;
	var delta = x.length - y.length;
	if (delta == 0) {
		var xTopic = g_sTopics[x[0]][1];
		var yTopic = g_sTopics[y[0]][1];
		if (xTopic > yTopic)
			return 1;
		else if (xTopic < yTopic)
			return -1;
		return 0;
	}
	if (delta < 0)
		return 1;
	return -1;
}

function getQueryResult(newQuery, arr) {
	var self = this,
		options = self.options,
		res = '';
	if (arr) {
		for (var i = 0, l = arr.length, item, td; i < l; i++) {
			item = arr[i];
			td = item && g_sTopics[item[0]];
			if (td) {
				res += options.itemTemplate(td[0], td[1], 'searchResult' + i);
			}
		}
	}
	return { results: res, correcting: newQuery };
}

function highlight(element, word, className) {
	element = $(element);
	if (element.hasClass(className)) {
		return;
	}
	if (typeof word === 'string') {
		word = new RegExp(getWildcardRegexp(word), 'i');
	}
	if (element.hasClass('nethelp-word')) {
		var t = element.text();
			m = word.exec(t);
		if (m && m[0].length === t.length) {
			element.addClass(className);
			return;
		}
	}
	element.contents().each(function() {
		var n = this, h;
		if (n.nodeType === 3 || n.nodeType === 4) {
			// The text from text nodes and CDATA nodes
			var m, l;
			while (m = word.exec(n.nodeValue || '')) {
				h = m.index ? n.splitText(m.index) : n;
				m = m[0];
				l = m.length;
				n = h.nodeValue.length > l ? h.splitText(l) : 0;
				$(h).wrap('<span class="nethelp-word ' + className + '" />');
			}
		}
		else if (this.nodeType !== 8) {
			highlight(this.nodeType === 9 ? $('body', this) : this, 
				word, className);
		}
	});
}
function unhighlight(element, className) {
	$(element)
		.find('.' + className).removeClass(className)
		.end().find('iframe').each(function() {
			unhighlight(this.contentWindow.document, className);
		});
}

var d_item = 'searchItem';

core.widget('search', {
	options: {
		dataSource: 'searchindex.js',
		inputElement: undefined,
		selectedClass: 'nethelp-search-selected',
		highlight: {
			disabled: false,
			element: undefined,
			className: 'search-highlight'
		},
		operators: {
			and: 'AND',
			or: 'OR',
			not: 'NOT'
		},
		itemTemplate: '<li data-url="#{url}"><a class="nethelp-search-text" id="#{id}" href="#{url}">#{text}</a></li>'
	},
	_create: function () {
		var self = this,
			_ready = self._Ready(),
			element = self.element,
			options = self.options,
			operators = options.operators,
			inputElement = options.inputElement;

		defineOperators(operators.and, operators.or, operators.not);

		// init templates
		if (typeof options.itemTemplate === 'string') {
			var itemTemplate = options.itemTemplate;
			options.itemTemplate = function(url, text, id) {
				return itemTemplate
					.replace(/#{text}/gi, text)
					.replace(/#{url}/gi, url)
					.replace(/\s+id="#{id}"/gi, id ? ' id="' + id + '"' : '');
			}
		}

		// init element
		element.addClass('nethelp-search');
		element.delegate('.nethelp-search-text', 'click', function(e) {
			var target = $(this),
				li = self.getItem(target);
			e.preventDefault();
			self.deselect();
			target.addClass(options.selectedClass);
			self.trigger('select', null, { target: target, li: li, url: li.data('url') });
		}).delegate('.correcting', 'click', function() {
			self.search($(this).text());
		});

		// init input
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
				inputElement.keydown(function (e) {
					if (e.which === 13) {
						e.preventDefault();
						self.search(inputElement.val());
					}
				});
			}
		}

		self.disable();
		$.getScript(options.dataSource, function() {
			element.empty();
			self.enable();
			_ready.fire();

		}).fail(function(request, status, error) {
			_ready.cancel({ status: status, error: error, request: request });
		});
	},
	getItem: function(li) {
		return li && li.__widget === this ? li : $(li).eq(0).closest('li', this.element).extend({ __widget: this });
	},
	getData: function(li) {
		li = this.getItem(li);
		return li.data(d_item);
	},
	disable: function(event) {
		var self = this;
		self.options.disabled = true;
		self.element.empty();
		self.trigger('disabled', event, { setter: true });
	},
	enable: function() {
		var self = this;
		if (self.options.disabled) {
			self.options.disabled = false;
			self.element.find('.service').remove();
		}
	},
	search: function (text, element, callback) {
		var self = this,
			options = self.options,
			p;
		if (options.disabled) {
			return;
		}
		if ($.isFunction(element)) {
			callback = element;
			element = undefined;
		}
		element = element && $(element) || self.element;
		self.highlight(false);
		element.empty();
		p = { element: element };
		var res = jExecQuery.call(self, text || self.query(), function (t, words) {
			self.query(t);
			self.highlight(words);
		}) || {};
		$.extend(p, res);
		if (res.results) {
			element.append(res.results);
		}
		else {
			self.trigger('notfound', undefined, p);
		}
		if (res.correcting) {
			self.trigger('correcting', undefined, p);
		}
		$.isFunction(callback) && callback(self, element);
	},
	query: function (text) {
		var input = this.input;
		if (text == undefined) {
			return input && input.val() || '';
		}
		else if (input) {
			input.val(text);
		}
	},
	deselect: function() {
		var c_selected = this.options.selectedClass;
		this.element.find('.' + c_selected).removeClass(c_selected);
	},
	highlight: function(params) {
		/*
			params: {
				action: boolean,
				element: jQuery,
				words: string|Clause,
				aliases: boolean
			}
			or params: false - unhighlight
			or params: string|Clause
		*/
		var self = this,
			action;
		if (typeof params === 'boolean') {
			params = { action: params };
		}
		else if (typeof params === 'string' || params instanceof Clause || $.isArray(params)) {
			params = { words: params };
		}
		params = $.extend({}, self.options.highlight, params);
		if (params.action || params.action == undefined && !params.disabled) {
			var words = params.words || self.query();
			if (typeof words === 'string') {
				words = new Clause(words);
			}
			if (words instanceof Clause) {
				words = words.getWords(true);
			}
			if ($.isArray(words)) {
				if (params.aliases) {
					words = $.map(words, function(w) {
						return getAliases(w);
					});
				}
				var p;
				words = $.map(words.sort(), function(w) {
					return w === p || !w ? undefined : (p = w);
				});
				for (var w in words) {
					highlight(params.element, words[w], params.className);
				}
			}
		}
		else if (params.action != undefined) {
			unhighlight(params.element, params.className);
		}
	}
}); // widget

})(jQuery, nethelp);